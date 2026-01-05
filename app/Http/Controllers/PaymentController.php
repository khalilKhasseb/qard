<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Services\PaymentService;
use Illuminate\Http\Request;
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
}
