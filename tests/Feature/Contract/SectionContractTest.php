<?php

/**
 * Section Contract Tests
 *
 * These tests ensure API responses match the TypeScript contract.
 * Contract: resources/js/types/contracts/Section.ts
 */

use App\Enums\SectionType;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->card = BusinessCard::factory()->create(['user_id' => $this->user->id]);
    $this->actingAs($this->user);
});

it('section in card response matches contract', function () {
    CardSection::factory()->count(2)->create([
        'business_card_id' => $this->card->id,
    ]);

    $response = $this->getJson("/api/cards/{$this->card->id}");

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'sections' => [
                    '*' => getSectionContractFields(),
                ],
            ],
        ]);
});

it('section create response matches contract', function () {
    $response = $this->postJson("/api/cards/{$this->card->id}/sections", [
        'section_type' => SectionType::Contact->value,
        'title' => ['en' => 'Test Section'],
        'content' => ['en' => ['text' => 'Hello World']],
        'is_active' => true,
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => getSectionContractFields(),
        ]);
});

it('section create accepts all section types from enum', function () {
    // Test a sample of section types to ensure enum validation works
    $typesToTest = [
        SectionType::Text,
        SectionType::Gallery,
        SectionType::Video,
        SectionType::Links,
        SectionType::About,
        SectionType::Custom,
    ];

    foreach ($typesToTest as $type) {
        $response = $this->postJson("/api/cards/{$this->card->id}/sections", [
            'section_type' => $type->value,
            'title' => ['en' => "Test {$type->value}"],
            'is_active' => true,
        ]);

        $response->assertSuccessful();
        expect($response->json('data.section_type'))->toBe($type->value);
    }
});

it('section update response matches contract', function () {
    $section = CardSection::factory()->create([
        'business_card_id' => $this->card->id,
    ]);

    $response = $this->putJson("/api/sections/{$section->id}", [
        'title' => ['en' => 'Updated Title'],
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'data' => getSectionContractFields(),
        ]);
});

it('section has correct field types', function () {
    $section = CardSection::factory()->create([
        'business_card_id' => $this->card->id,
        'title' => ['en' => 'Test'],
        'content' => ['en' => ['text' => 'Content']],
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $response = $this->getJson("/api/cards/{$this->card->id}");
    $sectionData = $response->json('data.sections.0');

    // Verify field types match contract
    expect($sectionData['id'])->toBeInt()
        ->and($sectionData['business_card_id'])->toBeInt()
        ->and($sectionData['section_type'])->toBeString()
        ->and($sectionData['sort_order'])->toBeInt()
        ->and($sectionData['is_active'])->toBeBool()
        ->and($sectionData['created_at'])->toBeString()
        ->and($sectionData['updated_at'])->toBeString();
});

it('section uses correct field names (not legacy names)', function () {
    $section = CardSection::factory()->create([
        'business_card_id' => $this->card->id,
    ]);

    $response = $this->getJson("/api/cards/{$this->card->id}");
    $sectionData = $response->json('data.sections.0');

    // Contract fields (correct)
    expect($sectionData)->toHaveKey('business_card_id')
        ->and($sectionData)->toHaveKey('sort_order')
        ->and($sectionData)->toHaveKey('image_path')
        ->and($sectionData)->toHaveKey('image_url');

    // Legacy fields should NOT exist
    expect($sectionData)->not->toHaveKey('card_id')
        ->and($sectionData)->not->toHaveKey('section_order');
});

/**
 * Get expected section contract fields.
 * Must match: resources/js/types/contracts/Section.ts > Section
 */
function getSectionContractFields(): array
{
    return [
        'id',
        'business_card_id',
        'section_type',
        'title',
        'content',
        'image_path',
        'image_url',
        'sort_order',
        'is_active',
        'metadata',
        'created_at',
        'updated_at',
    ];
}
