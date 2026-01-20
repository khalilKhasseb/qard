<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LahzaPaymentGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;

    protected string $publicKey;

    protected string $secretKey;

    protected string $webhookSecret;

    protected bool $testMode;

    public function __construct()
    {
        $this->baseUrl = config('payments.lahza.base_url', 'https://api.lahza.io');
        $this->publicKey = config('payments.lahza.public_key', '');
        $this->secretKey = config('payments.lahza.secret_key', '');
        $this->webhookSecret = config('payments.lahza.webhook_secret', '');
        $this->testMode = config('payments.lahza.test_mode', true);
    }

    public function createPayment(User $user, float $amount, array $data = []): Payment
    {
        $payment = Payment::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
            'amount' => $amount,
            'currency' => $data['currency'] ?? config('payments.lahza.currency', 'USD'),
            'payment_method' => 'lahza',
            'status' => 'pending',
            'gateway_reference' => null,
            'notes' => $data['notes'] ?? null,
            'metadata' => array_merge($data['metadata'] ?? [], [
                'test_mode' => $this->testMode,
            ]),
        ]);

        try {
            $response = $this->initializeTransaction($user, $amount, $payment->transaction_id, $data);

            $payment->update([
                'gateway_reference' => $response['data']['reference'] ?? null,
                'metadata' => array_merge($payment->metadata ?? [], [
                    'checkout_url' => $response['data']['checkout_url'] ?? null,
                    'api_response' => $response,
                ]),
            ]);

            Log::info('Lahza payment initialized', [
                'payment_id' => $payment->id,
                'reference' => $payment->transaction_id,
                'gateway_reference' => $payment->gateway_reference,
                'amount' => $amount,
                'user_id' => $user->id,
            ]);

        } catch (\Exception $e) {
            $payment->update([
                'status' => 'failed',
                'notes' => 'Failed to initialize payment: '.$e->getMessage(),
            ]);

            Log::error('Lahza payment initialization failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
                'amount' => $amount,
                'user_id' => $user->id,
            ]);

            throw $e;
        }

        return $payment;
    }

    public function initializeTransaction(User $user, float $amount, string $reference, array $data = []): array
    {
        $amountInCents = (int) round($amount * 100); // Convert to lowest currency unit

        $payload = [
            'email' => $user->email,
            'amount' => $amountInCents,
            'ref' => $reference,
            'currency' => $data['currency'] ?? config('payments.lahza.currency', 'USD'),
            'channels' => config('payments.lahza.channels', ['card']),
            'metadata' => array_merge($data['metadata'] ?? [], [
                'user_id' => $user->id,
                'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
            ]),
        ];

        // Add optional fields if provided
        if (isset($data['mobile'])) {
            $payload['mobile'] = $data['mobile'];
        }
        if (isset($data['firstName'])) {
            $payload['firstName'] = $data['firstName'];
        }
        if (isset($data['lastName'])) {
            $payload['lastName'] = $data['lastName'];
        }
        if (isset($data['callback_url'])) {
            $payload['callback_url'] = $data['callback_url'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'/transaction/initialize', $payload);

        if ($response->failed()) {
            throw new \Exception('Lahza API error: '.$response->body());
        }

        return $response->json();
    }

    public function verifyTransaction(string $reference): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
        ])->get($this->baseUrl.'/transaction/verify/'.urlencode($reference));

        if ($response->failed()) {
            throw new \Exception('Lahza verification error: '.$response->body());
        }

        return $response->json();
    }

    public function confirmPayment(Payment $payment, array $confirmationData = []): bool
    {
        $transactionId = $payment->gateway_reference ?? $payment->transaction_id;

        if (! $transactionId) {
            throw new \Exception('No gateway reference found for payment');
        }

        try {
            $verification = $this->verifyTransaction($transactionId);

            $status = $verification['data']['status'] ?? null;

            if ($status === 'success') {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'notes' => $confirmationData['notes'] ?? 'Payment verified successfully',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'verification_response' => $verification,
                        'verified_at' => now()->toISOString(),
                    ]),
                ]);

                Log::info('Lahza payment confirmed', [
                    'payment_id' => $payment->id,
                    'reference' => $payment->transaction_id,
                    'amount' => $payment->amount,
                ]);

                return true;
            } elseif ($status === 'failed' || $status === 'abandoned') {
                $payment->update([
                    'status' => 'failed',
                    'notes' => 'Payment failed or was abandoned',
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'verification_response' => $verification,
                    ]),
                ]);

                return false;
            } else {
                // Keep as pending if not yet determined
                $payment->update([
                    'metadata' => array_merge($payment->metadata ?? [], [
                        'verification_response' => $verification,
                    ]),
                ]);

                return false;
            }

        } catch (\Exception $e) {
            Log::error('Lahza payment confirmation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        $refundAmount = $amount ?? $payment->amount;
        $amountInCents = (int) round($refundAmount * 100);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->secretKey,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl.'/transaction/refund', [
            'reference' => $payment->gateway_reference,
            'amount' => $amountInCents,
        ]);

        if ($response->failed()) {
            Log::error('Lahza refund failed', [
                'payment_id' => $payment->id,
                'error' => $response->body(),
            ]);

            throw new \Exception('Lahza refund error: '.$response->body());
        }

        $payment->update([
            'status' => 'refunded',
            'metadata' => array_merge($payment->metadata ?? [], [
                'refund_response' => $response->json(),
                'refund_amount' => $refundAmount,
                'refund_date' => now()->toISOString(),
            ]),
        ]);

        Log::info('Lahza payment refunded', [
            'payment_id' => $payment->id,
            'amount' => $refundAmount,
        ]);

        return true;
    }

    public function getPaymentStatus(Payment $payment): string
    {
        if ($payment->gateway_reference) {
            try {
                $verification = $this->verifyTransaction($payment->gateway_reference);

                return $verification['data']['status'] ?? $payment->status;
            } catch (\Exception $e) {
                Log::warning('Failed to verify payment status', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $payment->status;
    }

    public function processPayment(Payment $payment): bool
    {
        // Process the payment using Lahza API
        // This is typically called after initializing the transaction
        if ($payment->gateway_reference) {
            try {
                $verification = $this->verifyTransaction($payment->gateway_reference);
                $status = $verification['data']['status'] ?? null;

                return $status === 'success';
            } catch (\Exception $e) {
                Log::error('Failed to process payment', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        }

        return false;
    }

    public function supportsRecurring(): bool
    {
        return false; // Lahza doesn't support recurring payments in basic implementation
    }

    public function getGatewayName(): string
    {
        return 'lahza';
    }

    public function getCheckoutUrl(Payment $payment): ?string
    {
        return $payment->metadata['checkout_url'] ?? null;
    }

    public function generateCallbackUrl(string $reference): string
    {
        return route('payments.callback', ['reference' => $reference]);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (empty($this->webhookSecret)) {
            Log::warning('Webhook secret not configured');

            return false;
        }

        $expectedSignature = hash_hmac('sha512', $payload, $this->webhookSecret);

        return hash_equals($expectedSignature, $signature);
    }
}
