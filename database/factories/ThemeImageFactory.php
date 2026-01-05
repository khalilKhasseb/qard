<?php

namespace Database\Factories;

use App\Models\Theme;
use App\Models\ThemeImage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeImageFactory extends Factory
{
    protected $model = ThemeImage::class;

    public function definition(): array
    {
        $fileType = fake()->randomElement(ThemeImage::FILE_TYPES);
        $width = fake()->randomElement([1920, 1280, 800, 400]);
        $height = fake()->randomElement([1080, 720, 600, 400]);
        
        return [
            'user_id' => User::factory(),
            'theme_id' => Theme::factory(),
            'file_path' => 'themes/' . fake()->uuid() . '.jpg',
            'file_type' => $fileType,
            'width' => $width,
            'height' => $height,
            'file_size' => fake()->numberBetween(50000, 5000000),
            'mime_type' => 'image/jpeg',
        ];
    }

    public function background(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'background',
            'width' => 1920,
            'height' => 1080,
        ]);
    }

    public function logo(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'logo',
            'width' => 400,
            'height' => 400,
            'mime_type' => 'image/png',
            'file_path' => 'themes/' . fake()->uuid() . '.png',
        ]);
    }

    public function header(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'header',
            'width' => 1280,
            'height' => 300,
        ]);
    }

    public function favicon(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'favicon',
            'width' => 32,
            'height' => 32,
            'mime_type' => 'image/png',
            'file_path' => 'themes/' . fake()->uuid() . '.png',
        ]);
    }
}
