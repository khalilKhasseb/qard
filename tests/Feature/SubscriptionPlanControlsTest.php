<?php

use App\Exceptions\SubscriptionLimitException;
use App\Models\BusinessCard;
use App\Models\Language;
use App\Models\SubscriptionPlan;
use App\Models\Theme;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\CardService;
use App\Services\ThemeService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Language::factory()->create(['code' => 'en', 'name' => 'English', 'is_active' => true, 'is_default' => true]);
});

describe('User capability methods', function () {
    it('admin can use all features', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        expect($admin->canUseNfc())->toBeTrue()
            ->and($admin->canUseAnalytics())->toBeTrue()
            ->and($admin->canUseCustomDomain())->toBeTrue()
            ->and($admin->canAccessPremiumTemplates())->toBeTrue()
            ->and($admin->canUseCustomCss())->toBeTrue();
    });

    it('user without subscription cannot use premium features', function () {
        $user = User::factory()->create();

        expect($user->canUseNfc())->toBeFalse()
            ->and($user->canUseAnalytics())->toBeFalse()
            ->and($user->canUseCustomDomain())->toBeFalse()
            ->and($user->canAccessPremiumTemplates())->toBeFalse()
            ->and($user->canUseCustomCss())->toBeFalse();
    });

    it('user with pro subscription can use enabled features', function () {
        $plan = SubscriptionPlan::factory()->create([
            'price' => 9.99,
            'cards_limit' => 5,
            'themes_limit' => 10,
            'custom_css_allowed' => true,
            'analytics_enabled' => true,
            'nfc_enabled' => true,
            'custom_domain_allowed' => true,
        ]);

        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        expect($user->canUseNfc())->toBeTrue()
            ->and($user->canUseAnalytics())->toBeTrue()
            ->and($user->canUseCustomDomain())->toBeTrue()
            ->and($user->canAccessPremiumTemplates())->toBeTrue()
            ->and($user->canUseCustomCss())->toBeTrue();
    });

    it('user with free subscription cannot access premium features', function () {
        $plan = SubscriptionPlan::factory()->free()->create();

        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        expect($user->canUseNfc())->toBeFalse()
            ->and($user->canUseAnalytics())->toBeFalse()
            ->and($user->canUseCustomDomain())->toBeFalse()
            ->and($user->canAccessPremiumTemplates())->toBeFalse()
            ->and($user->canUseCustomCss())->toBeFalse();
    });
});

describe('Card limit enforcement', function () {
    it('prevents card creation when limit reached', function () {
        $plan = SubscriptionPlan::factory()->create(['cards_limit' => 2]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        // Create cards up to the limit
        BusinessCard::factory()->count(2)->create(['user_id' => $user->id]);

        expect($user->canCreateCard())->toBeFalse();

        $cardService = app(CardService::class);

        expect(fn () => $cardService->createCard($user, [
            'title' => ['en' => 'Test Card'],
            'language_id' => Language::first()->id,
        ]))->toThrow(SubscriptionLimitException::class, 'Card limit reached for your plan');
    });

    it('allows card creation when under limit', function () {
        $plan = SubscriptionPlan::factory()->create(['cards_limit' => 3]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        // Create one card (limit is 3)
        BusinessCard::factory()->create(['user_id' => $user->id]);

        expect($user->canCreateCard())->toBeTrue();
    });
});

describe('Theme limit enforcement', function () {
    it('prevents theme creation when limit reached', function () {
        $plan = SubscriptionPlan::factory()->create(['themes_limit' => 2]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        // Create themes up to the limit
        Theme::factory()->count(2)->create(['user_id' => $user->id]);

        expect($user->canCreateTheme())->toBeFalse();

        $themeService = app(ThemeService::class);

        expect(fn () => $themeService->createTheme($user, [
            'name' => 'Test Theme',
            'config' => Theme::getDefaultConfig(),
        ]))->toThrow(SubscriptionLimitException::class, 'Theme limit reached for your plan');
    });

    it('prevents theme duplication when limit reached', function () {
        $plan = SubscriptionPlan::factory()->create(['themes_limit' => 1]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        // Create one theme (limit is 1)
        $theme = Theme::factory()->create(['user_id' => $user->id]);

        $themeService = app(ThemeService::class);

        expect(fn () => $themeService->duplicateTheme($theme, $user))
            ->toThrow(SubscriptionLimitException::class, 'Theme limit reached for your plan');
    });
});

describe('NFC feature enforcement', function () {
    it('prevents NFC assignment without subscription', function () {
        $user = User::factory()->create();
        $card = BusinessCard::factory()->create(['user_id' => $user->id]);

        $cardService = app(CardService::class);

        expect(fn () => $cardService->assignNfcIdentifier($card, 'nfc-123'))
            ->toThrow(SubscriptionLimitException::class, 'NFC feature requires a paid subscription');
    });

    it('allows NFC assignment with proper subscription', function () {
        $plan = SubscriptionPlan::factory()->create(['nfc_enabled' => true]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        $card = BusinessCard::factory()->create(['user_id' => $user->id]);
        $cardService = app(CardService::class);

        $cardService->assignNfcIdentifier($card, 'nfc-123');

        expect($card->fresh()->nfc_identifier)->toBe('nfc-123');
    });
});

describe('Analytics feature enforcement', function () {
    it('redirects to upgrade page when analytics not enabled', function () {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertInertia(fn ($page) => $page->component('Analytics/Upgrade')
            ->has('message'));
    });

    it('shows analytics when feature is enabled', function () {
        $plan = SubscriptionPlan::factory()->create(['analytics_enabled' => true]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        $response = $this->actingAs($user)->get(route('analytics.index'));

        $response->assertInertia(fn ($page) => $page->component('Analytics/Index'));
    });

    it('admin can always access analytics', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('analytics.index'));

        $response->assertInertia(fn ($page) => $page->component('Analytics/Index'));
    });
});

describe('Middleware subscription check', function () {
    it('uses correct method name for subscription check', function () {
        $user = User::factory()->create([
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->addMonth(),
        ]);

        expect($user->isSubscriptionActive())->toBeTrue();
    });

    it('returns false when subscription is expired', function () {
        $user = User::factory()->create([
            'subscription_status' => 'active',
            'subscription_expires_at' => now()->subDay(),
        ]);

        expect($user->isSubscriptionActive())->toBeFalse();
    });
});

describe('Theme controller passes usage props', function () {
    it('passes theme count and limit to create page', function () {
        $plan = SubscriptionPlan::factory()->create(['themes_limit' => 5]);
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
        ]);

        // Create 2 themes
        Theme::factory()->count(2)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('themes.create'));

        $response->assertInertia(fn ($page) => $page->component('Themes/Create')
            ->where('themeCount', 2)
            ->where('themeLimit', 5));
    });
});

describe('SubscriptionLimitException rendering', function () {
    it('returns JSON response for API requests', function () {
        $exception = new SubscriptionLimitException('Card limit reached', 'cards');

        $request = \Illuminate\Http\Request::create('/api/cards', 'POST');
        $request->headers->set('Accept', 'application/json');

        $response = $exception->render($request);

        expect($response)->toBeInstanceOf(\Illuminate\Http\JsonResponse::class)
            ->and($response->getStatusCode())->toBe(403)
            ->and($response->getData(true))->toMatchArray([
                'message' => 'Card limit reached',
                'feature' => 'cards',
            ]);
    });

    it('redirects for web requests', function () {
        $exception = new SubscriptionLimitException('Theme limit reached', 'themes');

        $request = \Illuminate\Http\Request::create('/themes', 'POST');

        $response = $exception->render($request);

        expect($response)->toBeInstanceOf(\Illuminate\Http\RedirectResponse::class)
            ->and($response->getTargetUrl())->toContain('addons');
    });
});
