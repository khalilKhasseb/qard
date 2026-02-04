<?php

use App\Models\Addon;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddon;
use App\Models\UserSubscription;

beforeEach(function () {
    $this->plan = SubscriptionPlan::factory()->create([
        'cards_limit' => 2,
        'is_active' => true,
    ]);

    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
        'subscription_status' => 'active',
    ]);

    $this->subscription = UserSubscription::factory()->active()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
    ]);
});

test('addons index page requires authentication', function () {
    $this->get(route('addons.index'))
        ->assertRedirect(route('login'));
});

test('addons index page renders for authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('addons.index'))
        ->assertSuccessful();
});

test('addons index shows available add-ons', function () {
    Addon::factory()->extraCards(3)->create();
    Addon::factory()->nfcUnlock()->create();

    $this->actingAs($this->user)
        ->get(route('addons.index'))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Addons/Index')
            ->has('extraCards', 1)
            ->has('featureUnlocks', 1)
            ->where('hasActiveSubscription', true)
        );
});

test('addons checkout page requires authentication', function () {
    $addon = Addon::factory()->extraCards(3)->create();

    $this->get(route('addons.checkout', $addon))
        ->assertRedirect(route('login'));
});

test('addons checkout page renders for authenticated user', function () {
    $addon = Addon::factory()->extraCards(3)->create();

    $this->actingAs($this->user)
        ->get(route('addons.checkout', $addon))
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('Addons/Checkout')
            ->has('addon')
        );
});

test('prevents duplicate feature unlock purchase', function () {
    $addon = Addon::factory()->nfcUnlock()->create();

    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    $this->actingAs($this->user)
        ->postJson(route('addons.initialize', $addon))
        ->assertStatus(422)
        ->assertJson(['message' => __('addons.already_owned')]);
});

test('callback without reference returns error', function () {
    $this->actingAs($this->user)
        ->get(route('addons.callback'))
        ->assertStatus(400);
});
