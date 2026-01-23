<?php

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->plan = SubscriptionPlan::factory()->create();

    // Mock the payment service to use cash gateway
    config(['payments.gateway' => 'cash']);
});

// Payment Endpoints
test('api: user can view subscription plans', function () {
    SubscriptionPlan::factory()->count(3)->create(['is_active' => true]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.subscription-plans.index'));

    $response->assertOk()
        ->assertJsonCount(4, 'data'); // 3 + 1 from beforeEach
});

test('api: subscription plans requires authentication', function () {
    $this->getJson(route('api.subscription-plans.index'))
        ->assertUnauthorized();
});

test('api: user can create payment', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.payments.create'), [
            'subscription_plan_id' => $this->plan->id,
            'payment_method' => 'cash',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'transaction_id',
                'amount',
                'status',
            ],
        ]);
});

test('api: payment creation validates required fields', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.payments.create'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['subscription_plan_id', 'payment_method']);
});

test('api: payment creation requires authentication', function () {
    $this->postJson(route('api.payments.create'), [
        'subscription_plan_id' => $this->plan->id,
    ])->assertUnauthorized();
});

test('api: admin can confirm payment', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $payment = Payment::factory()->forPlan($this->plan)->create([
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson(route('api.payments.confirm', $payment), [
            'notes' => 'Payment verified',
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('payments', [
        'id' => $payment->id,
        'status' => 'completed',
    ]);
});

test('api: regular user cannot confirm payment', function () {
    $payment = Payment::factory()->create([
        'user_id' => User::factory()->create()->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.payments.confirm', $payment))
        ->assertForbidden();
});

test('api: user can view payment history', function () {
    Payment::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.payments.history'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('api: user only sees their own payment history', function () {
    Payment::factory()->count(2)->create(['user_id' => $this->user->id]);
    Payment::factory()->count(3)->create(['user_id' => User::factory()->create()->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.payments.history'));

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('api: user can view pending payments', function () {
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'pending',
    ]);
    Payment::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.payments.pending'));

    $response->assertOk()
        ->assertJsonCount(1, 'data');
});

test('api: payment history requires authentication', function () {
    $this->getJson(route('api.payments.history'))
        ->assertUnauthorized();
});
