<?php

use App\Models\BusinessCard;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('auth: user can view their own card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    expect($this->user->can('view', $card))->toBeTrue();
});

test('auth: user cannot view other users unpublished card', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->otherUser->id,
        'is_published' => false,
    ]);
    
    expect($this->user->can('view', $card))->toBeFalse();
});

test('auth: user can view other users published card', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->otherUser->id,
        'is_published' => true,
    ]);
    
    expect($this->user->can('view', $card))->toBeTrue();
});

test('auth: user can update their own card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    expect($this->user->can('update', $card))->toBeTrue();
});

test('auth: user cannot update other users card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->otherUser->id]);
    
    expect($this->user->can('update', $card))->toBeFalse();
});

test('auth: user can delete their own card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    expect($this->user->can('delete', $card))->toBeTrue();
});

test('auth: user cannot delete other users card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->otherUser->id]);
    
    expect($this->user->can('delete', $card))->toBeFalse();
});
