<?php

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\PaymentService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'subscription_tier' => 'free',
        'subscription_status' => 'pending',
    ]);
    $this->plan = SubscriptionPlan::factory()->create([
        'slug' => 'pro',
        'price' => 29.99,
    ]);
    $this->service = app(PaymentService::class);
});

test('flow: complete subscription upgrade flow', function () {
    // Step 1: User creates payment
    $payment = $this->service->createSubscriptionPayment(
        $this->user,
        $this->plan,
        'cash'
    );
    
    expect($payment->status)->toBe('pending');
    expect($payment->amount)->toBe(29.99);
    
    // Step 2: User remains on free tier
    $this->user->refresh();
    expect($this->user->subscription_tier)->toBe('free');
    
    // Step 3: Admin confirms payment
    $this->service->confirmPaymentAndActivateSubscription($payment);
    
    // Step 4: User subscription is activated
    $this->user->refresh();
    expect($this->user->subscription_tier)->toBe('pro');
    expect($this->user->subscription_status)->toBe('active');
    expect($payment->fresh()->status)->toBe('completed');
});

test('flow: user receives notification after payment confirmation', function () {
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'pending',
    ]);
    
    Notification::fake();
    
    $this->service->confirmPaymentAndActivateSubscription($payment);
    
    Notification::assertSentTo(
        $this->user,
        \App\Notifications\PaymentConfirmedNotification::class
    );
});

test('flow: subscription expiry date is set correctly', function () {
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'pending',
    ]);
    
    $this->service->confirmPaymentAndActivateSubscription($payment);
    
    $this->user->refresh();
    expect($this->user->subscription_expires_at)->not->toBeNull();
});

test('flow: payment history is maintained', function () {
    // Create multiple payments
    $payment1 = $this->service->createSubscriptionPayment($this->user, $this->plan, 'cash');
    $this->service->confirmPaymentAndActivateSubscription($payment1);
    
    $payment2 = $this->service->createSubscriptionPayment($this->user, $this->plan, 'cash');
    $this->service->confirmPaymentAndActivateSubscription($payment2);
    
    $history = $this->service->getUserPaymentHistory($this->user);
    
    expect($history)->toHaveCount(2);
    expect($history->every(fn($p) => $p->status === 'completed'))->toBeTrue();
});

test('flow: user tier limits are updated after upgrade', function () {
    $this->user->update(['subscription_tier' => 'free']);
    
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'pending',
    ]);
    
    // Free user has 1 card limit
    expect($this->user->getCardLimit())->toBe(1);
    
    $this->service->confirmPaymentAndActivateSubscription($payment);
    $this->user->refresh();
    
    // Pro user has higher limit
    expect($this->user->getCardLimit())->toBeGreaterThan(1);
});

test('flow: failed payment does not activate subscription', function () {
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'failed',
    ]);
    
    $this->user->refresh();
    expect($this->user->subscription_tier)->toBe('free');
    expect($this->user->subscription_status)->not->toBe('active');
});

test('flow: subscription can be canceled', function () {
    // Activate subscription first
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'pending',
    ]);
    $this->service->confirmPaymentAndActivateSubscription($payment);
    
    // Cancel subscription
    $this->user->refresh();
    $this->user->update(['subscription_status' => 'canceled']);
    
    expect($this->user->subscription_status)->toBe('canceled');
});

test('flow: payment can be refunded', function () {
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
        'status' => 'completed',
    ]);
    
    $payment->update(['status' => 'refunded']);
    
    expect($payment->fresh()->status)->toBe('refunded');
});

test('flow: multiple payment methods are supported', function () {
    $cashPayment = $this->service->createSubscriptionPayment($this->user, $this->plan, 'cash');
    expect($cashPayment->payment_method)->toBe('cash');
    
    $bankPayment = $this->service->createSubscriptionPayment($this->user, $this->plan, 'bank_transfer');
    expect($bankPayment->payment_method)->toBe('bank_transfer');
});

test('flow: user can have pending and completed payments', function () {
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'completed',
    ]);
    
    $allPayments = Payment::where('user_id', $this->user->id)->get();
    expect($allPayments)->toHaveCount(2);
});

test('flow: admin can view all pending payments', function () {
    Payment::factory()->count(5)->create(['status' => 'pending']);
    Payment::factory()->count(3)->create(['status' => 'completed']);
    
    $pending = Payment::where('status', 'pending')->get();
    expect($pending)->toHaveCount(5);
});
