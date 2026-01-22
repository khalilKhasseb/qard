<?php

namespace App\Filament\Resources\TranslationHistoryResource\Pages;

use App\Filament\Resources\TranslationHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTranslationHistory extends ViewRecord
{
    protected static string $resource = TranslationHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
