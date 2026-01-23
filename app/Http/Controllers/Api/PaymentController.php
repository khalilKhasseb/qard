<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SubscriptionPlanResource;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function plans(): AnonymousResourceCollection
    {
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return SubscriptionPlanResource::collection($plans);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'subscription_plan_id' => 'required|exists:subscription_plans,id',
            'payment_method' => 'required|in:cash',
            'notes' => 'nullable|string|max:1000',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        $payment = $this->paymentService->createPayment(
            $request->user(),
            $plan,
            $validated['payment_method'],
            $validated['notes'] ?? null
        );

        // If request expects JSON, return JSON
        if ($request->wantsJson()) {
            return (new PaymentResource($payment->load('subscriptionPlan')))
                ->response()
                ->setStatusCode(201);
        }

        // Otherwise redirect to confirmation page (for Inertia)
        return redirect()->route('payments.confirmation', $payment->id);
    }

    public function confirm(Request $request, Payment $payment): JsonResponse
    {
        // Only admins can confirm payments
        if (! $request->user()->isAdmin()) {
            $this->authorize('view', $payment);
        }

        // If payment is pending, confirm it
        if ($payment->isPending()) {
            $this->paymentService->confirmPaymentAndActivateSubscription($payment, [
                'notes' => $request->input('notes'),
                'confirmed_by' => $request->user()->name,
            ]);
        }

        return response()->json([
            'payment' => new PaymentResource($payment->load('subscriptionPlan')),
            'message' => 'Payment confirmed successfully',
        ]);
    }

    public function history(Request $request): AnonymousResourceCollection
    {
        $payments = $request->user()
            ->payments()
            ->with('subscriptionPlan')
            ->latest()
            ->paginate(15);

        return PaymentResource::collection($payments);
    }

    public function pending(Request $request): AnonymousResourceCollection
    {
        $payments = $request->user()
            ->payments()
            ->where('status', 'pending')
            ->with('subscriptionPlan')
            ->latest()
            ->get();

        return PaymentResource::collection($payments);
    }
}
