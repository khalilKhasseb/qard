<?php

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\CashPaymentGateway;
use App\Services\PaymentService;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->plan = SubscriptionPlan::factory()->create([
        'name' => 'Pro',
        'slug' => 'pro',
        'price' => 9.99,
        'billing_cycle' => 'monthly',
    ]);
    $this->paymentService = new PaymentService;
});

test('can create subscription payment', function () {
    $payment = $this->paymentService->createSubscriptionPayment($this->user, $this->plan);

    expect($payment)->toBeInstanceOf(Payment::class)
        ->and($payment->amount)->toBe('9.99')
        ->and($payment->status)->toBe('pending')
        ->and($payment->payment_method)->toBe('cash')
        ->and($payment->subscription_plan_id)->toBe($this->plan->id);
});

test('payment has transaction id', function () {
    $payment = $this->paymentService->createSubscriptionPayment($this->user, $this->plan);

    expect($payment->transaction_id)->toStartWith('TXN-')
        ->and(strlen($payment->transaction_id))->toBe(16);
});

test('can confirm payment and activate subscription', function () {
    $payment = $this->paymentService->createSubscriptionPayment($this->user, $this->plan);

    $subscription = $this->paymentService->confirmPaymentAndActivateSubscription($payment, [
        'confirmed_by' => 'Admin',
        'receipt_number' => 'REC-001',
    ]);

    expect($payment->fresh()->status)->toBe('completed')
        ->and($payment->fresh()->paid_at)->not->toBeNull()
        ->and($subscription->status)->toBe('active')
        ->and($this->user->fresh()->subscription_status)->toBe('active')
        ->and($this->user->fresh()->subscription_tier)->toBe('pro');
});

test('cash gateway creates pending payment', function () {
    $gateway = new CashPaymentGateway;
    $payment = $gateway->createPayment($this->user, 19.99, [
        'subscription_plan_id' => $this->plan->id,
    ]);

    expect($payment->status)->toBe('pending')
        ->and($payment->payment_method)->toBe('cash');
});

test('can get payment history', function () {
    Payment::factory()->count(5)->create(['user_id' => $this->user->id]);

    $history = $this->paymentService->getPaymentHistory($this->user, 3);

    expect($history)->toHaveCount(3);
});
