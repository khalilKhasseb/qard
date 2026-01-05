<?php

use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('security: API endpoints have rate limiting', function () {
    $responses = [];
    
    // Make 100 requests quickly
    for ($i = 0; $i < 100; $i++) {
        $responses[] = $this->actingAs($this->user, 'sanctum')
            ->getJson(route('api.cards.index'));
    }
    
    // At least one should be rate limited (429)
    $rateLimited = collect($responses)->filter(fn($r) => $r->status() === 429);
    
    expect($rateLimited->count())->toBeGreaterThan(0);
})->skip('Rate limiting configuration needed');

test('security: login attempts are rate limited', function () {
    $responses = [];
    
    for ($i = 0; $i < 10; $i++) {
        $responses[] = $this->post(route('login'), [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);
    }
    
    $lastResponse = end($responses);
    expect($lastResponse->status())->toBe(429);
})->skip('Rate limiting configuration needed');

test('security: public analytics tracking is rate limited', function () {
    for ($i = 0; $i < 200; $i++) {
        $response = $this->postJson(route('api.analytics.track'), [
            'event_type' => 'view',
            'card_id' => 1,
        ]);
    }
    
    expect($response->status())->toBe(429);
})->skip('Rate limiting configuration needed');
