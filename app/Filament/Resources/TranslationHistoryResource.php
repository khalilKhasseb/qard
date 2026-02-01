<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslationHistoryResource\Pages;
use App\Models\TranslationHistory;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TranslationHistoryResource extends Resource
{
    protected static ?string $model = TranslationHistory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.management');
    }

    public static function getModelLabel(): string
    {
        return __('filament.translation_history.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.translation_history.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.translation_history.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.translation_history.sections.translation_details'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.translation_history.fields.user'))
                            ->relationship('user', 'name')
                            ->disabled(),
                        Forms\Components\Select::make('business_card_id')
                            ->label(__('filament.translation_history.fields.business_card'))
                            ->relationship('businessCard', 'title')
                            ->disabled(),
                        Forms\Components\TextInput::make('source_language')
                            ->label(__('filament.translation_history.fields.source_language'))
                            ->disabled(),
                        Forms\Components\TextInput::make('target_language')
                            ->label(__('filament.translation_history.fields.target_language'))
                            ->disabled(),
                        Forms\Components\Textarea::make('source_text')
                            ->label(__('filament.translation_history.fields.source_text'))
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\Textarea::make('translated_text')
                            ->label(__('filament.translation_history.fields.translated_text'))
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.translation_history.sections.quality_status'))
                    ->schema([
                        Forms\Components\TextInput::make('quality_score')
                            ->label(__('filament.translation_history.fields.quality_score'))
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('verification_status')
                            ->label(__('filament.translation_history.fields.verification_status'))
                            ->disabled(),
                        Forms\Components\TextInput::make('cost')
                            ->label(__('filament.translation_history.fields.cost'))
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\TextInput::make('content_hash')
                            ->label(__('filament.translation_history.fields.content_hash'))
                            ->disabled(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.translation_history.sections.additional_information'))
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('filament.translation_history.fields.metadata'))
                            ->disabled(),
                        Forms\Components\Textarea::make('error_message')
                            ->label(__('filament.translation_history.fields.error_message'))
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.translation_history.fields.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('businessCard.title')
                    ->label(__('filament.translation_history.fields.card'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_language')
                    ->label(__('filament.translation_history.fields.source'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_language')
                    ->label(__('filament.translation_history.fields.target'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('quality_score')
                    ->label(__('filament.translation_history.fields.quality_score'))
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('verification_status')
                    ->label(__('filament.translation_history.fields.verification_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'auto_verified' => 'info',
                        'pending', 'needs_review' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->label(__('filament.translation_history.fields.cost'))
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->label(__('filament.translation_history.fields.verification_status'))
                    ->options([
                        'pending' => __('filament.translation_history.verification_statuses.pending'),
                        'auto_verified' => __('filament.translation_history.verification_statuses.auto_verified'),
                        'approved' => __('filament.translation_history.verification_statuses.approved'),
                        'rejected' => __('filament.translation_history.verification_statuses.rejected'),
                        'needs_review' => __('filament.translation_history.verification_statuses.needs_review'),
                    ]),
                SelectFilter::make('user_id')
                    ->label(__('filament.translation_history.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label(__('filament.translation_history.filters.created_from')),
                        DatePicker::make('created_until')
                            ->label(__('filament.translation_history.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('approve')
                    ->label(__('filament.translation_history.actions.approve'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (TranslationHistory $record) => $record->update(['verification_status' => 'approved']))
                    ->visible(fn (TranslationHistory $record) => in_array($record->verification_status, ['pending', 'needs_review'])),
                \Filament\Actions\Action::make('reject')
                    ->label(__('filament.translation_history.actions.reject'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (TranslationHistory $record) => $record->update(['verification_status' => 'rejected']))
                    ->visible(fn (TranslationHistory $record) => in_array($record->verification_status, ['pending', 'needs_review'])),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('delete')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->delete()),
                    \Filament\Actions\BulkAction::make('forceDelete')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->forceDelete()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTranslationHistories::route('/'),
            'view' => Pages\ViewTranslationHistory::route('/{record}'),
        ];
    }
}
