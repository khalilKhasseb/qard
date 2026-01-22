<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TranslationHistoryResource\Pages;
use App\Models\TranslationHistory;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Illuminate\Database\Eloquent\Builder;

class TranslationHistoryResource extends Resource
{
    protected static ?string $model = TranslationHistory::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static string|\UnitEnum|null $navigationGroup = 'Management';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make('Translation Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->label('User'),
                        Forms\Components\Select::make('business_card_id')
                            ->relationship('businessCard', 'title')
                            ->disabled()
                            ->label('Business Card'),
                        Forms\Components\TextInput::make('source_language')
                            ->disabled(),
                        Forms\Components\TextInput::make('target_language')
                            ->disabled(),
                        Forms\Components\Textarea::make('source_text')
                            ->columnSpanFull()
                            ->disabled(),
                        Forms\Components\Textarea::make('translated_text')
                            ->columnSpanFull()
                            ->disabled(),
                    ])->columns(2),
                
                Schemas\Components\Section::make('Quality & Status')
                    ->schema([
                        Forms\Components\TextInput::make('quality_score')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('verification_status')
                            ->disabled(),
                        Forms\Components\TextInput::make('cost')
                            ->numeric()
                            ->prefix('$')
                            ->disabled(),
                        Forms\Components\TextInput::make('content_hash')
                            ->disabled(),
                    ])->columns(2),

                Schemas\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\KeyValue::make('metadata')
                            ->disabled(),
                        Forms\Components\Textarea::make('error_message')
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
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('businessCard.title')
                    ->label('Card')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source_language')
                    ->label('Source')
                    ->sortable(),
                Tables\Columns\TextColumn::make('target_language')
                    ->label('Target')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quality_score')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    }),
                Tables\Columns\TextColumn::make('verification_status')
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
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('verification_status')
                    ->options([
                        'pending' => 'Pending',
                        'auto_verified' => 'Auto Verified',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'needs_review' => 'Needs Review',
                    ]),
                SelectFilter::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from'),
                        DatePicker::make('created_until'),
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
                    })
            ])
            ->recordActions([
               \Filament\Actions\ViewAction::make(),
                \Filament\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (TranslationHistory $record) => $record->update(['verification_status' => 'approved']))
                    ->visible(fn (TranslationHistory $record) => in_array($record->verification_status, ['pending', 'needs_review'])),
                \Filament\Actions\Action::make('reject')
                    ->label('Reject')
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
                    ->action(fn ( $records) => $records->each->delete()),
                \Filament\Actions\BulkAction::make('forceDelete')
                    ->requiresConfirmation()
                    ->action(fn ( $records) => $records->each->forceDelete()),
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
