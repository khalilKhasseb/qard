<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAddonResource\Pages;
use App\Models\UserAddon;
use Filament\Actions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserAddonResource extends Resource
{
    protected static ?string $model = UserAddon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-gift';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return __('filament.user_addons.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.user_addons.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.user_addons.navigation_label');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.user_addons.fields.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('addon.name')
                    ->label(__('filament.user_addons.fields.addon'))
                    ->searchable()
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
                Tables\Columns\TextColumn::make('payment.transaction_id')
                    ->label(__('filament.user_addons.fields.transaction_id'))
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('granted_by')
                    ->label(__('filament.user_addons.fields.granted_by'))
                    ->options([
                        'purchase' => __('filament.user_addons.granted_types.purchase'),
                        'admin_grant' => __('filament.user_addons.granted_types.admin_grant'),
                        'promo' => __('filament.user_addons.granted_types.promo'),
                    ]),
            ])
            ->actions([
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserAddons::route('/'),
        ];
    }
}
