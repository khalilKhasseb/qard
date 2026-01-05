<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;

class CardViewsChart extends ChartWidget
{
    protected ?string $heading = 'Card Views (Last 7 Days)';

    protected int | string | array $columnSpan = 1;

    public static function getSort(): int
    {
        return 3;
    }

    protected function getData(): array
    {
        $data = AnalyticsEvent::where('event_type', 'view')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('D');
            $values[] = $data->where('date', $date)->first()?->count ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Views',
                    'data' => $values,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
