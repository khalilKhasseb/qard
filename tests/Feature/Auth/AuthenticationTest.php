<?php

use App\Models\User;
use App\Settings\AuthSettings;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using email', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'identifier' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can authenticate using phone number', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
    ]);

    $response = $this->post('/login', [
        'identifier' => '+1234567890',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can authenticate using phone number without plus sign', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
    ]);

    $response = $this->post('/login', [
        'identifier' => '1234567890',
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'identifier' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});

test('email login can be disabled via settings', function () {
    $user = User::factory()->create();

    $authSettings = app(AuthSettings::class);
    $authSettings->allow_email_login = false;
    $authSettings->save();

    $response = $this->post('/login', [
        'identifier' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('identifier');

    // Restore settings
    $authSettings->allow_email_login = true;
    $authSettings->save();
});

test('phone login can be disabled via settings', function () {
    $user = User::factory()->create([
        'phone' => '+1234567890',
    ]);

    $authSettings = app(AuthSettings::class);
    $authSettings->allow_phone_login = false;
    $authSettings->save();

    $response = $this->post('/login', [
        'identifier' => '+1234567890',
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors('identifier');

    // Restore settings
    $authSettings->allow_phone_login = true;
    $authSettings->save();
});
