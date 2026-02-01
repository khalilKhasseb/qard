<?php

use App\Exceptions\SubscriptionLimitException;
use App\Models\BusinessCard;
use App\Models\SubscriptionPlan;
use App\Models\Theme;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\CardService;
use App\Services\ThemeService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->freePlan = SubscriptionPlan::factory()->free()->create();
    $this->proPlan = SubscriptionPlan::factory()->pro()->create();
});

describe('User Capability Methods', function () {
    it('returns correct card limit for free plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        expect($user->getCardLimit())->toBe(1);
    });

    it('returns correct card limit for pro plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->getCardLimit())->toBe(5);
    });

    it('allows card creation when under limit', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->canCreateCard())->toBeTrue();
    });

    it('prevents card creation when limit reached', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        // Create one card (the limit for free)
        BusinessCard::factory()->create(['user_id' => $user->id]);

        expect($user->canCreateCard())->toBeFalse();
    });

    it('allows admin to bypass card limit', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        // Create many cards
        BusinessCard::factory()->count(10)->create(['user_id' => $admin->id]);

        expect($admin->canCreateCard())->toBeTrue();
    });

    it('returns false for NFC on free plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        expect($user->canUseNfc())->toBeFalse();
    });

    it('returns true for NFC on pro plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->canUseNfc())->toBeTrue();
    });

    it('returns false for analytics on free plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        expect($user->canUseAnalytics())->toBeFalse();
    });

    it('returns true for analytics on pro plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->canUseAnalytics())->toBeTrue();
    });

    it('returns false for custom domain on free plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        expect($user->canUseCustomDomain())->toBeFalse();
    });

    it('returns true for custom domain on pro plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->canUseCustomDomain())->toBeTrue();
    });

    it('returns false for premium templates on free plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        expect($user->canAccessPremiumTemplates())->toBeFalse();
    });

    it('returns true for premium templates on pro plan', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->canAccessPremiumTemplates())->toBeTrue();
    });
});

describe('CardService Limit Enforcement', function () {
    it('throws exception when card limit reached', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        // Create one card (the limit)
        BusinessCard::factory()->create(['user_id' => $user->id]);

        $cardService = app(CardService::class);

        expect(fn () => $cardService->createCard($user, [
            'title' => 'Test Card',
            'language_id' => 1,
        ]))->toThrow(SubscriptionLimitException::class, 'Card limit reached');
    });

    it('throws exception for NFC when not allowed', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        $card = BusinessCard::factory()->create(['user_id' => $user->id]);
        $cardService = app(CardService::class);

        expect(fn () => $cardService->assignNfcIdentifier($card, 'nfc-123'))
            ->toThrow(SubscriptionLimitException::class, 'NFC feature requires a paid subscription');
    });

    it('allows NFC when subscription permits', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        $card = BusinessCard::factory()->create(['user_id' => $user->id]);
        $cardService = app(CardService::class);

        $cardService->assignNfcIdentifier($card, 'nfc-123');

        expect($card->fresh()->nfc_identifier)->toBe('nfc-123');
    });

    it('throws exception for custom domain when not allowed', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        $cardService = app(CardService::class);

        expect(fn () => $cardService->createCard($user, [
            'title' => 'Test Card',
            'language_id' => 1,
            'custom_domain' => 'mysite.com',
        ]))->toThrow(SubscriptionLimitException::class, 'Custom domains require a paid subscription');
    });
});

describe('ThemeService Limit Enforcement', function () {
    it('throws exception when theme limit reached', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        // Create one theme (the limit)
        Theme::factory()->create(['user_id' => $user->id]);

        $themeService = app(ThemeService::class);

        expect(fn () => $themeService->createTheme($user, [
            'name' => 'New Theme',
            'config' => [],
        ]))->toThrow(SubscriptionLimitException::class, 'Theme limit reached');
    });

    it('throws exception when duplicating theme at limit', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $themeService = app(ThemeService::class);

        expect(fn () => $themeService->duplicateTheme($theme, $user))
            ->toThrow(SubscriptionLimitException::class, 'Theme limit reached');
    });

    it('allows theme duplication when under limit', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        $theme = Theme::factory()->create(['user_id' => $user->id]);
        $themeService = app(ThemeService::class);

        $newTheme = $themeService->duplicateTheme($theme, $user);

        expect($newTheme)->toBeInstanceOf(Theme::class)
            ->and($newTheme->name)->toContain('(Copy)');
    });
});

describe('API Usage Endpoint', function () {
    it('returns comprehensive usage data', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        BusinessCard::factory()->count(2)->create(['user_id' => $user->id]);
        Theme::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/usage');

        $response->assertOk()
            ->assertJsonStructure([
                'cards' => ['used', 'limit', 'can_create'],
                'themes' => ['used', 'limit', 'can_create'],
                'features' => ['custom_css', 'nfc', 'analytics', 'custom_domain', 'premium_templates'],
                'subscription' => ['plan_name', 'status', 'expires_at'],
            ])
            ->assertJsonPath('cards.used', 2)
            ->assertJsonPath('cards.limit', 5)
            ->assertJsonPath('themes.used', 1)
            ->assertJsonPath('features.nfc', true)
            ->assertJsonPath('features.analytics', true)
            ->assertJsonPath('subscription.plan_name', 'Pro');
    });

    it('returns correct data for free plan user', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/usage');

        $response->assertOk()
            ->assertJsonPath('cards.limit', 1)
            ->assertJsonPath('themes.limit', 1)
            ->assertJsonPath('features.nfc', false)
            ->assertJsonPath('features.analytics', false)
            ->assertJsonPath('features.custom_domain', false)
            ->assertJsonPath('subscription.plan_name', 'Free');
    });
});

describe('Analytics Access Control', function () {
    it('redirects to upgrade page when analytics not available', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->freePlan->id,
        ]);

        $response = $this->actingAs($user)->get('/analytics');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Analytics/Upgrade'));
    });

    it('shows analytics dashboard when available', function () {
        $user = User::factory()->create();
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        $response = $this->actingAs($user)->get('/analytics');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Analytics/Index'));
    });
});

describe('Subscription Middleware Enforcement', function () {
    it('redirects unsubscribed user from dashboard to subscription page', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('subscription.index'));
    });

    it('allows subscribed user to access dashboard', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    });

    it('allows admin to access dashboard without subscription', function () {
        $admin = User::factory()->create([
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
    });

    it('allows unsubscribed user to access subscription page', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/subscription');

        $response->assertOk();
    });

    it('allows unsubscribed user to access payments page', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/payments');

        $response->assertOk();
    });

    it('redirects unsubscribed user from cards to subscription page', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/cards');

        $response->assertRedirect(route('subscription.index'));
    });

    it('redirects unsubscribed user from themes to subscription page', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/themes');

        $response->assertRedirect(route('subscription.index'));
    });
});

describe('Registration Plan Selection', function () {
    it('requires plan selection during registration', function () {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('plan_id');
    });

    it('saves pending plan id during registration', function () {
        $plan = $this->proPlan;

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'phone' => '+1234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'plan_id' => $plan->id,
        ]);

        $user = User::where('email', 'newuser@example.com')->first();
        expect($user)->not->toBeNull()
            ->and($user->pending_plan_id)->toBe($plan->id);
    });

    it('provides plans to registration page', function () {
        $response = $this->get('/register');

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Auth/Register')
                ->has('plans')
            );
    });
});

describe('Post Verification Redirect', function () {
    it('redirects to checkout when user has pending plan and no subscription', function () {
        $user = User::factory()->create([
            'pending_plan_id' => $this->proPlan->id,
            'email_verified_at' => now(),
        ]);

        expect($user->getPostVerificationRedirect())
            ->toBe(route('payments.checkout', $this->proPlan->id));
    });

    it('redirects to subscription page when user has no pending plan and no subscription', function () {
        $user = User::factory()->create([
            'pending_plan_id' => null,
            'email_verified_at' => now(),
        ]);

        expect($user->getPostVerificationRedirect())
            ->toBe(route('subscription.index'));
    });

    it('redirects to dashboard when user has active subscription', function () {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        UserSubscription::factory()->active()->create([
            'user_id' => $user->id,
            'subscription_plan_id' => $this->proPlan->id,
        ]);

        expect($user->getPostVerificationRedirect())
            ->toBe(route('dashboard'));
    });
});

describe('No Free Tier Fallbacks', function () {
    it('returns zero card limit when user has no subscription', function () {
        $user = User::factory()->create();

        expect($user->getCardLimit())->toBe(0);
    });

    it('returns zero theme limit when user has no subscription', function () {
        $user = User::factory()->create();

        expect($user->getThemeLimit())->toBe(0);
    });

    it('returns zero translation credits when user has no subscription', function () {
        $user = User::factory()->create();

        expect($user->getTranslationCreditLimit())->toBe(0);
    });

    it('returns false for translation feature when user has no subscription', function () {
        $user = User::factory()->create();

        expect($user->hasTranslationFeature())->toBeFalse();
    });

    it('admin gets unlimited card limit even without subscription', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        expect($admin->getCardLimit())->toBe(PHP_INT_MAX);
    });

    it('admin gets unlimited theme limit even without subscription', function () {
        $admin = User::factory()->create(['is_admin' => true]);

        expect($admin->getThemeLimit())->toBe(PHP_INT_MAX);
    });
});
