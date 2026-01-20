<?php

use App\Models\BusinessCard;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['subscription_tier' => 'pro']);
});

// Themes CRUD - Index
test('api: user can list themes', function () {
    Theme::factory()->count(3)->create(['user_id' => $this->user->id]);
    Theme::factory()->count(2)->create(['is_public' => true]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.themes.index'));

    $response->assertOk()
        ->assertJsonCount(5, 'data');
});

test('api: themes index requires authentication', function () {
    $this->getJson(route('api.themes.index'))
        ->assertUnauthorized();
});

// Themes CRUD - Store
test('api: user can create theme', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.store'), [
            'name' => 'My Theme',
            'config' => [
                'colors' => [
                    'primary' => '#000000',
                ],
            ],
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'config',
            ],
        ]);
});

test('api: theme creation validates required fields', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.store'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('api: theme creation requires authentication', function () {
    $this->postJson(route('api.themes.store'), [
        'name' => 'Test',
    ])->assertUnauthorized();
});

// Themes CRUD - Show
test('api: user can view their own theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.themes.show', $theme));

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $theme->id,
                'name' => $theme->name,
            ],
        ]);
});

test('api: user can view public theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => User::factory()->create()->id,
        'is_public' => true,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.themes.show', $theme))
        ->assertOk();
});

test('api: user cannot view private theme of other user', function () {
    $theme = Theme::factory()->create([
        'user_id' => User::factory()->create()->id,
        'is_public' => false,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.themes.show', $theme))
        ->assertForbidden();
});

test('api: theme show returns 404 for non-existent theme', function () {
    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.themes.show', 99999))
        ->assertNotFound();
});

// Themes CRUD - Update
test('api: user can update their own theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.themes.update', $theme), [
            'name' => 'Updated Theme',
        ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'name' => 'Updated Theme',
            ],
        ]);
});

test('api: user cannot update other users theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.themes.update', $theme), [
            'name' => 'Updated',
        ])
        ->assertForbidden();
});

test('api: system themes cannot be updated by regular users', function () {
    $theme = Theme::factory()->create([
        'is_system_default' => true,
        'user_id' => null,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.themes.update', $theme), [
            'name' => 'Updated',
        ])
        ->assertForbidden();
});

// Themes CRUD - Delete
test('api: user can delete their own theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.themes.destroy', $theme));

    $response->assertOk();
    $this->assertDatabaseMissing('themes', ['id' => $theme->id]);
});

test('api: user cannot delete other users theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.themes.destroy', $theme))
        ->assertForbidden();
});

test('api: system themes cannot be deleted', function () {
    $theme = Theme::factory()->create([
        'is_system_default' => true,
        'user_id' => null,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.themes.destroy', $theme))
        ->assertForbidden();
});

// Additional Theme Endpoints
test('api: user can duplicate theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.duplicate', $theme));

    $response->assertCreated();
    $this->assertDatabaseCount('themes', 2);
});

test('api: user can apply theme to card', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.apply', [$theme, $card]));

    $response->assertOk();
    $this->assertDatabaseHas('business_cards', [
        'id' => $card->id,
        'theme_id' => $theme->id,
    ]);
});

test('api: user can upload theme image', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.upload'), [
            'image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

    $response->assertOk();
});

test('api: user can preview theme CSS', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.preview_css'), [
            'config' => [
                'colors' => [
                    'primary' => '#000000',
                ],
            ],
        ]);

    $response->assertOk();
});

test('api: user can generate theme preview', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson(route('api.themes.preview'), [
            'config' => [
                'colors' => [
                    'primary' => '#000000',
                ],
            ],
        ]);

    $response->assertOk();
});
