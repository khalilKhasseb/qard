<?php

namespace App\Filament\Resources\ThemeResource\Pages;

use App\Filament\Resources\ThemeResource;
use App\Models\Theme;
use Filament\Resources\Pages\CreateRecord;

class CreateTheme extends CreateRecord
{
    protected static string $resource = ThemeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $defaultConfig = Theme::getDefaultConfig();

        if (isset($data['config'])) {
            $data['config'] = array_replace_recursive($defaultConfig, $data['config']);
        } else {
            $data['config'] = $defaultConfig;
        }

        return $data;
    }
}
