<?php

namespace App\Filament\Widgets;

use App\Models\Language;
use Filament\Widgets\Widget;

class LanguageSelectorWidget extends Widget
{
    protected string $view = 'filament.widgets.language-selector';

    protected int|string|array $columnSpan = 'full';

    public function getLanguages()
    {
        return Language::active()->get();
    }

    public function getCurrentLanguage()
    {
        return app()->getLocale();
    }
}
