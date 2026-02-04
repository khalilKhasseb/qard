<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddonResource\Pages;
use App\Models\Addon;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AddonResource extends Resource
{
    protected static ?string $model = Addon::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return __('filament.addons.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.addons.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.addons.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.addons.sections.addon_details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.addons.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('filament.addons.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('filament.addons.fields.description'))
                            ->rows(3)
                            ->nullable(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.addons.sections.type_config'))
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label(__('filament.addons.fields.type'))
                            ->options([
                                'extra_cards' => __('filament.addons.types.extra_cards'),
                                'feature_unlock' => __('filament.addons.types.feature_unlock'),
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('feature_key')
                            ->label(__('filament.addons.fields.feature_key'))
                            ->options([
                                'nfc' => 'NFC',
                                'custom_domain' => __('filament.addons.feature_keys.custom_domain'),
                                'analytics' => __('filament.addons.feature_keys.analytics'),
                                'custom_css' => __('filament.addons.feature_keys.custom_css'),
                            ])
                            ->visible(fn (Schemas\Components\Utilities\Get $get): bool => $get('type') === 'feature_unlock')
                            ->required(fn (Schemas\Components\Utilities\Get $get): bool => $get('type') === 'feature_unlock'),
                        Forms\Components\TextInput::make('value')
                            ->label(__('filament.addons.fields.value'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('filament.addons.fields.value_helper')),
                    ])->columns(3),

                Schemas\Components\Section::make(__('filament.addons.sections.pricing'))
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label(__('filament.addons.fields.price'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('currency')
                            ->label(__('filament.addons.fields.currency'))
                            ->default('USD')
                            ->maxLength(10),
                        Forms\Components\TextInput::make('sort_order')
                            ->label(__('filament.addons.fields.sort_order'))
                            ->numeric()
                            ->default(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('filament.addons.fields.is_active'))
                            ->default(true),
                    ])->columns(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.addons.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('filament.addons.fields.type'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'extra_cards' => 'info',
                        'feature_unlock' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('feature_key')
                    ->label(__('filament.addons.fields.feature_key'))
                    ->badge()
                    ->color('warning')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('value')
                    ->label(__('filament.addons.fields.value'))
                    ->numeric(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament.addons.fields.price'))
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.addons.fields.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('user_addons_count')
                    ->counts('userAddons')
                    ->label(__('filament.addons.fields.purchases')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament.addons.fields.is_active')),
                Tables\Filters\SelectFilter::make('type')
                    ->label(__('filament.addons.fields.type'))
                    ->options([
                        'extra_cards' => __('filament.addons.types.extra_cards'),
                        'feature_unlock' => __('filament.addons.types.feature_unlock'),
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddons::route('/'),
            'create' => Pages\CreateAddon::route('/create'),
            'edit' => Pages\EditAddon::route('/{record}/edit'),
        ];
    }
}
