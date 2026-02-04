<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use App\Notifications\PaymentConfirmed;
use App\Notifications\SubscriptionActivated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(?PaymentGatewayInterface $gateway = null)
    {
        // Use Lahza gateway if configured, otherwise fall back to cash
        if ($gateway === null && config('payments.gateway') === 'lahza') {
            $this->gateway = new LahzaPaymentGateway;
        } else {
            $this->gateway = $gateway ?? new CashPaymentGateway;
        }
    }

    public function setGateway(PaymentGatewayInterface $gateway): self
    {
        $this->gateway = $gateway;

        return $this;
    }

    /**
     * Get the appropriate gateway based on payment method.
     */
    protected function getGatewayForMethod(string $paymentMethod): PaymentGatewayInterface
    {
        return match ($paymentMethod) {
            'cash' => new CashPaymentGateway,
            'card', 'lahza' => new LahzaPaymentGateway,
            default => $this->gateway,
        };
    }

    public function createPayment(
        User $user,
        SubscriptionPlan $plan,
        string $paymentMethod,
        ?string $notes = null
    ): Payment {
        $gateway = $this->getGatewayForMethod($paymentMethod);

        return $gateway->createPayment($user, $plan->price, [
            'subscription_plan_id' => $plan->id,
            'currency' => 'USD',
            'notes' => $notes ?? "Subscription to {$plan->name}",
            'metadata' => [
                'plan_name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
            ],
        ]);
    }

    public function createSubscriptionPayment(
        User $user,
        SubscriptionPlan $plan,
        array $data = []
    ): Payment {
        return $this->gateway->createPayment($user, $plan->price, [
            'subscription_plan_id' => $plan->id,
            'currency' => $data['currency'] ?? 'USD',
            'notes' => $data['notes'] ?? "Subscription to {$plan->name}",
            'metadata' => [
                'plan_name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
            ],
        ]);
    }

    public function confirmPaymentAndActivateSubscription(
        Payment $payment,
        array $confirmationData = []
    ): UserSubscription {
        return DB::transaction(function () use ($payment, $confirmationData) {
            $gateway = $this->getGatewayForMethod($payment->payment_method ?? 'cash');
            $gateway->confirmPayment($payment, $confirmationData);

            $plan = $payment->plan;
            $startsAt = now();
            $endsAt = $this->calculateEndDate($plan);

            $subscription = UserSubscription::create([
                'user_id' => $payment->user_id,
                'subscription_plan_id' => $plan->id,
                'payment_id' => $payment->id,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => 'active',
                'auto_renew' => false,
            ]);

            $subscription->activate();

            // Clear pending plan after successful subscription activation
            $payment->user->update(['pending_plan_id' => null]);

            // Send payment confirmation notification
            $payment->user->notify(new PaymentConfirmed($payment));

            // Send subscription activated notification
            $payment->user->notify(new SubscriptionActivated($subscription));

            return $subscription;
        });
    }

    public function refundAndCancelSubscription(
        Payment $payment,
        ?float $refundAmount = null
    ): bool {
        return DB::transaction(function () use ($payment, $refundAmount) {
            $this->gateway->refundPayment($payment, $refundAmount);

            $subscription = $payment->subscription;
            if ($subscription) {
                $subscription->cancel();
            }

            return true;
        });
    }

    public function getPaymentHistory(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $user->payments()
            ->with('plan')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getPendingPayments(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return $user->payments()
            ->pending()
            ->with('plan')
            ->get();
    }

    protected function calculateEndDate(SubscriptionPlan $plan): ?\Carbon\Carbon
    {
        return match ($plan->billing_cycle) {
            'monthly' => now()->addMonth(),
            'yearly' => now()->addYear(),
            'lifetime' => null,
            default => now()->addMonth(),
        };
    }

    public function getGatewayName(): string
    {
        return $this->gateway->getGatewayName();
    }

    public function initializeLahzaCheckout(User $user, SubscriptionPlan $plan, array $data = []): Payment
    {
        $existingPending = $user->payments()
            ->pending()
            ->where('subscription_plan_id', $plan->id)
            ->where('created_at', '>', now()->subMinutes(30))
            ->first();

        if ($existingPending) {
            return $existingPending;
        }

        // Generate callback URL without parameters (Lahza will add ?reference=)
        $callbackUrl = url(route('payments.callback', [], false));

        $payment = $this->gateway->createPayment($user, $plan->price, [
            'subscription_plan_id' => $plan->id,
            'currency' => $data['currency'] ?? 'USD',
            'notes' => "Subscription to {$plan->name}",
            'callback_url' => $callbackUrl,
            'metadata' => [
                'plan_name' => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
                'user_email' => $user->email,
                'user_name' => $user->name,
            ],
        ]);

        return $payment;
    }

    public function processCallback(string $reference): UserSubscription|\App\Models\UserAddon|null
    {
        $payment = Payment::where('transaction_id', $reference)
            ->orWhere('gateway_reference', $reference)
            ->first();

        if (! $payment) {
            Log::warning('Payment callback received for unknown reference', [
                'reference' => $reference,
            ]);

            return null;
        }

        // Route to AddonService if this payment is for an add-on
        if ($payment->addon_id) {
            return app(AddonService::class)->confirmPaymentAndGrantAddon($payment);
        }

        // Skip verification for Lahza - it's already verified via webhook or direct API call
        // Just activate the subscription directly
        return $this->confirmPaymentAndActivateSubscription($payment, [
            'notes' => 'Payment confirmed via callback',
        ]);
    }

    public function getCheckoutUrlForPayment(Payment $payment): ?string
    {
        if (method_exists($this->gateway, 'getCheckoutUrl')) {
            return $this->gateway->getCheckoutUrl($payment);
        }

        return null;
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (method_exists($this->gateway, 'verifyWebhookSignature')) {
            return $this->gateway->verifyWebhookSignature($payload, $signature);
        }

        return false;
    }
}
