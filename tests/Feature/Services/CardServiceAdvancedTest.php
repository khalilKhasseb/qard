<?php

use App\Models\BusinessCard;
use App\Models\User;
use App\Services\CardService;

beforeEach(function () {
    $this->service = app(CardService::class);
    $this->user = User::factory()->create(['subscription_tier' => 'pro']);
});

test('service: can generate QR code for card', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $qrUrl = $this->service->generateQrCode($card);
    
    expect($qrUrl)->toBeString();
    expect($card->fresh()->qr_code_url)->not->toBeNull();
});

test('service: can assign NFC identifier', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $this->service->assignNfcIdentifier($card, 'NFC123456');
    
    $this->assertDatabaseHas('business_cards', [
        'id' => $card->id,
        'nfc_identifier' => 'NFC123456',
    ]);
});

test('service: duplicate NFC identifier throws exception', function () {
    $card1 = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    $card2 = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $this->service->assignNfcIdentifier($card1, 'NFC123');
    
    expect(fn() => $this->service->assignNfcIdentifier($card2, 'NFC123'))
        ->toThrow(Exception::class, 'NFC identifier already in use');
});

test('service: can set custom slug', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $this->service->setCustomSlug($card, 'my-custom-slug');
    
    $this->assertDatabaseHas('business_cards', [
        'id' => $card->id,
        'custom_slug' => 'my-custom-slug',
    ]);
});

test('service: duplicate slug throws exception', function () {
    $card1 = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    $card2 = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $this->service->setCustomSlug($card1, 'unique-slug');
    
    expect(fn() => $this->service->setCustomSlug($card2, 'unique-slug'))
        ->toThrow(Exception::class, 'URL is already taken');
});

test('service: can track card view', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    $initialViews = $card->views_count;
    
    $this->service->trackView($card, ['ip' => '127.0.0.1']);
    
    expect($card->fresh()->views_count)->toBe($initialViews + 1);
});

test('service: can track NFC tap', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $this->service->trackNfcTap($card, ['device' => 'iPhone']);
    
    expect($card->fresh()->views_count)->toBeGreaterThan(0);
});

test('service: can track QR scan', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    
    $this->service->trackQrScan($card);
    
    expect($card->fresh()->views_count)->toBeGreaterThan(0);
});

test('service: can track section click', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    $section = $card->sections()->create([
        'section_type' => 'contact',
        'title' => 'Contact',
        'content' => [],
    ]);
    
    $this->service->trackSectionClick($card, $section);
    
    $this->assertDatabaseHas('analytics_events', [
        'card_id' => $card->id,
        'section_id' => $section->id,
        'event_type' => 'section_click',
    ]);
});

test('service: can track social share', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    $initialShares = $card->shares_count;
    
    $this->service->trackShare($card, 'twitter');
    
    expect($card->fresh()->shares_count)->toBe($initialShares + 1);
});

test('service: can get card by slug', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'custom_slug' => 'test-slug',
        'is_published' => true,
    ]);
    
    $found = $this->service->getCardBySlug('test-slug');
    
    expect($found->id)->toBe($card->id);
});

test('service: can get card by share URL', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'is_published' => true,
    ]);
    
    $found = $this->service->getCardByShareUrl($card->share_url);
    
    expect($found->id)->toBe($card->id);
});

test('service: can get card by NFC', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'nfc_identifier' => 'NFC999',
        'is_published' => true,
    ]);
    
    $found = $this->service->getCardByNfc('NFC999');
    
    expect($found->id)->toBe($card->id);
});

test('service: unpublished cards are not retrievable by public methods', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'custom_slug' => 'unpublished',
        'is_published' => false,
    ]);
    
    $found = $this->service->getCardBySlug('unpublished');
    
    expect($found)->toBeNull();
});
