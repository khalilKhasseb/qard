<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AddonsRelationManager extends RelationManager
{
    protected static string $relationship = 'userAddons';

    protected static ?string $recordTitleAttribute = 'addon.name';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('addon.name')
                    ->label(__('filament.user_addons.fields.addon'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('addon.type')
                    ->label(__('filament.addons.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'extra_cards' => 'info',
                        'feature_unlock' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('granted_by')
                    ->label(__('filament.user_addons.fields.granted_by'))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'purchase' => 'success',
                        'admin_grant' => 'warning',
                        'promo' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('notes')
                    ->label(__('filament.common.notes'))
                    ->limit(30),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                \Filament\Actions\DeleteAction::make(),
            ]);
    }
}
