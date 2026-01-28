<?php

use App\Models\BusinessCard;
use App\Models\Theme;
use App\Models\User;

use function Pest\Laravel\actingAs;

it('updates theme usage count when theme is updated directly', function () {
    $user = User::factory()->create();
    $theme1 = Theme::factory()->create(['user_id' => $user->id, 'used_by_cards_count' => 0]);
    $theme2 = Theme::factory()->create(['user_id' => $user->id, 'used_by_cards_count' => 0]);

    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'theme_id' => $theme1->id,
    ]);

    $theme1->update(['used_by_cards_count' => 1]);

    actingAs($user)
        ->put(route('cards.update', $card->id), [
            'theme_id' => $theme2->id,
            'title' => ['en' => 'Test Card'],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect($theme2->fresh()->used_by_cards_count)->toBe(1);
    expect($theme1->fresh()->used_by_cards_count)->toBe(0);
});

it('updates theme usage count when theme is updated via draft publishing', function () {
    $user = User::factory()->create();
    $theme1 = Theme::factory()->create(['user_id' => $user->id, 'used_by_cards_count' => 0]);
    $theme2 = Theme::factory()->create(['user_id' => $user->id, 'used_by_cards_count' => 0]);

    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'theme_id' => $theme1->id,
    ]);

    $theme1->update(['used_by_cards_count' => 1]);

    // Save as draft
    actingAs($user)
        ->put(route('cards.update', $card->id), [
            'theme_id' => $theme2->id,
            'save_as_draft' => true,
        ])
        ->assertRedirect();

    // Check draft data
    $card->refresh();
    expect($card->draft_data)->toHaveKey('theme_id', (string) $theme2->id);
    expect($theme2->fresh()->used_by_cards_count)->toBe(0); // Not applied yet

    // Publish draft
    actingAs($user)
        ->post(route('cards.publish-draft', $card->id))
        ->assertRedirect();

    expect($card->fresh()->theme_id)->toBe($theme2->id);
    expect($theme2->fresh()->used_by_cards_count)->toBe(1);
    expect($theme1->fresh()->used_by_cards_count)->toBe(0);
});
