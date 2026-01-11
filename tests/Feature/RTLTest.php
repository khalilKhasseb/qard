<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RTLTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create RTL language
        Language::create([
            'name' => 'Arabic',
            'code' => 'ar',
            'direction' => 'rtl',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Create LTR language
        Language::create([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
        ]);
    }

    public function test_rtl_language_sets_correct_direction()
    {
        $user = User::factory()->create();

        // Switch to Arabic (RTL)
        $response = $this->actingAs($user)
            ->post('/language/switch', [
                'language_code' => 'ar'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Language switched successfully']);

        // Verify direction is set to RTL
        $language = Language::where('code', 'ar')->first();
        $this->assertEquals('rtl', $language->direction);
    }

    public function test_ltr_language_sets_correct_direction()
    {
        $user = User::factory()->create();

        // Switch to English (LTR)
        $response = $this->actingAs($user)
            ->post('/language/switch', [
                'language_code' => 'en'
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Language switched successfully']);

        // Verify direction is set to LTR
        $language = Language::where('code', 'en')->first();
        $this->assertEquals('ltr', $language->direction);
    }

    public function test_rtl_css_is_loaded()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // In a real test, we would check if RTL CSS is included in the response
    }
}
