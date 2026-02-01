<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Routes requiring active subscription
Route::middleware(['auth', 'user.verified', 'subscribed'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    // Business Cards
    Route::resource('cards', App\Http\Controllers\CardController::class);
    Route::post('/cards/{card}/publish', [App\Http\Controllers\CardController::class, 'publish'])->name('cards.publish');
    Route::post('/cards/{card}/publish-draft', [App\Http\Controllers\CardController::class, 'publishDraft'])->name('cards.publish-draft');
    Route::post('/cards/{card}/discard-draft', [App\Http\Controllers\CardController::class, 'discardDraft'])->name('cards.discard-draft');
    Route::put('/cards/{card}/sections', [App\Http\Controllers\CardController::class, 'updateSections'])->name('cards.sections.update');
    Route::post('/cards/{card}/sections', [App\Http\Controllers\SectionController::class, 'store'])->name('cards.sections.store');
    Route::post('/cards/{card}/sections/reorder', [App\Http\Controllers\SectionController::class, 'reorder'])->name('cards.sections.reorder');

    // Sections
    Route::put('/sections/{section}', [App\Http\Controllers\SectionController::class, 'update'])->name('sections.update');
    Route::delete('/sections/{section}', [App\Http\Controllers\SectionController::class, 'destroy'])->name('sections.destroy');

    // Themes
    Route::resource('themes', App\Http\Controllers\ThemeController::class);
    Route::post('/themes/{theme}/duplicate', [App\Http\Controllers\ThemeController::class, 'duplicate'])->name('themes.duplicate');

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])
        ->name('analytics.index');
});

// Routes accessible without subscription (needed to subscribe!)
Route::middleware(['auth', 'user.verified'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Payments & Subscriptions
    Route::get('/payments', [App\Http\Controllers\PaymentController::class, 'index'])
        ->name('payments.index');
    Route::get('/payments/checkout/{plan}', [App\Http\Controllers\PaymentController::class, 'checkout'])
        ->name('payments.checkout');
    Route::get('/payments/confirmation/{payment}', [App\Http\Controllers\PaymentController::class, 'confirmation'])
        ->name('payments.confirmation');
    Route::post('/payments/{plan}/initialize', [App\Http\Controllers\PaymentController::class, 'initialize'])
        ->name('payments.initialize');
    Route::get('/payments/callback', [App\Http\Controllers\PaymentController::class, 'callback'])
        ->name('payments.callback');

    // Subscription Management
    Route::get('/subscription', function () {
        $user = request()->user();
        $subscription = $user->activeSubscription()->with('subscriptionPlan')->first();
        $plan = $subscription?->subscriptionPlan;

        // Get all active plans (available for upgrade)
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        // Pass all subscription data so the page doesn't need API calls
        return Inertia::render('Subscription/Index', [
            'availablePlans' => $plans,
            'subscription' => $subscription ? [
                'data' => [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'start_date' => $subscription->start_date,
                    'end_date' => $subscription->end_date,
                    'plan' => $plan ? [
                        'id' => $plan->id,
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'billing_cycle' => $plan->billing_cycle,
                        'cards_limit' => $plan->cards_limit,
                        'themes_limit' => $plan->themes_limit,
                        'features' => $plan->features,
                    ] : null,
                ],
            ] : null,
            'usage' => [
                'cards' => [
                    'used' => $user->cards()->count(),
                    'limit' => $user->getCardLimit(),
                    'can_create' => $user->canCreateCard(),
                ],
                'themes' => [
                    'used' => $user->themes()->count(),
                    'limit' => $user->getThemeLimit(),
                    'can_create' => $user->canCreateTheme(),
                ],
            ],
        ]);
    })->name('subscription.index');

    // Subscription sync (web route for Inertia)
    Route::post('/subscription/sync', function () {
        $user = request()->user();
        $subscription = $user->activeSubscription()->first();

        if ($subscription) {
            // Sync logic here if needed
            $subscription->touch(); // Update timestamp
        }

        return redirect()->back()->with('success', __('Subscription synced successfully.'));
    })->name('subscription.sync');

    // Subscription cancel (web route for Inertia)
    Route::post('/subscription/cancel', function () {
        $user = request()->user();
        $subscription = $user->activeSubscription()->first();

        if ($subscription) {
            $subscription->update(['status' => 'cancelled']);
        }

        return redirect()->back()->with('success', __('Subscription cancelled successfully.'));
    })->name('subscription.cancel');
});

// Language switching (no authentication required)
Route::post('/language/switch', [App\Http\Controllers\Api\LanguageController::class, 'switchLanguage'])
    ->name('language.switch');

// Health Check Routes (no authentication required)
Route::get('/health', [HealthController::class, 'check'])->name('health.check');
Route::get('/version', [HealthController::class, 'version'])->name('health.version');

// Public Card Routes
Route::get('/u/{slug}', [App\Http\Controllers\PublicCardController::class, 'bySlug'])
    ->name('card.public.slug');
Route::get('/c/{shareUrl}', [App\Http\Controllers\PublicCardController::class, 'byShareUrl'])
    ->name('card.public.share');
Route::get('/nfc/{nfcId}', [App\Http\Controllers\PublicCardController::class, 'byNfc'])
    ->name('card.public.nfc');
Route::get('/qr/{shareUrl}', [App\Http\Controllers\PublicCardController::class, 'qrScan'])
    ->name('card.public.qr');

// Webhook Routes (no authentication required)
Route::post('/webhooks/lahza', [App\Http\Controllers\Webhooks\LahzaWebhookController::class, 'handle'])
    ->name('webhooks.lahza');

require __DIR__.'/auth.php';
