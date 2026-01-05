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

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(?PaymentGatewayInterface $gateway = null)
    {
        $this->gateway = $gateway ?? new CashPaymentGateway();
    }

    public function setGateway(PaymentGatewayInterface $gateway): self
    {
        $this->gateway = $gateway;
        return $this;
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
            $this->gateway->confirmPayment($payment, $confirmationData);

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
}
