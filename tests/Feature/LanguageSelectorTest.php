<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageSelectorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test languages
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
    }

    public function test_language_selector_widget_returns_correct_languages()
    {
        // Test that the language model returns active languages correctly
        $languages = \App\Models\Language::active()->get();
        
        $this->assertCount(2, $languages); // English and Arabic are active
        $this->assertContains('English', $languages->pluck('name'));
        $this->assertContains('Arabic', $languages->pluck('name'));
    }

    public function test_language_switching_in_web_interface()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/language/switch', [
                'language_code' => 'ar'
            ]);

        $response->assertStatus(200); // API response
        $response->assertJson(['message' => 'Language switched successfully']);
    }

    public function test_default_language_is_set()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertEquals('en', app()->getLocale());
    }
}
