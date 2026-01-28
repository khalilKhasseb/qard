<?php

use App\Models\Theme;
use App\Models\User;
use App\Services\ThemeService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'is_admin' => true,
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
        ->and($css)->toContain('border-radius: 8px')
        ->and($css)->toContain('.card-viewer {');
});

test('theme css generation includes background image', function () {
    $theme = Theme::factory()->create([
        'config' => [
            'images' => [
                'background' => [
                    'url' => 'https://example.com/bg.jpg',
                ],
            ],
        ],
    ]);

    $css = $this->themeService->generateCSS($theme);

    expect($css)->toContain('background-image: url(https://example.com/bg.jpg)')
        ->and($css)->toContain('.card-viewer {');
});

test('theme css generation applies overrides', function () {
    $theme = Theme::factory()->create([
        'config' => [
            'colors' => ['primary' => '#ff0000', 'background' => '#ffffff'],
            'fonts' => ['body' => 'Helvetica'],
            'layout' => ['border_radius' => '8px'],
        ],
    ]);

    $overrides = [
        'colors' => ['primary' => '#0000ff'],
        'layout' => ['border_radius' => '20px'],
    ];

    $css = $this->themeService->generateCSS($theme, $overrides);

    expect($css)->toContain('--primary: #0000ff') // Overridden
        ->and($css)->toContain('--primary-soft: #0000ff10') // Derived from override
        ->and($css)->toContain('--background: #ffffff') // From theme
        ->and($css)->toContain('font-family: Helvetica') // From theme
        ->and($css)->toContain('border-radius: 20px'); // Overridden
});

test('theme css generation handles null values in config', function () {
    $theme = Theme::factory()->create([
        'config' => [
            'colors' => [
                'primary' => '#ff0000',
                'secondary' => null, // null color
            ],
            'fonts' => [
                'body' => 'Arial',
                'body_url' => null, // null URL
            ],
            'custom_css' => null, // null CSS
        ],
    ]);

    $css = $this->themeService->generateCSS($theme);

    expect($css)->toContain('--primary: #ff0000')
        ->and($css)->toContain('--secondary: ')
        ->and($css)->not->toContain('/* Custom CSS */');
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
