<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true) . ' Theme',
            'is_system_default' => false,
            'is_public' => false,
            'config' => Theme::getDefaultConfig(),
            'preview_image' => null,
            'used_by_cards_count' => 0,
        ];
    }

    public function systemDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_system_default' => true,
            'is_public' => true,
        ]);
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }
}
