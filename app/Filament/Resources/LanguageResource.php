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
use Filament\Tables\Table;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-language';

    protected static string|\UnitEnum|null $navigationGroup = 'System Management';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make('Language Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(10)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('direction')
                            ->options([
                                'ltr' => 'Left to Right',
                                'rtl' => 'Right to Left',
                            ])
                            ->required(),
                        Forms\Components\Toggle::make('is_active')
                            ->required(),
                        Forms\Components\Toggle::make('is_default')
                            ->required(),
                    ])->columns(2),
                Schemas\Components\Section::make('UI Labels')
                    ->description('Manage label translations for this language')
                    ->schema([
                        Forms\Components\Repeater::make('labels')
                            ->label('Labels')
                            ->schema([
                                Forms\Components\TextInput::make('key')
                                    ->required(),
                                Forms\Components\TextInput::make('value')
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
                                if (!is_array($state)) {
                                    return [];
                                }
                                $out = [];
                                foreach ($state as $row) {
                                    if (!isset($row['key'])) {
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
                    ->searchable(),
                Columns\TextColumn::make('code')
                    ->searchable(),
                Columns\TextColumn::make('direction'),
                Columns\IconColumn::make('is_active')
                    ->boolean(),
                Columns\IconColumn::make('is_default')
                    ->boolean(),
                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Columns\TextColumn::make('updated_at')
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
