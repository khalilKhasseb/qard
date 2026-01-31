<?php

use App\Models\User;
use App\Services\Sms\OtpManager;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

test('registration requires phone number', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertSessionHasErrors('phone');
});

test('registration with valid phone redirects to phone verification', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1234567890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('phone.verification.notice'));

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'phone' => '+1234567890',
    ]);

    // User should be logged in
    $this->assertAuthenticated();

    // Phone should not be verified yet
    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasVerifiedPhone())->toBeFalse();
});

test('phone number is normalized on registration', function () {
    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test2@example.com',
        'phone' => '1 234 567 890',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test2@example.com',
        'phone' => '+1234567890',
    ]);
});

test('phone verification page is accessible after registration', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get(route('phone.verification.notice'));

    $response->assertStatus(200);
    $response->assertInertia(
        fn ($page) => $page
            ->component('Auth/VerifyPhone')
            ->has('phone')
    );
});

test('verified users are redirected from verification page', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('phone.verification.notice'));

    $response->assertRedirect(route('dashboard'));
});

test('can send OTP to phone', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->postJson(route('phone.verification.send'));

    $response->assertOk();
    $response->assertJson(['success' => true]);
});

test('can verify OTP', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    $otpManager = app(OtpManager::class);
    $otpManager->send($user->phone, 'registration');

    // Get the code from cache
    $cacheKey = "otp:registration:{$user->phone}";
    $storedData = Cache::get($cacheKey);

    $response = $this->actingAs($user)->postJson(route('phone.verification.verify'), [
        'code' => $storedData['code'],
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    // Refresh user
    $user->refresh();
    expect($user->hasVerifiedPhone())->toBeTrue();
});

test('rejects invalid OTP', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    $otpManager = app(OtpManager::class);
    $otpManager->send($user->phone, 'registration');

    $response = $this->actingAs($user)->postJson(route('phone.verification.verify'), [
        'code' => '000000',
    ]);

    $response->assertStatus(422);
    $response->assertJson(['success' => false]);
});

test('phone update page is accessible', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    $response = $this->actingAs($user)->get(route('phone.update'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Auth/UpdatePhone'));
});

test('can update phone number', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => now(),
    ]);

    $response = $this->actingAs($user)->post(route('phone.update.store'), [
        'phone' => '+9876543210',
    ]);

    $response->assertRedirect(route('phone.verification.notice'));

    $user->refresh();
    expect($user->phone)->toBe('+9876543210');
    expect($user->phone_verified_at)->toBeNull();
});

test('phone verified middleware blocks unverified users', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    // Create a route with the middleware for testing
    Route::get('/test-phone-verified', function () {
        return 'OK';
    })->middleware(['auth', 'phone.verified']);

    $response = $this->actingAs($user)->get('/test-phone-verified');

    $response->assertRedirect(route('phone.verification.notice'));
});

test('phone verified middleware allows verified users', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => now(),
    ]);

    // Create a route with the middleware for testing
    Route::get('/test-phone-verified-ok', function () {
        return 'OK';
    })->middleware(['auth', 'phone.verified']);

    $response = $this->actingAs($user)->get('/test-phone-verified-ok');

    $response->assertOk();
});
