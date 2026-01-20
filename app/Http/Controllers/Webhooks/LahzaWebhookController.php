<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LahzaWebhookController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function handle(Request $request)
    {
        // Log the webhook received
        Log::info('Lahza webhook received', [
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
        ]);

        // Verify webhook signature if enabled
        if (config('payments.security.verify_webhook_signatures', true)) {
            $signature = $request->header('X-Lahza-Signature');

            if (! $signature) {
                Log::warning('Webhook received without signature', [
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'error' => 'Missing signature header',
                ], 401);
            }

            $payload = $request->getContent();
            $isVerified = $this->paymentService->verifyWebhookSignature($payload, $signature);

            if (! $isVerified) {
                Log::warning('Webhook signature verification failed', [
                    'signature' => $signature,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'error' => 'Invalid signature',
                ], 401);
            }
        }

        // Validate webhook payload
        $validator = Validator::make($request->all(), [
            'event' => 'required|string',
            'data' => 'required|array',
            'data.reference' => 'required|string',
            'data.status' => 'required|string',
            'data.amount' => 'sometimes|numeric',
            'data.currency' => 'sometimes|string',
            'data.created_at' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid webhook payload', [
                'errors' => $validator->errors(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Invalid payload',
                'details' => $validator->errors(),
            ], 400);
        }

        $event = $request->input('event');
        $data = $request->input('data');
        $reference = $data['reference'];

        try {
            switch ($event) {
                case 'charge.success':
                    $this->handleChargeSuccess($data, $reference);
                    break;

                case 'charge.failed':
                case 'charge.abandoned':
                    $this->handleChargeFailure($data, $reference);
                    break;

                case 'charge.refunded':
                    $this->handleChargeRefund($data, $reference);
                    break;

                default:
                    Log::info('Unhandled webhook event', [
                        'event' => $event,
                        'reference' => $reference,
                    ]);
                    break;
            }

            Log::info('Webhook processed successfully', [
                'event' => $event,
                'reference' => $reference,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Webhook processed successfully',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'event' => $event,
                'reference' => $reference,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return 500 so Lahza will retry
            return response()->json([
                'error' => 'Internal server error',
            ], 500);
        }
    }

    protected function handleChargeSuccess(array $data, string $reference): void
    {
        // Find the payment by reference
        $payment = Payment::where('gateway_reference', $reference)
            ->orWhere('transaction_id', $reference)
            ->first();

        if (! $payment) {
            Log::warning('Payment not found for successful charge', [
                'reference' => $reference,
            ]);

            // Create a new payment record if it doesn't exist
            // This handles cases where webhook arrives before callback
            $this->createPaymentFromWebhook($data, $reference);

            return;
        }

        // Skip if already completed
        if ($payment->isCompleted()) {
            Log::info('Payment already completed, skipping', [
                'payment_id' => $payment->id,
                'reference' => $reference,
            ]);

            return;
        }

        // Mark payment as completed
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => 'Payment verified via webhook',
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook_data' => $data,
                'webhook_received_at' => now()->toISOString(),
                'webhook_event' => 'charge.success',
            ]),
        ]);

        Log::info('Payment marked as completed via webhook', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'amount' => $payment->amount,
        ]);

        // Activate subscription if not already active
        if (! $payment->subscription) {
            $subscription = $payment->subscriptionPlan;
            if ($subscription) {
                $this->paymentService->confirmPaymentAndActivateSubscription($payment, [
                    'notes' => 'Payment confirmed via webhook',
                    'gateway_response' => $data,
                ]);

                Log::info('Subscription activated via webhook', [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'subscription_id' => $payment->subscription->id,
                ]);
            }
        }
    }

    protected function handleChargeFailure(array $data, string $reference): void
    {
        $payment = Payment::where('gateway_reference', $reference)
            ->orWhere('transaction_id', $reference)
            ->first();

        if (! $payment) {
            Log::warning('Payment not found for failed charge', [
                'reference' => $reference,
            ]);

            return;
        }

        // Skip if already failed
        if ($payment->status === 'failed') {
            return;
        }

        $payment->update([
            'status' => 'failed',
            'notes' => 'Payment failed via webhook: '.($data['failure_reason'] ?? 'Unknown reason'),
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook_data' => $data,
                'webhook_received_at' => now()->toISOString(),
                'webhook_event' => 'charge.failed',
            ]),
        ]);

        Log::info('Payment marked as failed via webhook', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'reason' => $data['failure_reason'] ?? 'Unknown',
        ]);
    }

    protected function handleChargeRefund(array $data, string $reference): void
    {
        $payment = Payment::where('gateway_reference', $reference)
            ->orWhere('transaction_id', $reference)
            ->first();

        if (! $payment) {
            Log::warning('Payment not found for refund', [
                'reference' => $reference,
            ]);

            return;
        }

        $payment->update([
            'status' => 'refunded',
            'notes' => 'Payment refunded via webhook',
            'metadata' => array_merge($payment->metadata ?? [], [
                'webhook_data' => $data,
                'webhook_received_at' => now()->toISOString(),
                'webhook_event' => 'charge.refunded',
                'refund_amount' => $data['amount'] ?? null,
            ]),
        ]);

        // Cancel the subscription if it exists
        if ($payment->subscription) {
            $payment->subscription->cancel();
        }

        Log::info('Payment marked as refunded via webhook', [
            'payment_id' => $payment->id,
            'reference' => $reference,
        ]);
    }

    protected function createPaymentFromWebhook(array $data, string $reference): void
    {
        // Try to find subscription plan based on metadata or amount
        $subscriptionPlan = null;
        $amountInCents = $data['amount'] ?? 0;
        $amount = $amountInCents / 100; // Convert back to base currency

        // Look for subscription plan by amount (approximate match)
        if ($amount > 0) {
            $subscriptionPlan = \App\Models\SubscriptionPlan::where('price', $amount)
                ->active()
                ->first();
        }

        // Get user ID from webhook metadata
        $userId = $data['metadata']['user_id'] ?? null;
        if (! $userId) {
            Log::error('Cannot create payment from webhook without user_id', [
                'reference' => $reference,
            ]);

            return;
        }

        // Create payment record
        $payment = Payment::create([
            'user_id' => $userId,
            'subscription_plan_id' => $subscriptionPlan?->id,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'USD',
            'payment_method' => 'lahza',
            'status' => 'completed',
            'transaction_id' => $reference,
            'gateway_reference' => $reference,
            'paid_at' => now(),
            'notes' => 'Payment created from webhook (charge.success)',
            'metadata' => [
                'webhook_data' => $data,
                'webhook_received_at' => now()->toISOString(),
                'webhook_event' => 'charge.success',
                'source' => 'webhook',
            ],
        ]);

        Log::info('Payment created from webhook', [
            'payment_id' => $payment->id,
            'reference' => $reference,
            'user_id' => $userId,
        ]);

        // Activate subscription if plan exists
        if ($subscriptionPlan) {
            $this->paymentService->confirmPaymentAndActivateSubscription($payment, [
                'notes' => 'Payment confirmed and subscription activated via webhook (created payment)',
                'gateway_response' => $data,
            ]);
        }
    }
}
