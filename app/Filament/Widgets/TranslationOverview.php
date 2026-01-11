<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class TranslationOverview extends Widget
{
    protected string $view = 'filament.widgets.translation-overview';

    protected int | string | array $columnSpan = 'full';

    public function getTranslationStats()
    {
        // This would be implemented to show translation completion stats
        return [
            'total_keys' => 0,
            'translated_keys' => 0,
            'completion_percentage' => 0,
        ];
    }
}
