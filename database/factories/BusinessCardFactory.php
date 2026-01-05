<?php

namespace Database\Factories;

use App\Models\BusinessCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BusinessCardFactory extends Factory
{
    protected $model = BusinessCard::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->name(),
            'subtitle' => fake()->jobTitle(),
            'template_id' => null,
            'theme_id' => null,
            'theme_overrides' => null,
            'custom_slug' => null,
            'share_url' => Str::random(10),
            'qr_code_url' => null,
            'nfc_identifier' => null,
            'is_published' => false,
            'is_primary' => false,
            'views_count' => 0,
            'shares_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_slug' => $slug,
        ]);
    }
}
