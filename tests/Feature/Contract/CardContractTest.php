<?php

/**
 * Card Contract Tests
 *
 * These tests ensure API responses match the TypeScript contract.
 * Contract: resources/js/types/contracts/Card.ts
 */

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('card index response matches contract', function () {
    BusinessCard::factory()->count(2)->create(['user_id' => $this->user->id]);

    $response = $this->getJson('/api/cards');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                '*' => getCardContractFields(),
            ],
        ]);
});

it('card show response matches contract', function () {
    $card = BusinessCard::factory()
        ->has(CardSection::factory()->count(2), 'sections')
        ->create(['user_id' => $this->user->id]);

    $response = $this->getJson("/api/cards/{$card->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => array_merge(getCardContractFields(), [
                'sections' => [
                    '*' => getSectionContractFields(),
                ],
            ]),
        ]);
});

it('card create response matches contract with multilingual title', function () {
    // Multilingual format - the proper way
    $response = $this->postJson('/api/cards', [
        'title' => ['en' => 'Test Card', 'ar' => 'بطاقة اختبار'],
        'subtitle' => ['en' => 'Test Subtitle'],
        'active_languages' => ['en', 'ar'],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => getCardContractFields(),
        ]);

    // Verify multilingual data is stored correctly
    $data = $response->json('data');
    expect($data['title'])->toBeArray()
        ->and($data['title']['en'])->toBe('Test Card')
        ->and($data['active_languages'])->toContain('en', 'ar');
});

it('card create accepts string title for backward compatibility', function () {
    // String format - backward compatible, converts to multilingual
    $response = $this->postJson('/api/cards', [
        'title' => 'Simple Title',
    ]);

    $response->assertSuccessful();

    // String gets converted to array with default language
    $data = $response->json('data');
    expect($data['title'])->toBeArray()
        ->and($data['title']['en'])->toBe('Simple Title');
});

it('card update response matches contract with multilingual title', function () {
    $card = BusinessCard::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/cards/{$card->id}", [
        'title' => ['en' => 'Updated Title', 'ar' => 'عنوان محدث'],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => getCardContractFields(),
        ]);

    $data = $response->json('data');
    expect($data['title']['en'])->toBe('Updated Title')
        ->and($data['title']['ar'])->toBe('عنوان محدث');
});

it('card has correct field types', function () {
    $card = BusinessCard::factory()->create([
        'user_id' => $this->user->id,
        'title' => ['en' => 'Test'],
        'active_languages' => ['en', 'ar'],
    ]);

    $response = $this->getJson("/api/cards/{$card->id}");
    $data = $response->json('data');

    // Verify field types match contract
    expect($data['id'])->toBeInt()
        ->and($data['user_id'])->toBeInt()
        ->and($data['title'])->toBeArray()
        ->and($data['active_languages'])->toBeArray()
        ->and($data['is_published'])->toBeBool()
        ->and($data['is_primary'])->toBeBool()
        ->and($data['views_count'])->toBeInt()
        ->and($data['shares_count'])->toBeInt()
        ->and($data['full_url'])->toBeString()
        ->and($data['created_at'])->toBeString()
        ->and($data['updated_at'])->toBeString();
});

/**
 * Get expected card contract fields.
 * Must match: resources/js/types/contracts/Card.ts > Card
 */
function getCardContractFields(): array
{
    return [
        'id',
        'user_id',
        'language_id',
        'title',
        'subtitle',
        'cover_image_path',
        'cover_image_url',
        'profile_image_path',
        'profile_image_url',
        'template_id',
        'theme_id',
        'theme_overrides',
        'active_languages',
        'draft_data',
        'custom_slug',
        'share_url',
        'qr_code_url',
        'nfc_identifier',
        'is_published',
        'is_primary',
        'views_count',
        'shares_count',
        'full_url',
        'created_at',
        'updated_at',
        'user',
    ];
}
