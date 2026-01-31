<?php

use App\Models\BusinessCard;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;

beforeEach(function () {
    $this->user = User::factory()->create();

    // Create a pro subscription plan and assign to user
    $proPlan = SubscriptionPlan::factory()->pro()->create();
    UserSubscription::factory()->active()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $proPlan->id,
    ]);
});

// Cards CRUD - Index
test('api: user can list their cards', function () {
    BusinessCard::factory()->count(3)->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.cards.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('api: cards index requires authentication', function () {
    $this->getJson(route('api.cards.index'))
        ->assertUnauthorized();
});

// Cards CRUD - Store
test('api: user can create card', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.cards.store'), [
            'title' => 'John Doe',
            'subtitle' => 'Software Engineer',
            'is_published' => false,
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'subtitle',
                'is_published',
            ],
        ]);
});

test('api: card creation validates required fields', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.cards.store'), []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['title']);
});

test('api: card creation requires authentication', function () {
    $this->postJson(route('api.cards.store'), [
        'title' => 'Test',
    ])->assertUnauthorized();
});

// Cards CRUD - Show
test('api: user can view their own card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.cards.show', $card));

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $card->id,
                'title' => $card->title,
            ],
        ]);
});

test('api: user cannot view other users card', function () {
    $otherUser = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.cards.show', $card))
        ->assertForbidden();
});

test('api: card show returns 404 for non-existent card', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.cards.show', 99999))
        ->assertNotFound();
});

// Cards CRUD - Update
test('api: user can update their own card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.cards.update', $card), [
            'title' => ['en' => 'Updated Title'],
        ]);

    $response->assertOk();
    expect($response->json('data.title.en'))->toBe('Updated Title');
});

test('api: user cannot update other users card', function () {
    $otherUser = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.cards.update', $card), [
            'title' => 'Updated',
        ])
        ->assertForbidden();
});

test('api: card update validates data', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.cards.update', $card), [
            'title' => '', // Invalid
        ])
        ->assertUnprocessable();
});

// Cards CRUD - Delete
test('api: user can delete their own card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.cards.destroy', $card));

    $response->assertOk();
    $this->assertDatabaseMissing('business_cards', ['id' => $card->id]);
});

test('api: user cannot delete other users card', function () {
    $otherUser = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.cards.destroy', $card))
        ->assertForbidden();
});

// Additional Card Endpoints
test('api: user can publish card', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'is_published' => false,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.cards.publish', $card), [
            'is_published' => true,
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('business_cards', [
        'id' => $card->id,
        'is_published' => true,
    ]);
});

test('api: user can duplicate card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.cards.duplicate', $card));

    $response->assertCreated();
    $this->assertDatabaseCount('business_cards', 2);
});

test('api: user can view card analytics', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.cards.analytics', $card));

    $response->assertOk()
        ->assertJsonStructure([
            'card_id',
            'analytics',
        ]);
});
