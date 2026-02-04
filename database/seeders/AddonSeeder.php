<?php

namespace Database\Seeders;

use App\Models\Addon;
use Illuminate\Database\Seeder;

class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $addons = [
            [
                'name' => '+3 Extra Card Slots',
                'slug' => 'extra-cards-3',
                'description' => 'Add 3 more business card slots to your account.',
                'type' => 'extra_cards',
                'feature_key' => null,
                'value' => 3,
                'price' => 4.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => '+5 Extra Card Slots',
                'slug' => 'extra-cards-5',
                'description' => 'Add 5 more business card slots to your account.',
                'type' => 'extra_cards',
                'feature_key' => null,
                'value' => 5,
                'price' => 7.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => '+10 Extra Card Slots',
                'slug' => 'extra-cards-10',
                'description' => 'Add 10 more business card slots to your account.',
                'type' => 'extra_cards',
                'feature_key' => null,
                'value' => 10,
                'price' => 12.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'NFC Support',
                'slug' => 'nfc-unlock',
                'description' => 'Enable NFC contactless sharing for your business cards.',
                'type' => 'feature_unlock',
                'feature_key' => 'nfc',
                'value' => 1,
                'price' => 4.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Advanced Analytics',
                'slug' => 'analytics-unlock',
                'description' => 'Unlock detailed analytics and insights for your cards.',
                'type' => 'feature_unlock',
                'feature_key' => 'analytics',
                'value' => 1,
                'price' => 3.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 11,
            ],
            [
                'name' => 'Custom Domain',
                'slug' => 'custom-domain-unlock',
                'description' => 'Use your own domain for your business cards.',
                'type' => 'feature_unlock',
                'feature_key' => 'custom_domain',
                'value' => 1,
                'price' => 9.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 12,
            ],
            [
                'name' => 'Custom CSS',
                'slug' => 'custom-css-unlock',
                'description' => 'Add custom CSS styling to your business cards.',
                'type' => 'feature_unlock',
                'feature_key' => 'custom_css',
                'value' => 1,
                'price' => 2.99,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 13,
            ],
        ];

        foreach ($addons as $addon) {
            Addon::updateOrCreate(
                ['slug' => $addon['slug']],
                $addon
            );
        }
    }
}
