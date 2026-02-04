<?php

use App\Models\Addon;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddon;
use App\Models\UserSubscription;
use App\Services\AddonService;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    $this->plan = SubscriptionPlan::factory()->create([
        'cards_limit' => 2,
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

test('confirming payment creates user addon record', function () {
    Notification::fake();

    $addon = Addon::factory()->extraCards(3)->create();
    $payment = Payment::factory()->completed()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
        'amount' => $addon->price,
    ]);

    $service = app(AddonService::class);
    $userAddon = $service->confirmPaymentAndGrantAddon($payment);

    expect($userAddon)->toBeInstanceOf(UserAddon::class);
    expect($userAddon->user_id)->toBe($this->user->id);
    expect($userAddon->addon_id)->toBe($addon->id);
    expect($userAddon->payment_id)->toBe($payment->id);
    expect($userAddon->granted_by)->toBe('purchase');
});

test('allows multiple extra card purchases', function () {
    Notification::fake();

    $addon = Addon::factory()->extraCards(3)->create();

    // First purchase
    $payment1 = Payment::factory()->completed()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);
    $service = app(AddonService::class);
    $service->confirmPaymentAndGrantAddon($payment1);

    // Second purchase
    $payment2 = Payment::factory()->completed()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);
    $service->confirmPaymentAndGrantAddon($payment2);

    expect($this->user->userAddons()->count())->toBe(2);
    expect($this->user->getExtraCardSlots())->toBe(6);
});

test('admin can grant add-on without payment', function () {
    $addon = Addon::factory()->nfcUnlock()->create();

    $service = app(AddonService::class);
    $userAddon = $service->grantAddon($this->user, $addon, 'Test grant');

    expect($userAddon->granted_by)->toBe('admin_grant');
    expect($userAddon->payment_id)->toBeNull();
    expect($userAddon->notes)->toBe('Test grant');
    expect($this->user->canUseNfc())->toBeTrue();
});

test('get available addons marks owned feature unlocks', function () {
    $extraCards = Addon::factory()->extraCards(3)->create();
    $nfcAddon = Addon::factory()->nfcUnlock()->create();

    // Purchase NFC unlock
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $nfcAddon->id,
    ]);

    $service = app(AddonService::class);
    $available = $service->getAvailableAddons($this->user);

    expect($available['extra_cards'])->toHaveCount(1);
    expect($available['feature_unlocks'])->toHaveCount(1);
    expect($available['feature_unlocks'][0]->is_owned)->toBeTrue();
});

test('addon model scopes work correctly', function () {
    Addon::factory()->extraCards(3)->create(['is_active' => true]);
    Addon::factory()->nfcUnlock()->create(['is_active' => true]);
    Addon::factory()->analyticsUnlock()->create(['is_active' => false]);

    expect(Addon::active()->count())->toBe(2);
    expect(Addon::extraCards()->count())->toBe(1);
    expect(Addon::featureUnlocks()->count())->toBe(2);
});

test('user addon relationships work correctly', function () {
    $addon = Addon::factory()->extraCards(3)->create();
    $payment = Payment::factory()->completed()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    $userAddon = UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
        'payment_id' => $payment->id,
    ]);

    expect($userAddon->user->id)->toBe($this->user->id);
    expect($userAddon->addon->id)->toBe($addon->id);
    expect($userAddon->payment->id)->toBe($payment->id);
});

test('payment addon relationship works', function () {
    $addon = Addon::factory()->extraCards(3)->create();
    $payment = Payment::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon->id,
    ]);

    expect($payment->addon->id)->toBe($addon->id);
});

test('user addons relationship returns all addons', function () {
    $addon1 = Addon::factory()->extraCards(3)->create();
    $addon2 = Addon::factory()->nfcUnlock()->create();

    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon1->id,
    ]);
    UserAddon::factory()->create([
        'user_id' => $this->user->id,
        'addon_id' => $addon2->id,
    ]);

    expect($this->user->userAddons()->count())->toBe(2);
    expect($this->user->addons()->count())->toBe(2);
});
