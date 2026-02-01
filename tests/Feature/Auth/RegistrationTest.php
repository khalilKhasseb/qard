<?php

use App\Models\SubscriptionPlan;
use App\Settings\AuthSettings;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register with email verification', function () {
    $plan = SubscriptionPlan::factory()->create(['is_active' => true]);

    // Mock email verification as default
    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'email';
    $authSettings->save();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
        'plan_id' => $plan->id,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('new users can register with phone verification', function () {
    $plan = SubscriptionPlan::factory()->create(['is_active' => true]);

    // Set phone verification
    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'phone';
    $authSettings->save();

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test2@example.com',
        'phone' => '+1234567891',
        'password' => 'password',
        'password_confirmation' => 'password',
        'plan_id' => $plan->id,
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('phone.verification.notice', absolute: false));
});

test('registration requires plan selection', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('plan_id');
});
