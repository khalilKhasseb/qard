<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get overall stats
        $stats = [
            'total_views' => $this->analyticsService->getTotalViews($user),
            'nfc_taps' => $this->analyticsService->getTotalByType($user, 'nfc_tap'),
            'qr_scans' => $this->analyticsService->getTotalByType($user, 'qr_scan'),
            'shares' => $this->analyticsService->getTotalByType($user, 'social_share'),
        ];

        // Get card-level analytics
        $cardAnalytics = $user->cards()
            ->select(['id', 'title', 'subtitle', 'views_count', 'shares_count', 'is_published'])
            ->orderByDesc('views_count')
            ->get();

        // Get recent events
        $recentEvents = AnalyticsEvent::query()
            ->whereIn('business_card_id', $user->cards->pluck('id'))
            ->with('businessCard:id,title')
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'card_title' => $event->businessCard?->title ?? 'Unknown Card',
                    'created_at' => $event->created_at,
                ];
            });

        return Inertia::render('Analytics/Index', [
            'stats' => $stats,
            'cardAnalytics' => $cardAnalytics,
            'recentEvents' => $recentEvents,
        ]);
    }
}
