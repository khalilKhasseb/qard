<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_access_language_resource()
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/languages');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_language()
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/languages', [
                'name' => 'Spanish',
                'code' => 'es',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => false,
            ]);

        $response->assertStatus(302); // Redirect after creation
        $this->assertDatabaseHas('languages', ['code' => 'es']);
    }

    public function test_admin_can_update_language()
    {
        $language = Language::create([
            'name' => 'German',
            'code' => 'de',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->put("/admin/languages/{$language->id}", [
                'name' => 'German (Updated)',
                'code' => 'de',
                'direction' => 'ltr',
                'is_active' => false,
                'is_default' => false,
            ]);

        $response->assertStatus(302); // Redirect after update
        $this->assertDatabaseHas('languages', [
            'code' => 'de',
            'name' => 'German (Updated)',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_language()
    {
        $language = Language::create([
            'name' => 'Italian',
            'code' => 'it',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/languages/{$language->id}");

        $response->assertStatus(302); // Redirect after deletion
        $this->assertDatabaseMissing('languages', ['code' => 'it']);
    }

    public function test_only_one_language_can_be_default()
    {
        $language1 = Language::create([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
        ]);

        $language2 = Language::create([
            'name' => 'French',
            'code' => 'fr',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
        ]);

        // Update second language to be default
        $response = $this->actingAs($this->admin)
            ->put("/admin/languages/{$language2->id}", [
                'name' => 'French',
                'code' => 'fr',
                'direction' => 'ltr',
                'is_active' => true,
                'is_default' => true,
            ]);

        $response->assertStatus(302);

        // Verify first language is no longer default
        $language1->refresh();
        $language2->refresh();

        $this->assertFalse($language1->is_default);
        $this->assertTrue($language2->is_default);
    }
}
