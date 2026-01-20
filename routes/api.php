<?php

use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ThemeController;
use Illuminate\Support\Facades\Route;

// Public analytics tracking (no auth required)
Route::post('/analytics/track', [AnalyticsController::class, 'track'])
    ->name('api.analytics.track');

// Authenticated API routes
Route::middleware('web')->group(function () {
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
