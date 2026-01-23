<?php

namespace App\Filament\Widgets;

use App\Models\TranslationHistory;
use App\Models\UserTranslationUsage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class TranslationUsageWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $totalTranslationsToday = TranslationHistory::whereDate('created_at', $today)->count();
        $totalTranslationsThisMonth = TranslationHistory::whereDate('created_at', '>=', $thisMonth)->count();
        $totalCostThisMonth = TranslationHistory::whereDate('created_at', '>=', $thisMonth)->sum('cost');
        
        $pendingVerifications = TranslationHistory::whereIn('verification_status', ['pending', 'needs_review'])->count();

        return [
            Stat::make('Translations Today', $totalTranslationsToday)
                ->description('AI translations processed today')
                ->descriptionIcon('heroicon-m-language')
                ->color('info'),
            Stat::make('Monthly Volume', $totalTranslationsThisMonth)
                ->description('Translations this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
            Stat::make('Estimated Cost', '$' . number_format($totalCostThisMonth, 2))
                ->description('API cost this month')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('primary'),
            Stat::make('Pending Review', $pendingVerifications)
                ->description('Translations needing verification')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($pendingVerifications > 0 ? 'warning' : 'success'),
        ];
    }
}
