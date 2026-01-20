<?php

namespace Tests\Feature\Api;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some test languages
        Language::create([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
        ]);

        Language::create([
            'name' => 'Arabic',
            'code' => 'ar',
            'direction' => 'rtl',
            'is_active' => true,
            'is_default' => false,
        ]);

        Language::create([
            'name' => 'French',
            'code' => 'fr',
            'direction' => 'ltr',
            'is_active' => false,
            'is_default' => false,
        ]);
    }

    public function test_get_all_active_languages()
    {
        $response = $this->getJson('/api/language');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'code', 'direction', 'is_active', 'is_default', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_get_specific_language()
    {
        $language = Language::where('code', 'en')->first();

        $response = $this->getJson("/api/language/{$language->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'code' => 'en',
                    'name' => 'English',
                    'direction' => 'ltr',
                ],
            ]);
    }

    public function test_switch_language()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/language/switch', [
                'language_code' => 'ar',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Language switched successfully',
                'language' => [
                    'id' => 2,
                    'name' => 'Arabic',
                    'code' => 'ar',
                    'direction' => 'rtl',
                    'is_active' => true,
                    'is_default' => false,
                ],
            ]);
    }

    public function test_switch_to_invalid_language()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/language/switch', [
                'language_code' => 'xx',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language_code']);
    }

    public function test_switch_to_inactive_language()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/language/switch', [
                'language_code' => 'fr',
            ]);

        $response->assertStatus(422);
    }
}
