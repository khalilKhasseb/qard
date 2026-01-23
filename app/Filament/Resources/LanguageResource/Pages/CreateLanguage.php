<?php

namespace App\Filament\Resources\LanguageResource\Pages;

use App\Filament\Resources\LanguageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLanguage extends CreateRecord
{
    protected static string $resource = LanguageResource::class;

    protected function afterCreate(): void
    {
        // If this is set as default, unset other defaults
        if ($this->record->is_default) {
            $this->record->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }
}
