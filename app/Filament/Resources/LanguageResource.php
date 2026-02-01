<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageResource\Pages;
use App\Models\Language;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables\Columns;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.system_management');
    }

    public static function getModelLabel(): string
    {
        return __('filament.languages.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.languages.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.languages.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.languages.sections.language_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.languages.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->label(__('filament.languages.fields.code'))
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('direction')
                            ->label(__('filament.languages.fields.direction'))
                            ->options([
                                'ltr' => __('filament.languages.directions.ltr'),
                                'rtl' => __('filament.languages.directions.rtl'),
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('filament.languages.fields.is_active'))
                            ->required(),
                        Forms\Components\Toggle::make('is_default')
                            ->label(__('filament.languages.fields.is_default'))
                            ->required(),
                    ])->columns(2),
                Schemas\Components\Section::make(__('filament.languages.sections.ui_labels'))
                    ->description(__('filament.languages.sections.labels_description'))
                    ->schema([
                        Forms\Components\Repeater::make('labels')
                            ->label(__('filament.languages.fields.labels'))
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->label(__('filament.common.key'))
                                    ->required(),
                                Forms\Components\TextInput::make('value')
                                    ->label(__('filament.common.value'))
                                    ->required(),
                            ])
                            ->columns(2)
                            ->default([])
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, $state): void {
                                if (is_array($state) && array_keys($state) !== range(0, count($state) - 1)) {
                                    $component->state(
                                        collect($state)
                                            ->map(fn ($value, $key) => ['key' => $key, 'value' => $value])
                                            ->values()
                                            ->all()
                                    );
                                }
                            })
                            ->dehydrateStateUsing(function ($state): array {
                                if (! is_array($state)) {
                                    return [];
                                }
                                $out = [];
                                foreach ($state as $row) {
                                    if (! isset($row['key'])) {
                                        continue;
                                    }
                                    $out[$row['key']] = $row['value'] ?? '';
                                }

                                return $out;
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label(__('filament.languages.fields.name'))
                    ->searchable(),
                Columns\TextColumn::make('code')
                    ->label(__('filament.languages.fields.code'))
                    ->searchable(),
                Columns\TextColumn::make('direction')
                    ->label(__('filament.languages.fields.direction')),
                ToggleColumn::make('is_active')
                    ->label(__('filament.languages.fields.is_active')),
                ToggleColumn::make('is_default')
                    ->label(__('filament.languages.fields.is_default')),
                Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Columns\TextColumn::make('updated_at')
                    ->label(__('filament.common.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkAction::make('delete'),

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
            'index' => Pages\ListLanguages::route('/'),
            'create' => Pages\CreateLanguage::route('/create'),
            'edit' => Pages\EditLanguage::route('/{record}/edit'),
        ];
    }
}
