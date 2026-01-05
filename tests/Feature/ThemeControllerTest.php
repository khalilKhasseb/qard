<?php

use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'subscription_tier' => 'pro',
    ]);
});

test('user can view themes index', function () {
    $this->actingAs($this->user)
        ->get(route('themes.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            ->has('themes')
        );
});

test('user can view theme create page', function () {
    $this->actingAs($this->user)
        ->get(route('themes.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Themes/Create')
        );
});

test('user can create theme', function () {
    $response = $this->actingAs($this->user)
        ->post(route('themes.store'), [
            'name' => 'My Custom Theme',
            'config' => [
                'colors' => [
                    'primary' => '#3b82f6',
                    'secondary' => '#1e40af',
                    'background' => '#ffffff',
                    'text' => '#1f2937',
                    'card_bg' => '#f9fafb',
                ],
                'fonts' => [
                    'heading' => 'Inter',
                    'body' => 'Inter',
                ],
                'images' => [],
                'layout' => [
                    'card_style' => 'elevated',
                    'border_radius' => '12px',
                    'alignment' => 'center',
                    'spacing' => 'normal',
                ],
                'custom_css' => '',
            ],
            'is_public' => false,
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('themes', [
        'user_id' => $this->user->id,
        'name' => 'My Custom Theme',
    ]);
});

test('user can edit their own theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->user->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('themes.edit', $theme))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Themes/Edit')
            ->has('theme')
        );
});

test('user cannot edit someone elses theme', function () {
    $otherUser = User::factory()->create();
    $theme = Theme::factory()->create([
        'user_id' => $otherUser->id,
    ]);

    $this->actingAs($this->user)
        ->get(route('themes.edit', $theme))
        ->assertForbidden();
});

test('user can update their theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Old Name',
    ]);

    $this->actingAs($this->user)
        ->put(route('themes.update', $theme), [
            'name' => 'New Name',
        ]);

    $this->assertDatabaseHas('themes', [
        'id' => $theme->id,
        'name' => 'New Name',
    ]);
});

test('user can delete unused theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->user->id,
        'used_by_cards_count' => 0,
    ]);

    $this->actingAs($this->user)
        ->delete(route('themes.destroy', $theme))
        ->assertRedirect();

    $this->assertDatabaseMissing('themes', [
        'id' => $theme->id,
    ]);
});

test('user cannot delete theme in use', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->user->id,
        'used_by_cards_count' => 5,
    ]);

    $this->actingAs($this->user)
        ->delete(route('themes.destroy', $theme))
        ->assertSessionHasErrors();

    $this->assertDatabaseHas('themes', [
        'id' => $theme->id,
    ]);
});
