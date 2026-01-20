<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\BusinessCard;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function getCardStats(BusinessCard $card, string $period = 'month'): array
    {
        $query = AnalyticsEvent::forCard($card->id)->inPeriod($period);

        return [
            'total_views' => (clone $query)->ofType('view')->count(),
            'nfc_taps' => (clone $query)->ofType('nfc_tap')->count(),
            'qr_scans' => (clone $query)->ofType('qr_scan')->count(),
            'shares' => (clone $query)->ofType('social_share')->count(),
            'section_clicks' => (clone $query)->ofType('section_click')->count(),
            'contact_saves' => (clone $query)->ofType('contact_save')->count(),
        ];
    }

    public function getUserStats(User $user, string $period = 'month'): array
    {
        $cardIds = $user->cards()->pluck('id');

        $query = AnalyticsEvent::whereIn('business_card_id', $cardIds)->inPeriod($period);

        return [
            'total_views' => (clone $query)->ofType('view')->count(),
            'total_interactions' => (clone $query)->count(),
            'unique_cards_viewed' => (clone $query)->distinct('business_card_id')->count('business_card_id'),
        ];
    }

    public function getViewsOverTime(BusinessCard $card, string $period = 'month', string $groupBy = 'day'): Collection
    {
        $driver = DB::connection()->getDriverName();

        $dateExpression = match ([$driver, $groupBy]) {
            ['sqlite', 'hour'] => "strftime('%Y-%m-%d %H:00', created_at)",
            ['sqlite', 'day'] => "strftime('%Y-%m-%d', created_at)",
            ['sqlite', 'week'] => "strftime('%Y-%W', created_at)",
            ['sqlite', 'month'] => "strftime('%Y-%m', created_at)",
            ['mysql', 'hour'] => "DATE_FORMAT(created_at, '%Y-%m-%d %H:00')",
            ['mysql', 'day'] => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            ['mysql', 'week'] => "DATE_FORMAT(created_at, '%Y-%u')",
            ['mysql', 'month'] => "DATE_FORMAT(created_at, '%Y-%m')",
            default => $driver === 'sqlite' ? "strftime('%Y-%m-%d', created_at)" : "DATE_FORMAT(created_at, '%Y-%m-%d')",
        };

        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->ofType('view')
            ->selectRaw("{$dateExpression} as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getTopReferrers(BusinessCard $card, string $period = 'month', int $limit = 10): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->selectRaw('referrer, COUNT(*) as count')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    public function getDeviceBreakdown(BusinessCard $card, string $period = 'month'): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->whereNotNull('device_type')
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get();
    }

    public function getBrowserBreakdown(BusinessCard $card, string $period = 'month'): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->whereNotNull('browser')
            ->selectRaw('browser, COUNT(*) as count')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get();
    }

    public function getCountryBreakdown(BusinessCard $card, string $period = 'month'): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->whereNotNull('country')
            ->selectRaw('country, COUNT(*) as count')
            ->groupBy('country')
            ->orderByDesc('count')
            ->get();
    }

    public function getSectionClickStats(BusinessCard $card, string $period = 'month'): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->ofType('section_click')
            ->with('section:id,title,section_type')
            ->selectRaw('card_section_id, COUNT(*) as count')
            ->groupBy('card_section_id')
            ->orderByDesc('count')
            ->get();
    }

    public function getEventTypeBreakdown(BusinessCard $card, string $period = 'month'): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->inPeriod($period)
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();
    }

    public function getRecentEvents(BusinessCard $card, int $limit = 20): Collection
    {
        return AnalyticsEvent::forCard($card->id)
            ->with('section:id,title')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getPlatformStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::active()->count(),
            'total_cards' => BusinessCard::count(),
            'published_cards' => BusinessCard::published()->count(),
            'total_views_today' => AnalyticsEvent::ofType('view')->inPeriod('today')->count(),
            'total_views_week' => AnalyticsEvent::ofType('view')->inPeriod('week')->count(),
        ];
    }

    // User-level analytics methods
    public function getTotalViews(User $user): int
    {
        $cardIds = $user->cards()->pluck('id');

        return AnalyticsEvent::whereIn('business_card_id', $cardIds)
            ->ofType('view')
            ->count();
    }

    public function getTotalShares(User $user): int
    {
        $cardIds = $user->cards()->pluck('id');

        return AnalyticsEvent::whereIn('business_card_id', $cardIds)
            ->ofType('social_share')
            ->count();
    }

    public function getTotalNfcTaps(User $user): int
    {
        $cardIds = $user->cards()->pluck('id');

        return AnalyticsEvent::whereIn('business_card_id', $cardIds)
            ->ofType('nfc_tap')
            ->count();
    }

    public function getTotalByType(User $user, string $type): int
    {
        $cardIds = $user->cards()->pluck('id');

        return AnalyticsEvent::whereIn('business_card_id', $cardIds)
            ->ofType($type)
            ->count();
    }

    public function getUserRecentEvents(User $user, int $limit = 10): Collection
    {
        $cardIds = $user->cards()->pluck('id');

        return AnalyticsEvent::whereIn('business_card_id', $cardIds)
            ->with(['businessCard:id,title', 'section:id,title'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getCardViewsChart(User $user, int $days = 7): array
    {
        $cardIds = $user->cards()->pluck('id');

        $data = AnalyticsEvent::whereIn('business_card_id', $cardIds)
            ->ofType('view')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $counts = [];

        // Fill in all dates even if no data
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $dayData = $data->firstWhere('date', $date);
            $counts[] = $dayData ? $dayData->count : 0;
        }

        return [
            'dates' => $dates,
            'counts' => $counts,
        ];
    }
}
