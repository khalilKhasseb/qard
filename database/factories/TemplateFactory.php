<?php

namespace Database\Factories;

use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TemplateFactory extends Factory
{
    protected $model = Template::class;

    public function definition(): array
    {
        $name = fake()->words(2, true).' Template';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'preview_image' => null,
            'default_sections' => [
                ['type' => 'contact', 'title' => 'Contact Information', 'content' => []],
                ['type' => 'social', 'title' => 'Social Links', 'content' => ['links' => []]],
                ['type' => 'about', 'title' => 'About', 'content' => ['text' => '']],
            ],
            'default_theme_config' => null,
            'is_premium' => false,
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withDefaultTheme(): static
    {
        return $this->state(fn (array $attributes) => [
            'default_theme_config' => [
                'colors' => [
                    'primary' => fake()->hexColor(),
                    'secondary' => fake()->hexColor(),
                    'background' => '#ffffff',
                    'text' => '#1f2937',
                ],
            ],
        ]);
    }
}
