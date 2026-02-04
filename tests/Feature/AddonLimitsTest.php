<?php

use App\Models\Addon;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddon;
use App\Models\UserSubscription;

beforeEach(function () {
    $this->plan = SubscriptionPlan::factory()->create([
        'cards_limit' => 2,
        'nfc_enabled' => false,
        'analytics_enabled' => false,
        'custom_domain_allowed' => false,
        'custom_css_allowed' => false,
        'is_active' => true,
    ]);

    $this->user = User::factory()->create([
        'email_verified_at' => now(),
        'subscription_status' => 'active',
    ]);

    $this->subscription = UserSubscription::factory()->active()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
    ]);
});

test('card limit equals plan limit plus extra card add-ons', function () {
    expect($this->user->getCardLimit())->toBe(2);

    $addon = Addon::factory()->extraCards(3)->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($this->user->getCardLimit())->toBe(5);
});

test('multiple extra card add-ons are cumulative', function () {
    $addon1 = Addon::factory()->extraCards(3)->create();
    $addon2 = Addon::factory()->extraCards(5)->create(['slug' => 'extra-cards-5-b']);

    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon1->id,
    ]);

    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon2->id,
    ]);

    expect($this->user->getCardLimit())->toBe(10); // 2 + 3 + 5
});

test('extra card slots are not counted when subscription is inactive', function () {
    $addon = Addon::factory()->extraCards(3)->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    // Expire the subscription
    $this->subscription->update([
        'status' => 'expired',
        'ends_at' => now()->subDay(),
    ]);

    // Clear the cached relationship
    $this->user->unsetRelation('activeSubscription');

    expect($this->user->getCardLimit())->toBe(0);
});

test('feature unlocks work via add-on when plan does not include feature', function () {
    expect($this->user->canUseNfc())->toBeFalse();

    $addon = Addon::factory()->nfcUnlock()->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($this->user->canUseNfc())->toBeTrue();
});

test('feature unlock via add-on for analytics', function () {
    expect($this->user->canUseAnalytics())->toBeFalse();

    $addon = Addon::factory()->analyticsUnlock()->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($this->user->canUseAnalytics())->toBeTrue();
});

test('feature unlock via add-on for custom domain', function () {
    expect($this->user->canUseCustomDomain())->toBeFalse();

    $addon = Addon::factory()->customDomainUnlock()->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($this->user->canUseCustomDomain())->toBeTrue();
});

test('feature unlock via add-on for custom css', function () {
    expect($this->user->canUseCustomCss())->toBeFalse();

    $addon = Addon::factory()->customCssUnlock()->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($this->user->canUseCustomCss())->toBeTrue();
});

test('features not accessible when subscription expired even with add-on', function () {
    $addon = Addon::factory()->nfcUnlock()->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($this->user->canUseNfc())->toBeTrue();

    // Expire subscription
    $this->subscription->update([
        'status' => 'expired',
        'ends_at' => now()->subDay(),
    ]);
    $this->user->unsetRelation('activeSubscription');

    expect($this->user->canUseNfc())->toBeFalse();
});

test('add-ons are restored on resubscription', function () {
    $addon = Addon::factory()->extraCards(3)->create();
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    // Expire subscription
    $this->subscription->update([
        'status' => 'expired',
        'ends_at' => now()->subDay(),
    ]);
    $this->user->unsetRelation('activeSubscription');

    expect($this->user->getCardLimit())->toBe(0);

    // Create new active subscription
    UserSubscription::factory()->active()->create([
        'user_id' => $this->user->id,
        'subscription_plan_id' => $this->plan->id,
    ]);
    $this->user->unsetRelation('activeSubscription');

    expect($this->user->getCardLimit())->toBe(5); // 2 + 3
});

test('admin bypasses everything regardless of add-ons', function () {
    $admin = User::factory()->create([
        'is_admin' => true,
        'email_verified_at' => now(),
    ]);

    expect($admin->getCardLimit())->toBe(PHP_INT_MAX);
    expect($admin->canUseNfc())->toBeTrue();
    expect($admin->canUseAnalytics())->toBeTrue();
    expect($admin->canUseCustomDomain())->toBeTrue();
    expect($admin->canUseCustomCss())->toBeTrue();
});

test('plan features still work without add-ons', function () {
    $proPlan = SubscriptionPlan::factory()->create([
        'cards_limit' => 10,
        'nfc_enabled' => true,
        'analytics_enabled' => true,
        'custom_domain_allowed' => true,
        'custom_css_allowed' => true,
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
        'subscription_status' => 'active',
    ]);

    UserSubscription::factory()->active()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $proPlan->id,
    ]);

    expect($user->getCardLimit())->toBe(10);
    expect($user->canUseNfc())->toBeTrue();
    expect($user->canUseAnalytics())->toBeTrue();
    expect($user->canUseCustomDomain())->toBeTrue();
    expect($user->canUseCustomCss())->toBeTrue();
});
