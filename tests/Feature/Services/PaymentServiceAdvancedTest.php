<?php

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\PaymentService;

beforeEach(function () {
    $this->service = app(PaymentService::class);
    $this->user = User::factory()->create();
    $this->plan = SubscriptionPlan::factory()->create([
        'price' => 29.99,
        'slug' => 'pro',
    ]);
});

test('service: payment creates with correct amount', function () {
    // Use cash gateway for this test
    $this->service->setGateway(new \App\Services\CashPaymentGateway);

    $payment = $this->service->createSubscriptionPayment(
        $this->user,
        $this->plan,
        ['currency' => 'USD']
    );

    expect((float) $payment->amount)->toBe(29.99);
    expect($payment->status)->toBe('pending');
});

test('service: payment has unique transaction ID', function () {
    // Use cash gateway for this test
    $this->service->setGateway(new \App\Services\CashPaymentGateway);

    $payment1 = $this->service->createSubscriptionPayment($this->user, $this->plan, ['currency' => 'USD']);
    $payment2 = $this->service->createSubscriptionPayment($this->user, $this->plan, ['currency' => 'USD']);

    expect($payment1->transaction_id)->not->toBe($payment2->transaction_id);
});

test('service: confirming payment updates user subscription', function () {
    // Use cash gateway for this test
    $this->service->setGateway(new \App\Services\CashPaymentGateway);

    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'pending',
    ]);

    $this->service->confirmPaymentAndActivateSubscription($payment);

    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => 'completed',
    ]);

    $this->user->refresh();
    expect($this->user->subscription_tier)->toBe('pro');
    expect($this->user->subscription_status)->toBe('active');
});

test('service: can get user payment history', function () {
    // Use cash gateway for this test
    $this->service->setGateway(new \App\Services\CashPaymentGateway);

    Payment::factory()->count(3)->create(['user_id' => $this->user->id]);

    $history = $this->service->getPaymentHistory($this->user);

    expect($history)->toHaveCount(3);
});

test('service: can get pending payments for user', function () {
    // Use cash gateway for this test
    $this->service->setGateway(new \App\Services\CashPaymentGateway);

    Payment::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'completed',
    ]);

    $pending = $this->service->getPendingPayments($this->user);

    expect($pending)->toHaveCount(1);
});
