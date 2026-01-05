<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subscription_plan_id' => null,
            'amount' => fake()->randomFloat(2, 5, 100),
            'currency' => 'USD',
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'gateway']),
            'status' => 'pending',
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
            'gateway_reference' => null,
            'notes' => null,
            'metadata' => null,
            'paid_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    public function forPlan(SubscriptionPlan $plan): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_plan_id' => $plan->id,
            'amount' => $plan->price,
        ]);
    }
}
