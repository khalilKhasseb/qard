<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@tapit.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'is_admin' => true,
                'subscription_tier' => 'business',
                'subscription_status' => 'active',
            ]
        );

        // Languages
        Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'is_default' => true]
        );
        Language::updateOrCreate(
            ['code' => 'ar'],
            ['name' => 'Arabic', 'direction' => 'rtl', 'is_active' => true, 'is_default' => false]
        );

        // Create subscription plans
        $this->createSubscriptionPlans();

        // Create default themes
        $this->createDefaultThemes();

        // Create default templates
        $this->createDefaultTemplates();

        // Language labels
        $this->call(LanguageLabelsSeeder::class);
        $this->call(PublicViewLabelsSeeder::class);

        // Demo public card with fully populated sections
        $this->call(DemoPublicCardSeeder::class);
    }

    protected function createSubscriptionPlans(): void
    {
        SubscriptionPlan::updateOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Get started with basic features',
            'price' => 0,
            'billing_cycle' => 'monthly',
            'cards_limit' => 1,
            'themes_limit' => 1,
            'custom_css_allowed' => false,
            'analytics_enabled' => false,
            'nfc_enabled' => false,
            'custom_domain_allowed' => false,
            'is_active' => true,
        ]);

        SubscriptionPlan::updateOrCreate(['slug' => 'pro'], [
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Perfect for professionals',
            'price' => 9.99,
            'billing_cycle' => 'monthly',
            'cards_limit' => 5,
            'themes_limit' => 10,
            'custom_css_allowed' => true,
            'analytics_enabled' => true,
            'nfc_enabled' => true,
            'custom_domain_allowed' => false,
            'is_active' => true,
        ]);

        SubscriptionPlan::updateOrCreate(['slug' => 'business'], [
            'name' => 'Business',
            'slug' => 'business',
            'description' => 'For teams and businesses',
            'price' => 29.99,
            'billing_cycle' => 'monthly',
            'cards_limit' => 20,
            'themes_limit' => 50,
            'custom_css_allowed' => true,
            'analytics_enabled' => true,
            'nfc_enabled' => true,
            'custom_domain_allowed' => true,
            'is_active' => true,
        ]);
    }

    protected function createDefaultThemes(): void
    {
        Theme::updateOrCreate(['name' => 'Classic Blue', 'is_system_default' => true], [
            'name' => 'Classic Blue',
            'is_system_default' => true,
            'is_public' => true,
            'config' => [
                'colors' => [
                    'primary' => '#2563eb',
                    'secondary' => '#1e40af',
                    'background' => '#ffffff',
                    'text' => '#1f2937',
                    'card_bg' => '#f9fafb',
                    'border' => '#e5e7eb',
                ],
                'fonts' => [
                    'heading' => 'Inter',
                    'body' => 'Inter',
                ],
                'images' => [],
                'layout' => [
                    'card_style' => 'elevated',
                    'border_radius' => '12px',
                    'alignment' => 'center',
                    'spacing' => 'normal',
                ],
                'custom_css' => '',
            ],
        ]);

        Theme::updateOrCreate(['name' => 'Dark Mode', 'is_system_default' => true], [
            'name' => 'Dark Mode',
            'is_system_default' => true,
            'is_public' => true,
            'config' => [
                'colors' => [
                    'primary' => '#3b82f6',
                    'secondary' => '#60a5fa',
                    'background' => '#111827',
                    'text' => '#f9fafb',
                    'card_bg' => '#1f2937',
                    'border' => '#374151',
                ],
                'fonts' => [
                    'heading' => 'Inter',
                    'body' => 'Inter',
                ],
                'images' => [],
                'layout' => [
                    'card_style' => 'elevated',
                    'border_radius' => '12px',
                    'alignment' => 'center',
                    'spacing' => 'normal',
                ],
                'custom_css' => '',
            ],
        ]);

        Theme::updateOrCreate(['name' => 'Minimal Green', 'is_system_default' => true], [
            'name' => 'Minimal Green',
            'is_system_default' => true,
            'is_public' => true,
            'config' => [
                'colors' => [
                    'primary' => '#059669',
                    'secondary' => '#047857',
                    'background' => '#f0fdf4',
                    'text' => '#166534',
                    'card_bg' => '#ffffff',
                    'border' => '#bbf7d0',
                ],
                'fonts' => [
                    'heading' => 'Playfair Display',
                    'body' => 'Inter',
                ],
                'images' => [],
                'layout' => [
                    'card_style' => 'outlined',
                    'border_radius' => '8px',
                    'alignment' => 'left',
                    'spacing' => 'relaxed',
                ],
                'custom_css' => '',
            ],
        ]);
    }

    protected function createDefaultTemplates(): void
    {
        Template::updateOrCreate(['slug' => 'professional'], [
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'Clean and professional layout for business cards',
            'default_sections' => [
                ['type' => 'contact', 'title' => 'Contact Information', 'content' => []],
                ['type' => 'social', 'title' => 'Connect With Me', 'content' => ['links' => []]],
                ['type' => 'about', 'title' => 'About Me', 'content' => ['text' => '']],
            ],
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Template::updateOrCreate(['slug' => 'business-services'], [
            'name' => 'Business Services',
            'slug' => 'business-services',
            'description' => 'Showcase your services and business hours',
            'default_sections' => [
                ['type' => 'contact', 'title' => 'Contact', 'content' => []],
                ['type' => 'services', 'title' => 'Our Services', 'content' => ['items' => []]],
                ['type' => 'hours', 'title' => 'Business Hours', 'content' => ['schedule' => []]],
                ['type' => 'social', 'title' => 'Follow Us', 'content' => ['links' => []]],
            ],
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Template::updateOrCreate(['slug' => 'portfolio'], [
            'name' => 'Portfolio',
            'slug' => 'portfolio',
            'description' => 'Perfect for creatives and freelancers',
            'default_sections' => [
                ['type' => 'about', 'title' => 'About', 'content' => ['text' => '']],
                ['type' => 'gallery', 'title' => 'My Work', 'content' => ['images' => []]],
                ['type' => 'testimonials', 'title' => 'Testimonials', 'content' => ['items' => []]],
                ['type' => 'contact', 'title' => 'Get In Touch', 'content' => []],
                ['type' => 'social', 'title' => 'Social Media', 'content' => ['links' => []]],
            ],
            'is_premium' => true,
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}
