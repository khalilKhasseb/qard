<?php

/**
 * Theme Contract Tests
 *
 * These tests ensure API responses match the TypeScript contract.
 * Contract: resources/js/types/contracts/Theme.ts
 */

use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('theme index response matches contract', function () {
    Theme::factory()->count(2)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/themes');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => getThemeContractFields(),
            ],
        ]);
});

it('theme show response matches contract', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/themes/{$theme->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => getThemeContractFields(),
        ]);
});

it('theme create response matches contract', function () {
    $response = $this->postJson('/api/themes', [
        'name' => 'Test Theme',
        'config' => Theme::getDefaultConfig(),
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => getThemeContractFields(),
        ]);
});

it('theme has correct field types', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->user->id,
        'config' => Theme::getDefaultConfig(),
    ]);

    $response = $this->getJson("/api/themes/{$theme->id}");
    $data = $response->json('data');

    // Verify field types match contract
    expect($data['id'])->toBeInt()
        ->and($data['name'])->toBeString()
        ->and($data['is_system_default'])->toBeBool()
        ->and($data['is_public'])->toBeBool()
        ->and($data['config'])->toBeArray()
        ->and($data['used_by_cards_count'])->toBeInt()
        ->and($data['created_at'])->toBeString()
        ->and($data['updated_at'])->toBeString();
});

it('theme config has correct structure', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->user->id,
        'config' => Theme::getDefaultConfig(),
    ]);

    $response = $this->getJson("/api/themes/{$theme->id}");
    $config = $response->json('data.config');

    // Verify config structure matches ThemeConfig contract
    expect($config)->toHaveKeys(['colors', 'fonts', 'images', 'layout', 'custom_css'])
        ->and($config['colors'])->toHaveKeys(['primary', 'secondary', 'background', 'text', 'card_bg', 'border'])
        ->and($config['fonts'])->toHaveKeys(['heading', 'body'])
        ->and($config['layout'])->toHaveKeys(['card_style', 'border_radius', 'alignment', 'spacing']);
});

/**
 * Get expected theme contract fields.
 * Must match: resources/js/types/contracts/Theme.ts > Theme
 */
function getThemeContractFields(): array
{
    return [
        'id',
        'user_id',
        'name',
        'is_system_default',
        'is_public',
        'config',
        'preview_image',
        'used_by_cards_count',
        'created_at',
        'updated_at',
    ];
}
