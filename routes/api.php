<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ThemeController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Public analytics tracking (no auth required)
Route::post('/analytics/track', [AnalyticsController::class, 'track'])
    ->name('api.analytics.track');

// Authenticated API routes
Route::middleware(['web', 'auth'])->group(function () {
    // Business Cards
    Route::apiResource('cards', CardController::class)->names([
        'index' => 'api.cards.index',
        'store' => 'api.cards.store',
        'show' => 'api.cards.show',
        'update' => 'api.cards.update',
        'destroy' => 'api.cards.destroy',
    ]);
    Route::post('cards/{card}/publish', [CardController::class, 'publish'])
        ->name('api.cards.publish');
    Route::post('cards/{card}/duplicate', [CardController::class, 'duplicate'])
        ->name('api.cards.duplicate');
    Route::get('cards/{card}/analytics', [CardController::class, 'analytics'])
        ->name('api.cards.analytics');

    // Card Sections
    Route::post('cards/{card}/sections', [SectionController::class, 'store'])
        ->name('api.sections.store');
    Route::put('sections/{section}', [SectionController::class, 'update'])
        ->name('api.sections.update');
    Route::delete('sections/{section}', [SectionController::class, 'destroy'])
        ->name('api.sections.destroy');
    Route::post('cards/{card}/sections/reorder', [SectionController::class, 'reorder'])
        ->name('api.sections.reorder');

    // Gallery image quick upload (immediate per-item upload)
    Route::post('sections/{section}/gallery-upload', [SectionController::class, 'uploadGallery'])
        ->name('api.sections.gallery.upload');

    // Themes
    Route::apiResource('themes', ThemeController::class)->names([
        'index' => 'api.themes.index',
        'store' => 'api.themes.store',
        'show' => 'api.themes.show',
        'update' => 'api.themes.update',
        'destroy' => 'api.themes.destroy',
    ]);
    Route::post('themes/{theme}/duplicate', [ThemeController::class, 'duplicate'])
        ->name('api.themes.duplicate');
    Route::post('themes/{theme}/apply/{card}', [ThemeController::class, 'apply'])
        ->name('api.themes.apply');
    Route::post('themes/upload', [ThemeController::class, 'upload'])
        ->name('api.themes.upload');
    Route::post('themes/preview-css', [ThemeController::class, 'previewCss'])
        ->name('api.themes.preview_css');
    Route::post('themes/preview', [ThemeController::class, 'preview'])
        ->name('api.themes.preview');

    // Payments & Subscriptions
    Route::get('subscription-plans', [PaymentController::class, 'plans'])
        ->name('api.subscription-plans.index');
    Route::post('payments', [PaymentController::class, 'create'])
        ->name('api.payments.create');
    Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm'])
        ->name('api.payments.confirm');
    Route::get('payments/history', [PaymentController::class, 'history'])
        ->name('api.payments.history');
    Route::get('payments/pending', [PaymentController::class, 'pending'])
        ->name('api.payments.pending');

    Route::get('subscription', [SubscriptionController::class, 'show'])
        ->name('api.subscription.show');
    Route::post('subscription/sync', [SubscriptionController::class, 'sync'])
        ->name('api.subscription.sync');
    Route::post('subscription/cancel', [SubscriptionController::class, 'cancel'])
        ->name('api.subscription.cancel');

    // Usage stats endpoint
    Route::get('usage', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'cardCount' => $user->cards()->count(),
            'themeCount' => $user->themes()->count(),
        ]);
    })->name('api.usage');
});

// Language API routes (public)
Route::prefix('language')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\LanguageController::class, 'index'])
        ->name('api.language.index');
    Route::get('/{language}', [\App\Http\Controllers\Api\LanguageController::class, 'show'])
        ->name('api.language.show');
    Route::post('/switch', [\App\Http\Controllers\Api\LanguageController::class, 'switchLanguage'])
        ->name('api.language.switch');
});

// Translation API routes (authenticated)
Route::middleware('auth:sanctum')->prefix('translations')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\TranslationController::class, 'index'])
        ->name('api.translations.index');
    Route::post('/', [\App\Http\Controllers\Api\TranslationController::class, 'store'])
        ->name('api.translations.store');
    Route::get('/{translation}', [\App\Http\Controllers\Api\TranslationController::class, 'show'])
        ->name('api.translations.show');
    Route::put('/{translation}', [\App\Http\Controllers\Api\TranslationController::class, 'update'])
        ->name('api.translations.update');
    Route::delete('/{translation}', [\App\Http\Controllers\Api\TranslationController::class, 'destroy'])
        ->name('api.translations.destroy');
});

// AI Translation routes (authenticated with rate limiting)
Route::middleware(['web', 'auth', 'throttle:ai-translation'])->prefix('ai-translate')->group(function () {
    // Translate single section
    Route::post('/sections/{section}', [\App\Http\Controllers\TranslationController::class, 'translateSection'])
        ->name('api.ai-translate.section');
    
    // Translate entire card
    Route::post('/cards/{card}', [\App\Http\Controllers\TranslationController::class, 'translateCard'])
        ->name('api.ai-translate.card');
    
    // Get available languages for card
    Route::get('/cards/{card}/languages', [\App\Http\Controllers\TranslationController::class, 'availableLanguages'])
        ->name('api.ai-translate.languages');
    
    // Verify translation
    Route::post('/history/{translation}/verify', [\App\Http\Controllers\TranslationController::class, 'verifyTranslation'])
        ->name('api.ai-translate.verify');
});

// Translation history and credits (with separate rate limiter)
Route::middleware(['web', 'auth', 'throttle:translation-history'])->prefix('ai-translate')->group(function () {
    // Get translation history
    Route::get('/history', [\App\Http\Controllers\TranslationController::class, 'history'])
        ->name('api.ai-translate.history');
    
    // Get card translation history
    Route::get('/cards/{card}/history', [\App\Http\Controllers\TranslationController::class, 'cardHistory'])
        ->name('api.ai-translate.card-history');
    
    // Get user's translation credits
    Route::get('/credits', [\App\Http\Controllers\TranslationController::class, 'credits'])
        ->name('api.ai-translate.credits');
    
    // Server-sent events for real-time translation updates
    Route::get('/events/{card}', [\App\Http\Controllers\TranslationSseController::class, 'streamEvents'])
        ->name('api.ai-translate.events');
});
