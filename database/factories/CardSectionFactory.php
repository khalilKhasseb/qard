<?php

namespace Database\Factories;

use App\Models\BusinessCard;
use App\Models\CardSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardSectionFactory extends Factory
{
    protected $model = CardSection::class;

    public function definition(): array
    {
        return [
            'business_card_id' => BusinessCard::factory(),
            'section_type' => fake()->randomElement(['contact', 'social', 'services', 'about']),
            'title' => fake()->words(2, true),
            'content' => [],
            'sort_order' => 0,
            'is_active' => true,
            'metadata' => null,
        ];
    }

    public function contact(): static
    {
        return $this->state(fn (array $attributes) => [
            'section_type' => 'contact',
            'title' => 'Contact',
            'content' => [
                'email' => fake()->email(),
                'phone' => fake()->phoneNumber(),
                'website' => fake()->url(),
            ],
        ]);
    }

    public function social(): static
    {
        return $this->state(fn (array $attributes) => [
            'section_type' => 'social',
            'title' => 'Social Media',
            'content' => [
                'links' => [
                    ['platform' => 'linkedin', 'url' => 'https://linkedin.com/in/'.fake()->userName()],
                    ['platform' => 'twitter', 'url' => 'https://twitter.com/'.fake()->userName()],
                ],
            ],
        ]);
    }
}
