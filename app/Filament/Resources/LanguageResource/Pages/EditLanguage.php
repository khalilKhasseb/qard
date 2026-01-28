<?php

namespace App\Filament\Resources\LanguageResource\Pages;

use App\Filament\Resources\LanguageResource;
use App\Models\Language;
use App\Services\AiLabelTranslationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
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
            Actions\Action::make('translateLabels')
                ->label('Auto-Translate Labels')
                ->icon('heroicon-o-language')
                ->color('primary')
                ->requiresConfirmation()
                ->schema([
                    Forms\Components\Toggle::make('overwrite')
                        ->label('Overwrite existing labels')
                        ->default(false),
                ])
                ->action(function (array $data): void {
                    $sourceLanguage = Language::default()->first()
                        ?? Language::where('code', 'en')->first();

                    if (! $sourceLanguage) {
                        Notification::make()
                            ->title('No source language found')
                            ->body('Set a default language or create an English language record with labels.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        app(AiLabelTranslationService::class)->translateAndPersist(
                            sourceLanguage: $sourceLanguage,
                            targetLanguage: $this->record,
                            user: auth()->user(),
                            overwrite: (bool) ($data['overwrite'] ?? false),
                        );

                        $this->record->refresh();
                        $this->form->fill($this->record->toArray());

                        Notification::make()
                            ->title('Labels translated')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Label translation failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
