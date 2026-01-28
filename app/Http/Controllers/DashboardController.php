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

        // dd($subscription);
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentCards' => $recentCards,
            'subscription' => $subscription,
        ]);
    }
}
