<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $stats = [
            'total_cards' => $user->cards()->count(),
            'published_cards' => $user->cards()->where('is_published', true)->count(),
            'total_views' => $user->cards()->sum('views_count'),
            'total_themes' => $user->themes()->count(),
            'total_nfc_taps' => $user->cards()->sum('nfc_taps_count'),
        ];

        $recentCards = $user->cards()
            ->latest()
            ->take(5)
            ->get();

        $subscription = $user->activeSubscription()
            ->with('subscriptionPlan')
            ->first();

        $cardLimit = $user->getCardLimit();
        $extraCardSlots = $user->getExtraCardSlots();

        $purchasedAddons = $user->userAddons()
            ->with('addon')
            ->latest()
            ->get()
            ->map(fn ($ua) => [
                'id' => $ua->id,
                'name' => $ua->addon->name,
                'type' => $ua->addon->type,
                'value' => $ua->addon->value,
                'feature_key' => $ua->addon->feature_key,
                'created_at' => $ua->created_at,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentCards' => $recentCards,
            'subscription' => $subscription,
            'cardLimit' => $cardLimit,
            'extraCardSlots' => $extraCardSlots,
            'purchasedAddons' => $purchasedAddons,
        ]);
    }
}
