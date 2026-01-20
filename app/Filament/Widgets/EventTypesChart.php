<?php

namespace App\Filament\Widgets;

use App\Models\AnalyticsEvent;
use Filament\Widgets\ChartWidget;

class EventTypesChart extends ChartWidget
{
    protected ?string $heading = 'Event Types Distribution';

    protected int|string|array $columnSpan = 1;

    public static function getSort(): int
    {
        return 4;
    }

    protected function getData(): array
    {
        $data = AnalyticsEvent::where('created_at', '>=', now()->subMonth())
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->orderByDesc('count')
            ->get();

        $labels = $data->pluck('event_type')->map(fn ($type) => match ($type) {
            'view' => 'Page Views',
            'nfc_tap' => 'NFC Taps',
            'qr_scan' => 'QR Scans',
            'social_share' => 'Shares',
            'section_click' => 'Section Clicks',
            'contact_save' => 'Contact Saves',
            'link_click' => 'Link Clicks',
            default => ucfirst($type),
        })->toArray();

        $colors = [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(239, 68, 68, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Events',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
