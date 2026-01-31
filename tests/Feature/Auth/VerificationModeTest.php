<?php

use App\Http\Middleware\EnsureUserIsVerified;
use App\Models\User;
use App\Settings\AuthSettings;
use Illuminate\Http\Request;

beforeEach(function () {
    // Reset auth settings to defaults before each test
    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'email';
    $authSettings->allow_email_login = true;
    $authSettings->allow_phone_login = true;
    $authSettings->save();
});

test('email verification mode redirects unverified users to email verification', function () {
    $user = User::factory()->unverified()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => now(), // Phone verified but email not
    ]);

    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'email';
    $authSettings->save();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('verification.notice'));
});

test('email verification mode allows email-verified users', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
        'phone' => '+1234567890',
        'phone_verified_at' => null, // Phone not verified
    ]);

    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'email';
    $authSettings->save();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('phone verification mode redirects unverified users to phone verification', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(), // Email verified but phone not
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);

    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'phone';
    $authSettings->save();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertRedirect(route('phone.verification.notice'));
});

test('phone verification mode allows phone-verified users', function () {
    $user = User::factory()->unverified()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => now(),
    ]);

    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'phone';
    $authSettings->save();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
});

test('middleware returns 403 for JSON requests from unverified users', function () {
    $user = User::factory()->unverified()->create([
        'phone' => '+1234567890',
    ]);

    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'email';
    $authSettings->save();

    $response = $this->actingAs($user)
        ->getJson('/dashboard');

    $response->assertForbidden();
});

test('auth settings can be updated', function () {
    $authSettings = app(AuthSettings::class);

    $authSettings->verification_method = 'phone';
    $authSettings->allow_email_login = false;
    $authSettings->allow_phone_login = true;
    $authSettings->save();

    // Refresh from database
    $freshSettings = app(AuthSettings::class);

    expect($freshSettings->verification_method)->toBe('phone');
    expect($freshSettings->allow_email_login)->toBeFalse();
    expect($freshSettings->allow_phone_login)->toBeTrue();

    // Restore defaults
    $authSettings->verification_method = 'email';
    $authSettings->allow_email_login = true;
    $authSettings->save();
});

test('EnsureUserIsVerified middleware handles email mode correctly', function () {
    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'email';
    $authSettings->save();

    $middleware = new EnsureUserIsVerified;

    // Unverified user
    $unverifiedUser = User::factory()->unverified()->create();
    $request = Request::create('/dashboard');
    $request->setUserResolver(fn () => $unverifiedUser);

    $response = $middleware->handle($request, function () {
        return response('OK');
    });

    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toContain('verify-email');
});

test('EnsureUserIsVerified middleware handles phone mode correctly', function () {
    $authSettings = app(AuthSettings::class);
    $authSettings->verification_method = 'phone';
    $authSettings->save();

    $middleware = new EnsureUserIsVerified;

    // User with unverified phone
    $user = User::factory()->create([
        'phone' => '+1234567890',
        'phone_verified_at' => null,
    ]);
    $request = Request::create('/dashboard');
    $request->setUserResolver(fn () => $user);

    $response = $middleware->handle($request, function () {
        return response('OK');
    });

    expect($response->isRedirect())->toBeTrue();
    expect($response->getTargetUrl())->toContain('verify-phone');

    // Restore
    $authSettings->verification_method = 'email';
    $authSettings->save();
});

test('guest requests pass through verification middleware', function () {
    $middleware = new EnsureUserIsVerified;
    $request = Request::create('/dashboard');
    $request->setUserResolver(fn () => null);

    $response = $middleware->handle($request, function () {
        return response('OK');
    });

    expect($response->getContent())->toBe('OK');
});
