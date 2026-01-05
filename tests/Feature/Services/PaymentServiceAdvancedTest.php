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
    $payment = $this->service->createSubscriptionPayment(
        $this->user,
        $this->plan,
        'cash'
    );
    
    expect($payment->amount)->toBe(29.99);
    expect($payment->status)->toBe('pending');
});

test('service: payment has unique transaction ID', function () {
    $payment1 = $this->service->createSubscriptionPayment($this->user, $this->plan, 'cash');
    $payment2 = $this->service->createSubscriptionPayment($this->user, $this->plan, 'cash');
    
    expect($payment1->transaction_id)->not->toBe($payment2->transaction_id);
});

test('service: confirming payment updates user subscription', function () {
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
    Payment::factory()->count(3)->create(['user_id' => $this->user->id]);
    
    $history = $this->service->getUserPaymentHistory($this->user);
    
    expect($history)->toHaveCount(3);
});

test('service: can get pending payments for user', function () {
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
