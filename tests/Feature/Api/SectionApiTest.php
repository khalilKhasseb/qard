<?php

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['subscription_tier' => 'pro']);
    $this->card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
});

test('api: user can create section for their card', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.sections.store', $this->card), [
            'section_type' => 'contact',
            'title' => 'Contact Info',
            'content' => ['email' => 'test@example.com'],
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'section_type',
                'title',
                'content',
            ],
        ]);
});

test('api: section creation validates required fields', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.sections.store', $this->card), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['section_type', 'title']);
});

test('api: user cannot create section for other users card', function () {
    $otherCard = BusinessCard::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.sections.store', $otherCard), [
            'section_type' => 'contact',
            'title' => 'Test',
        ])
        ->assertForbidden();
});

test('api: user can update their card section', function () {
    $section = CardSection::factory()->create([
        'business_card_id' => $this->card->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.sections.update', $section), [
            'title' => 'Updated Title',
        ]);

    $response->assertOk();
});

test('api: user cannot update other users card section', function () {
    $otherCard = BusinessCard::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);
    $section = CardSection::factory()->create([
        'business_card_id' => $otherCard->id,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.sections.update', $section), [
            'title' => 'Updated',
        ])
        ->assertForbidden();
});

test('api: user can delete their card section', function () {
    $section = CardSection::factory()->create([
        'business_card_id' => $this->card->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.sections.destroy', $section));

    $response->assertOk();
    $this->assertDatabaseMissing('card_sections', ['id' => $section->id]);
});

test('api: user can reorder card sections', function () {
    $sections = CardSection::factory()->count(3)->create([
        'business_card_id' => $this->card->id,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.sections.reorder', $this->card), [
            'section_ids' => $sections->pluck('id')->reverse()->toArray(),
        ]);

    $response->assertOk();
});

test('api: section requires authentication', function () {
    $this->postJson(route('api.sections.store', $this->card), [])
        ->assertUnauthorized();
});
