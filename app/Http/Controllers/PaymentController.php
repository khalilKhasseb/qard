<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $subscription = $user->activeSubscription()
            ->with('subscriptionPlan')
            ->first();

        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        $payments = Payment::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Payments/Index', [
            'subscription' => $subscription,
            'plans' => $plans,
            'payments' => $payments,
        ]);
    }

    public function checkout(Request $request, SubscriptionPlan $plan): Response
    {
        return Inertia::render('Payments/Checkout', [
            'plan' => $plan,
        ]);
    }

    public function confirmation(Request $request, Payment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load('subscriptionPlan');

        return Inertia::render('Payments/Confirmation', [
            'payment' => $payment,
        ]);
    }

    public function initialize(Request $request, SubscriptionPlan $plan)
    {

        \Log::info('CSRF Debug - Payment Initialize', [
            'incoming_x_csrf_token' => $request->header('X-CSRF-TOKEN'),
            'session_token' => $request->session()->token(),
            'tokens_match' => hash_equals($request->session()->token(), $request->header('X-CSRF-TOKEN')),
            'session_id' => $request->session()->getId(),
            'user_id' => $request->user()?->id,
        ]);

        $user = $request->user();

        // Check if user already has an active subscription for this plan
        $existingSubscription = $user->subscriptions()
            ->where('subscription_plan_id', $plan->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return response()->json([
                'message' => 'You already have an active subscription for this plan.',
                'subscription' => $existingSubscription,
            ], 422);
        }

        try {
            $payment = $this->paymentService->initializeLahzaCheckout($user, $plan);

            $checkoutUrl = $this->paymentService->getCheckoutUrlForPayment($payment);

            // Fallback: Try multiple nested locations if getCheckoutUrl failed
            if (! $checkoutUrl) {
                $checkoutUrl = $payment->metadata['authorization_url']
                    ?? $payment->metadata['api_response']['data']['authorization_url']
                    ?? null;
            }

            if (! $checkoutUrl) {
                Log::error('Failed to generate checkout URL', [
                    'payment_id' => $payment->id,
                    'metadata' => $payment->metadata,
                ]);

                return response()->json([
                    'message' => 'Failed to generate checkout URL. Please try again.',
                    'payment' => $payment,
                ], 500);
            }

            Log::info('Payment initialized', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_id' => $payment->id,
                'checkout_url' => $checkoutUrl,
            ]);

            return response()->json([
                'message' => 'Payment initialized successfully.',
                'payment' => $payment,
                'checkout_url' => $checkoutUrl,
                'reference' => $payment->transaction_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Payment initialization failed', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to initialize payment. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (! $reference) {
            return response()->json([
                'message' => 'Missing transaction reference.',
            ], 400);
        }

        try {
            $subscription = $this->paymentService->processCallback($reference);

            if ($subscription) {
                Log::info('Payment callback processed successfully', [
                    'reference' => $reference,
                    'subscription_id' => $subscription->id,
                ]);

                return Inertia::render('Payments/Callback', [
                    'success' => true,
                    'subscription' => $subscription->load('plan'),
                    'message' => 'Payment verified successfully! Your subscription is now active.',
                ]);
            } else {
                Log::warning('Payment callback processing failed', [
                    'reference' => $reference,
                ]);

                return Inertia::render('Payments/Callback', [
                    'success' => false,
                    'message' => 'Payment verification failed or payment is still pending. Please try again or contact support.',
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Payment callback error', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return Inertia::render('Payments/Callback', [
                'success' => false,
                'message' => 'An error occurred while processing your payment. Please contact support.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ]);
        }
    }
}
