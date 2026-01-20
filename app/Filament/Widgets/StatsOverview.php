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
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Active Subscriptions', User::where('subscription_status', 'active')->count())
                ->description('Paid subscribers')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('success'),

            Stat::make('Published Cards', BusinessCard::where('is_published', true)->count())
                ->description('Active business cards')
                ->descriptionIcon('heroicon-m-identification')
                ->color('info'),

            Stat::make('Weekly Views', AnalyticsEvent::where('event_type', 'view')
                ->where('created_at', '>=', now()->subWeek())
                ->count())
                ->description('Card views this week')
                ->descriptionIcon('heroicon-m-eye')
                ->color('warning'),
        ];
    }
}
