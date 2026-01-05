<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Business Cards
    Route::resource('cards', App\Http\Controllers\CardController::class);
    Route::post('/cards/{card}/publish', [App\Http\Controllers\CardController::class, 'publish'])->name('cards.publish');
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

    // Payments & Subscriptions
    Route::get('/payments', [App\Http\Controllers\PaymentController::class, 'index'])
        ->name('payments.index');
    Route::get('/payments/checkout/{plan}', [App\Http\Controllers\PaymentController::class, 'checkout'])
        ->name('payments.checkout');
    Route::get('/payments/confirmation/{payment}', [App\Http\Controllers\PaymentController::class, 'confirmation'])
        ->name('payments.confirmation');
});

// Public Card Routes
Route::get('/u/{slug}', [App\Http\Controllers\PublicCardController::class, 'bySlug'])
    ->name('card.public.slug');
Route::get('/c/{shareUrl}', [App\Http\Controllers\PublicCardController::class, 'byShareUrl'])
    ->name('card.public.share');
Route::get('/nfc/{nfcId}', [App\Http\Controllers\PublicCardController::class, 'byNfc'])
    ->name('card.public.nfc');
Route::get('/qr/{shareUrl}', [App\Http\Controllers\PublicCardController::class, 'qrScan'])
    ->name('card.public.qr');

require __DIR__.'/auth.php';
