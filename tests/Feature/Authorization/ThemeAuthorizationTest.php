<?php

use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('auth: user can view their own theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    expect($this->user->can('view', $theme))->toBeTrue();
});

test('auth: user can view public theme', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->otherUser->id,
        'is_public' => true,
    ]);

    expect($this->user->can('view', $theme))->toBeTrue();
});

test('auth: user cannot view private theme of other user', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->otherUser->id,
        'is_public' => false,
    ]);

    expect($this->user->can('view', $theme))->toBeFalse();
});

test('auth: user can update their own theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    expect($this->user->can('update', $theme))->toBeTrue();
});

test('auth: user cannot update other users theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->otherUser->id]);

    expect($this->user->can('update', $theme))->toBeFalse();
});

test('auth: user cannot update system theme', function () {
    $theme = Theme::factory()->create([
        'is_system_default' => true,
        'user_id' => null,
    ]);

    expect($this->user->can('update', $theme))->toBeFalse();
});

test('auth: user can delete their own theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->user->id]);

    expect($this->user->can('delete', $theme))->toBeTrue();
});

test('auth: user cannot delete other users theme', function () {
    $theme = Theme::factory()->create(['user_id' => $this->otherUser->id]);

    expect($this->user->can('delete', $theme))->toBeFalse();
});

test('auth: user cannot delete system theme', function () {
    $theme = Theme::factory()->create([
        'is_system_default' => true,
        'user_id' => null,
    ]);

    expect($this->user->can('delete', $theme))->toBeFalse();
});
