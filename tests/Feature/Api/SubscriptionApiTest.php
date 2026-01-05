<?php

use App\Models\User;
use App\Models\UserSubscription;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('api: user can view their subscription', function () {
    UserSubscription::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
    ]);
    
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.subscription.show'));
    
    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'status',
                'tier',
            ]
        ]);
});

test('api: subscription show requires authentication', function () {
    $this->getJson(route('api.subscription.show'))
        ->assertUnauthorized();
});

test('api: user can cancel their subscription', function () {
    UserSubscription::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'active',
    ]);
    
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.subscription.cancel'));
    
    $response->assertOk();
    $this->assertDatabaseHas('user_subscriptions', [
        'user_id' => $this->user->id,
        'status' => 'canceled',
    ]);
});

test('api: subscription cancel requires authentication', function () {
    $this->postJson(route('api.subscription.cancel'))
        ->assertUnauthorized();
});
