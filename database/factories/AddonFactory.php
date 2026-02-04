<?php

namespace Database\Factories;

use App\Models\Addon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AddonFactory extends Factory
{
    protected $model = Addon::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'type' => 'extra_cards',
            'feature_key' => null,
            'value' => 3,
            'price' => fake()->randomFloat(2, 1, 50),
            'currency' => 'USD',
            'is_active' => true,
            'sort_order' => 0,
            'metadata' => null,
        ];
    }

    public function extraCards(int $value = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'extra_cards',
            'feature_key' => null,
            'value' => $value,
            'name' => "+{$value} Extra Card Slots",
            'slug' => "extra-cards-{$value}",
        ]);
    }

    public function featureUnlock(string $key): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'feature_unlock',
            'feature_key' => $key,
            'value' => 1,
            'name' => ucfirst(str_replace('_', ' ', $key)).' Unlock',
            'slug' => "{$key}-unlock",
        ]);
    }

    public function nfcUnlock(): static
    {
        return $this->featureUnlock('nfc')->state(fn (array $attributes) => [
            'name' => 'NFC Support',
            'slug' => 'nfc-unlock',
            'price' => 4.99,
        ]);
    }

    public function analyticsUnlock(): static
    {
        return $this->featureUnlock('analytics')->state(fn (array $attributes) => [
            'name' => 'Advanced Analytics',
            'slug' => 'analytics-unlock',
            'price' => 3.99,
        ]);
    }

    public function customDomainUnlock(): static
    {
        return $this->featureUnlock('custom_domain')->state(fn (array $attributes) => [
            'name' => 'Custom Domain',
            'slug' => 'custom-domain-unlock',
            'price' => 9.99,
        ]);
    }

    public function customCssUnlock(): static
    {
        return $this->featureUnlock('custom_css')->state(fn (array $attributes) => [
            'name' => 'Custom CSS',
            'slug' => 'custom-css-unlock',
            'price' => 2.99,
        ]);
    }
}
