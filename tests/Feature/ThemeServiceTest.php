<?php

use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'subscription_tier' => 'pro',
    ]);
    $this->themeService = app(ThemeService::class);
});

test('user can create theme', function () {
    $theme = $this->themeService->createTheme($this->user, [
        'name' => 'My Custom Theme',
        'is_public' => false,
    ]);

    expect($theme)->toBeInstanceOf(Theme::class)
        ->and($theme->name)->toBe('My Custom Theme')
        ->and($theme->user_id)->toBe($this->user->id)
        ->and($theme->config)->toBeArray();
});

test('theme has default config when created', function () {
    $theme = $this->themeService->createTheme($this->user, [
        'name' => 'Test Theme',
    ]);

    expect($theme->config)->toHaveKey('colors')
        ->and($theme->config)->toHaveKey('fonts')
        ->and($theme->config)->toHaveKey('layout')
        ->and($theme->config['colors']['primary'])->toBe('#2563eb');
});

test('theme css generation works', function () {
    $theme = Theme::factory()->create([
        'config' => [
            'colors' => ['primary' => '#ff0000', 'secondary' => '#00ff00', 'background' => '#ffffff', 'text' => '#000000', 'card_bg' => '#f0f0f0'],
            'fonts' => ['heading' => 'Arial', 'body' => 'Helvetica'],
            'images' => [],
            'layout' => ['card_style' => 'elevated', 'border_radius' => '8px', 'alignment' => 'center', 'spacing' => 'normal'],
            'custom_css' => '',
        ],
    ]);

    $css = $this->themeService->generateCSS($theme);

    expect($css)->toContain('--primary: #ff0000')
        ->and($css)->toContain('font-family: Helvetica')
        ->and($css)->toContain('border-radius: 8px');
});

test('free user cannot exceed theme limit', function () {
    $freeUser = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    // Create one theme (the limit for free users)
    Theme::factory()->create(['user_id' => $freeUser->id]);

    expect(fn () => $this->themeService->createTheme($freeUser, ['name' => 'Second Theme']))
        ->toThrow(Exception::class, 'Theme limit reached');
});

test('theme can be duplicated', function () {
    $originalTheme = Theme::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Original Theme',
        'config' => Theme::getDefaultConfig(),
    ]);

    $duplicatedTheme = $this->themeService->duplicateTheme($originalTheme, $this->user);

    expect($duplicatedTheme->name)->toBe('Original Theme (Copy)')
        ->and($duplicatedTheme->config)->toEqual($originalTheme->config)
        ->and($duplicatedTheme->is_public)->toBeFalse();
});
