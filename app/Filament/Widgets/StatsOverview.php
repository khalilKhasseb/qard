<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use App\Models\BusinessCard;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    public static function getSort(): int
    {
        return 1;
    }

    protected function getStats(): array
    {
        return [
            Stat::make(__('filament.widgets.stats.total_users'), User::count())
                ->description(__('filament.widgets.stats.registered_users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('filament.widgets.stats.active_subscriptions'), User::where('subscription_status', 'active')->count())
                ->description(__('filament.widgets.stats.paid_subscribers'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make(__('filament.widgets.stats.published_cards'), BusinessCard::where('is_published', true)->count())
                ->description(__('filament.widgets.stats.active_cards'))
                ->descriptionIcon('heroicon-m-identification')
                ->color('info'),

            Stat::make(__('filament.widgets.stats.weekly_views'), AnalyticsEvent::where('event_type', 'view')
                ->where('created_at', '>=', now()->subWeek())
                ->count())
                ->description(__('filament.widgets.stats.views_this_week'))
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),
        ];
    }
}
