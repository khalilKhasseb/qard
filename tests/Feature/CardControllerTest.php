<?php

use App\Models\BusinessCard;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'subscription_tier' => 'pro',
    ]);
});

test('user can view cards index', function () {
    $this->actingAs($this->user)
        ->get(route('cards.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Cards/Index')
            ->has('cards')
        );
});

test('user can view card create page', function () {
    $this->actingAs($this->user)
        ->get(route('cards.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Cards/Create')
            ->has('themes')
        );
});

test('user can create card', function () {
    $language = \App\Models\Language::create([
        'name' => 'English',
        'code' => 'en',
        'direction' => 'ltr',
        'is_active' => true,
    ]);

    $response = $this->actingAs($this->user)
        ->post(route('cards.store'), [
            'title' => 'John Doe',
            'subtitle' => 'Software Engineer',
            'language_id' => $language->id,
            'is_published' => false,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('business_cards', [
        'user_id' => $this->user->id,
        'title' => json_encode(['en' => 'John Doe']),
        'subtitle' => json_encode(['en' => 'Software Engineer']),
    ]);
});

test('user can edit their own card', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('cards.edit', $card))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Cards/Edit')
            ->has('card')
        );
});

test('user cannot edit someone elses card', function () {
    $otherUser = User::factory()->create();
    $card = BusinessCard::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('cards.edit', $card))
        ->assertForbidden();
});

test('user can update their card', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Old Title',
    ]);

    $this->actingAs($this->user)
        ->put(route('cards.update', $card), [
            'title' => ['en' => 'New Title'],
            'active_languages' => ['en', 'ar'],
        ]);

    $this->assertDatabaseHas('business_cards', [
        'id' => $card->id,
        'title' => json_encode(['en' => 'New Title']),
        'active_languages' => json_encode(['en', 'ar']),
    ]);
});

test('user can delete their card', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->delete(route('cards.destroy', $card))
        ->assertRedirect();

    $this->assertDatabaseMissing('business_cards', [
        'id' => $card->id,
    ]);
});

test('guest cannot access card management', function () {
    $this->get(route('cards.index'))
        ->assertRedirect(route('login'));
});
