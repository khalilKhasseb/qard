<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 0, 99.99),
            'billing_cycle' => fake()->randomElement(['monthly', 'yearly', 'lifetime']),
            'cards_limit' => fake()->numberBetween(1, 20),
            'themes_limit' => fake()->numberBetween(1, 50),
            'custom_css_allowed' => fake()->boolean(),
            'analytics_enabled' => fake()->boolean(),
            'nfc_enabled' => fake()->boolean(),
            'custom_domain_allowed' => fake()->boolean(),
            'features' => null,
            'is_active' => true,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Free',
            'slug' => 'free',
            'price' => 0,
            'cards_limit' => 1,
            'themes_limit' => 1,
            'custom_css_allowed' => false,
            'analytics_enabled' => false,
            'nfc_enabled' => false,
            'custom_domain_allowed' => false,
        ]);
    }

    public function pro(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pro',
            'slug' => 'pro',
            'price' => 9.99,
            'cards_limit' => 5,
            'themes_limit' => 10,
            'custom_css_allowed' => true,
            'analytics_enabled' => true,
            'nfc_enabled' => true,
            'custom_domain_allowed' => true,
        ]);
    }
}
