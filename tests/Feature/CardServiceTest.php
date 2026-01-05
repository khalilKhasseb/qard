<?php

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\Template;
use App\Models\User;
use App\Services\CardService;

beforeEach(function () {
    $this->user = User::factory()->create([
        'subscription_tier' => 'pro',
    ]);
    $this->cardService = new CardService();
});

test('user can create card', function () {
    $card = $this->cardService->createCard($this->user, [
        'title' => 'John Doe',
        'subtitle' => 'Software Engineer',
    ]);

    expect($card)->toBeInstanceOf(BusinessCard::class)
        ->and($card->title)->toBe('John Doe')
        ->and($card->subtitle)->toBe('Software Engineer')
        ->and($card->user_id)->toBe($this->user->id)
        ->and($card->share_url)->toHaveLength(10);
});

test('first card is set as primary', function () {
    $card = $this->cardService->createCard($this->user, [
        'title' => 'First Card',
    ]);

    expect($card->is_primary)->toBeTrue();
});

test('subsequent cards are not primary', function () {
    $this->cardService->createCard($this->user, ['title' => 'First Card']);
    $secondCard = $this->cardService->createCard($this->user, ['title' => 'Second Card']);

    expect($secondCard->is_primary)->toBeFalse();
});

test('card has unique share url', function () {
    $card1 = $this->cardService->createCard($this->user, ['title' => 'Card 1']);
    $card2 = $this->cardService->createCard($this->user, ['title' => 'Card 2']);

    expect($card1->share_url)->not->toBe($card2->share_url);
});

test('can add section to card', function () {
    $card = $this->cardService->createCard($this->user, ['title' => 'Test Card']);

    $section = $this->cardService->addSection($card, [
        'section_type' => 'contact',
        'title' => 'Contact Information',
        'content' => [
            'email' => 'john@example.com',
            'phone' => '+1234567890',
        ],
    ]);

    expect($section)->toBeInstanceOf(CardSection::class)
        ->and($section->section_type)->toBe('contact')
        ->and($section->content['email'])->toBe('john@example.com');
});

test('sections are ordered correctly', function () {
    $card = $this->cardService->createCard($this->user, ['title' => 'Test Card']);

    $section1 = $this->cardService->addSection($card, ['section_type' => 'contact', 'title' => 'First']);
    $section2 = $this->cardService->addSection($card, ['section_type' => 'social', 'title' => 'Second']);
    $section3 = $this->cardService->addSection($card, ['section_type' => 'about', 'title' => 'Third']);

    expect($section1->sort_order)->toBe(0)
        ->and($section2->sort_order)->toBe(1)
        ->and($section3->sort_order)->toBe(2);
});

test('can duplicate card', function () {
    $originalCard = $this->cardService->createCard($this->user, [
        'title' => 'Original Card',
        'subtitle' => 'My Subtitle',
    ]);

    $this->cardService->addSection($originalCard, [
        'section_type' => 'contact',
        'title' => 'Contact',
        'content' => ['email' => 'test@example.com'],
    ]);

    $duplicatedCard = $this->cardService->duplicateCard($originalCard);

    expect($duplicatedCard->title)->toBe('Original Card (Copy)')
        ->and($duplicatedCard->subtitle)->toBe('My Subtitle')
        ->and($duplicatedCard->share_url)->not->toBe($originalCard->share_url)
        ->and($duplicatedCard->sections)->toHaveCount(1);
});

test('free user cannot exceed card limit', function () {
    $freeUser = User::factory()->create([
        'subscription_tier' => 'free',
    ]);

    // Create one card (the limit for free users)
    $this->cardService->createCard($freeUser, ['title' => 'First Card']);

    expect(fn () => $this->cardService->createCard($freeUser, ['title' => 'Second Card']))
        ->toThrow(Exception::class, 'Card limit reached');
});

test('can set custom slug', function () {
    $card = $this->cardService->createCard($this->user, ['title' => 'Test Card']);

    $this->cardService->setCustomSlug($card, 'john-doe');

    expect($card->fresh()->custom_slug)->toBe('john-doe');
});

test('duplicate slug throws exception', function () {
    $card1 = $this->cardService->createCard($this->user, ['title' => 'Card 1']);
    $card2 = $this->cardService->createCard($this->user, ['title' => 'Card 2']);

    $this->cardService->setCustomSlug($card1, 'my-slug');

    expect(fn () => $this->cardService->setCustomSlug($card2, 'my-slug'))
        ->toThrow(Exception::class, 'already taken');
});
