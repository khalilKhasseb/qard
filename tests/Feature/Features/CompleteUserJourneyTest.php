<?php

use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\SubscriptionPlan;
use App\Models\Theme;
use App\Models\User;
use App\Models\UserSubscription;
use App\Services\CardService;

test('journey: new user can register and create first card', function () {
    // Register
    $response = $this->post(route('register'), [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'john@example.com')->first();
    expect($user)->not->toBeNull();

    // Create card using service (which sets is_primary)
    $service = app(CardService::class);
    $card = $service->createCard($user, ['title' => ['en' => 'My Card']]);
    expect($card->is_primary)->toBeTrue();
});

test('journey: user can create card with sections and theme', function () {
    $user = User::factory()->create(['subscription_tier' => 'pro']);
    $theme = Theme::factory()->create(['user_id' => $user->id]);

    // Create card
    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'theme_id' => $theme->id,
    ]);

    // Add sections
    $section1 = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'section_type' => 'contact',
    ]);
    $section2 = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'section_type' => 'social',
    ]);

    expect($card->sections)->toHaveCount(2);
    expect($card->theme_id)->toBe($theme->id);
});

test('journey: user can publish card and view analytics', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'is_published' => false,
    ]);

    // Publish card
    $card->update(['is_published' => true]);
    expect($card->is_published)->toBeTrue();

    // Track views
    $card->increment('views_count');
    $card->increment('views_count');

    expect($card->fresh()->views_count)->toBe(2);
});

test('journey: user can upgrade subscription', function () {
    $user = User::factory()->create([
        'subscription_tier' => 'free',
        'subscription_status' => 'pending',
    ]);

    // Upgrade
    $user->update([
        'subscription_tier' => 'pro',
        'subscription_status' => 'active',
    ]);

    expect($user->subscription_tier)->toBe('pro');
    expect($user->subscription_status)->toBe('active');
});

test('journey: user can share card via QR code', function () {
    if (! class_exists('SimpleSoftwareIO\QrCode\Facades\QrCode')) {
        $this->markTestSkipped('QrCode package not installed');
    }

    $user = User::factory()->create();
    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'is_published' => true,
    ]);

    $service = app(\App\Services\CardService::class);
    $qrUrl = $service->generateQrCode($card);

    expect($qrUrl)->toBeString();
    expect($card->fresh()->qr_code_url)->not->toBeNull();
});

test('journey: user can duplicate existing card', function () {
    $user = User::factory()->create();

    // Create a pro subscription
    $proPlan = SubscriptionPlan::factory()->pro()->create();
    UserSubscription::factory()->active()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $proPlan->id,
    ]);

    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'title' => ['en' => 'Original Card'],
    ]);

    $service = app(\App\Services\CardService::class);
    $newCard = $service->duplicateCard($card);

    expect($newCard->id)->not->toBe($card->id);
    expect($newCard->title['en'])->toContain('Copy');
});

test('journey: user can create and apply custom theme', function () {
    $user = User::factory()->create(['subscription_tier' => 'pro']);
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $theme = Theme::factory()->create(['user_id' => $user->id]);

    $card->update(['theme_id' => $theme->id]);

    expect($card->fresh()->theme_id)->toBe($theme->id);
});

test('journey: free user hits card limit', function () {
    $user = User::factory()->create(['subscription_tier' => 'free']);

    // Create first card (allowed)
    BusinessCard::factory()->create(['user_id' => $user->id]);

    expect($user->canCreateCard())->toBeFalse();
});

test('journey: pro user has higher limits', function () {
    $user = User::factory()->create();

    // Create a pro subscription (allows 5 cards)
    $proPlan = SubscriptionPlan::factory()->pro()->create();
    UserSubscription::factory()->active()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $proPlan->id,
    ]);

    // Pro plan allows 5 cards, so user can still create more after 4
    BusinessCard::factory()->count(4)->create(['user_id' => $user->id]);

    expect($user->canCreateCard())->toBeTrue();
});

test('journey: card can be accessed via custom slug', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'custom_slug' => 'johndoe',
        'is_published' => true,
    ]);

    $service = app(\App\Services\CardService::class);
    $found = $service->getCardBySlug('johndoe');

    expect($found->id)->toBe($card->id);
});

test('journey: card can be accessed via NFC', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create([
        'user_id' => $user->id,
        'nfc_identifier' => 'NFC123',
        'is_published' => true,
    ]);

    $service = app(\App\Services\CardService::class);
    $found = $service->getCardByNfc('NFC123');

    expect($found->id)->toBe($card->id);
});

test('journey: analytics events are tracked', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $service = app(\App\Services\CardService::class);
    $service->trackView($card);
    $service->trackShare($card, 'twitter');

    expect($card->fresh()->views_count)->toBeGreaterThan(0);
    expect($card->fresh()->shares_count)->toBeGreaterThan(0);
});

test('journey: user can manage multiple cards', function () {
    $user = User::factory()->create(['subscription_tier' => 'business']);

    $card1 = BusinessCard::factory()->create(['user_id' => $user->id]);
    $card2 = BusinessCard::factory()->create(['user_id' => $user->id]);
    $card3 = BusinessCard::factory()->create(['user_id' => $user->id]);

    expect($user->cards)->toHaveCount(3);
});

test('journey: primary card is automatically set', function () {
    $user = User::factory()->create();

    // Create a pro subscription
    $proPlan = SubscriptionPlan::factory()->pro()->create();
    UserSubscription::factory()->active()->create([
        'user_id' => $user->id,
        'subscription_plan_id' => $proPlan->id,
    ]);

    // Use CardService to properly set is_primary
    $service = app(CardService::class);
    $card1 = $service->createCard($user, ['title' => ['en' => 'Card 1']]);
    $card2 = $service->createCard($user, ['title' => ['en' => 'Card 2']]);

    expect($card1->is_primary)->toBeTrue();
    expect($card2->is_primary)->toBeFalse();
});

test('journey: user can search their cards', function () {
    $user = User::factory()->create();

    BusinessCard::factory()->create([
        'user_id' => $user->id,
        'title' => 'John Doe - Developer',
    ]);
    BusinessCard::factory()->create([
        'user_id' => $user->id,
        'title' => 'Jane Smith - Designer',
    ]);

    $results = BusinessCard::where('user_id', $user->id)
        ->where('title', 'like', '%Developer%')
        ->get();

    expect($results)->toHaveCount(1);
});

test('journey: sections can be reordered', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $section1 = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'sort_order' => 0,
    ]);
    $section2 = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'sort_order' => 1,
    ]);

    $service = app(\App\Services\CardService::class);
    $service->reorderSections($card, [$section2->id, $section1->id]);

    expect($section2->fresh()->sort_order)->toBe(0);
    expect($section1->fresh()->sort_order)->toBe(1);
});

test('journey: inactive sections are hidden', function () {
    $user = User::factory()->create();
    $card = BusinessCard::factory()->create(['user_id' => $user->id]);

    $activeSection = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'is_active' => true,
    ]);
    $inactiveSection = CardSection::factory()->create([
        'business_card_id' => $card->id,
        'is_active' => false,
    ]);

    $activeSections = $card->sections()->where('is_active', true)->get();

    expect($activeSections)->toHaveCount(1);
});

test('journey: theme can be shared publicly', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $theme = Theme::factory()->create([
        'user_id' => $user1->id,
        'is_public' => true,
    ]);

    $card = BusinessCard::factory()->create(['user_id' => $user2->id]);
    $card->update(['theme_id' => $theme->id]);

    expect($card->theme_id)->toBe($theme->id);
});

test('journey: user profile can be updated', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $user->update(['name' => 'New Name']);

    expect($user->fresh()->name)->toBe('New Name');
});

test('journey: subscription can be canceled', function () {
    $user = User::factory()->create([
        'subscription_status' => 'active',
    ]);

    $user->update(['subscription_status' => 'canceled']);

    expect($user->fresh()->subscription_status)->toBe('canceled');
});
