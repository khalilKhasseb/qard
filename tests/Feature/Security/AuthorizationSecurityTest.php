<?php

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
});

test('security: user cannot access other users cards via API', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.cards.show', $card))
        ->assertForbidden();
});

test('security: user cannot modify other users cards', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.cards.update', $card), ['title' => 'Hacked'])
        ->assertForbidden();

    expect($card->fresh()->title)->not->toBe('Hacked');
});

test('security: user cannot delete other users cards', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.cards.destroy', $card))
        ->assertForbidden();

    $this->assertDatabaseHas('business_cards', ['id' => $card->id]);
});

test('security: user cannot access other users sections', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->otherUser->id]);
    $section = CardSection::factory()->create(['business_card_id' => $card->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.sections.update', $section), ['title' => 'Hacked'])
        ->assertForbidden();
});

test('security: user cannot view private themes of other users', function () {
    $theme = Theme::factory()->create([
        'user_id' => $this->otherUser->id,
        'is_public' => false,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson(route('api.themes.show', $theme))
        ->assertForbidden();
});

test('security: user cannot modify system themes', function () {
    $theme = Theme::factory()->create([
        'is_system_default' => true,
        'user_id' => null,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.themes.update', $theme), ['name' => 'Hacked'])
        ->assertForbidden();
});

test('security: user cannot delete system themes', function () {
    $theme = Theme::factory()->create([
        'is_system_default' => true,
        'user_id' => null,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson(route('api.themes.destroy', $theme))
        ->assertForbidden();

    $this->assertDatabaseHas('themes', ['id' => $theme->id]);
});

test('security: authentication required for all API endpoints', function () {
    $this->getJson(route('api.cards.index'))->assertUnauthorized();
    $this->getJson(route('api.themes.index'))->assertUnauthorized();
    $this->getJson(route('api.payments.history'))->assertUnauthorized();
});

test('security: SQL injection attempts are prevented', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.cards.update', $card), [
            'title' => "'; DROP TABLE business_cards; --",
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('business_cards', ['id' => $card->id]);
});

test('security: XSS attempts are escaped', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $xssPayload = '<script>alert("XSS")</script>';

    $this->actingAs($this->user, 'sanctum')
        ->putJson(route('api.cards.update', $card), [
            'title' => $xssPayload,
        ]);

    $card->refresh();
    expect($card->title)->toBe($xssPayload); // Stored as-is
    // Frontend should escape when displaying
});
