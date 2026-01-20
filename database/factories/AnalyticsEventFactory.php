<?php

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use App\Models\BusinessCard;
use App\Models\CardSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    public function definition(): array
    {
        $eventTypes = array_keys(AnalyticsEvent::EVENT_TYPES);

        return [
            'business_card_id' => BusinessCard::factory(),
            'card_section_id' => null,
            'event_type' => fake()->randomElement($eventTypes),
            'referrer' => fake()->optional()->url(),
            'user_agent' => fake()->userAgent(),
            'ip_address' => fake()->ipv4(),
            'country' => fake()->optional()->country(),
            'city' => fake()->optional()->city(),
            'device_type' => fake()->randomElement(['mobile', 'desktop', 'tablet']),
            'browser' => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'os' => fake()->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            'metadata' => null,
        ];
    }

    public function view(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'view',
        ]);
    }

    public function nfcTap(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'nfc_tap',
        ]);
    }

    public function qrScan(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'qr_scan',
        ]);
    }

    public function withSection(): static
    {
        return $this->state(fn (array $attributes) => [
            'card_section_id' => CardSection::factory(),
            'event_type' => 'section_click',
        ]);
    }

    public function withMetadata(array $metadata): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => $metadata,
        ]);
    }
}
