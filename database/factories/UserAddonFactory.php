<?php

namespace Database\Factories;

use App\Models\Addon;
use App\Models\User;
use App\Models\UserAddon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAddonFactory extends Factory
{
    protected $model = UserAddon::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'addon_id' => Addon::factory(),
            'payment_id' => null,
            'granted_by' => 'purchase',
            'notes' => null,
        ];
    }

    public function adminGrant(): static
    {
        return $this->state(fn (array $attributes) => [
            'granted_by' => 'admin_grant',
            'notes' => 'Granted by administrator',
        ]);
    }

    public function promo(): static
    {
        return $this->state(fn (array $attributes) => [
            'granted_by' => 'promo',
            'notes' => 'Promotional grant',
        ]);
    }
}
