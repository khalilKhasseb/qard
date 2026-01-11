<?php

namespace App\Filament\Resources\LanguageResource\Pages;

use App\Filament\Resources\LanguageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLanguage extends EditRecord
{
    protected static string $resource = LanguageResource::class;

    protected function afterSave(): void
    {
        // If this is set as default, unset other defaults
        if ($this->record->is_default) {
            $this->record->where('id', '!=', $this->record->id)
                ->update(['is_default' => false]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
