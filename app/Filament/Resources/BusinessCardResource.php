<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessCardResource\Pages;
use App\Models\BusinessCard;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas;
use Filament\Tables;
use Filament\Tables\Table;

class BusinessCardResource extends Resource
{
    protected static ?string $model = BusinessCard::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-identification';

    protected static string | \UnitEnum | null $navigationGroup = 'Cards';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
               Schemas\Components\Section::make('Card Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\KeyValue::make('title')
                            ->required()
                            ->keyLabel('Language Code (e.g., en, ar)')
                            ->valueLabel('Title'),
                        Forms\Components\KeyValue::make('subtitle')
                            ->keyLabel('Language Code (e.g., en, ar)')
                            ->valueLabel('Subtitle'),
                        Forms\Components\FileUpload::make('cover_image_path')
                            ->image()
                            ->directory('covers')
                            ->label('Cover Image'),
                        Forms\Components\FileUpload::make('profile_image_path')
                            ->image()
                            ->directory('profiles')
                            ->label('Profile Image (Avatar/Logo)'),
                        Forms\Components\Select::make('language_id')
                            ->relationship('language', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),

                Schemas\Components\Section::make('Appearance')
                    ->schema([
                        Forms\Components\Select::make('template_id')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Select::make('theme_id')
                            ->relationship('theme', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\KeyValue::make('theme_overrides')
                            ->keyLabel('Property')
                            ->valueLabel('Value')
                            ->nullable(),
                    ])->columns(2),

                Schemas\Components\Section::make('URLs & Identifiers')
                    ->schema([
                        Forms\Components\TextInput::make('custom_slug')
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->prefix('u/')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('share_url')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('nfc_identifier')
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->maxLength(255),
                    ])->columns(3),

                Schemas\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->default(false),
                        Forms\Components\Toggle::make('is_primary')
                            ->label('Primary Card')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image_path')
                    ->label('Avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->formatStateUsing(fn ($state) => is_array($state) ? (reset($state) ?: '-') : $state)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subtitle')
                    ->formatStateUsing(fn ($state) => is_array($state) ? (reset($state) ?: '-') : $state)
                    ->limit(30)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('language.name')
                    ->label('Language')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published'),
                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean()
                    ->label('Primary'),
                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable()
                    ->label('Views'),
                Tables\Columns\TextColumn::make('shares_count')
                    ->numeric()
                    ->sortable()
                    ->label('Shares'),
                Tables\Columns\TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published'),
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary'),
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn (BusinessCard $record): string => $record->full_url)
                    ->openUrlInNewTab(),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinessCards::route('/'),
            'create' => Pages\CreateBusinessCard::route('/create'),
            'view' => Pages\ViewBusinessCard::route('/{record}'),
            'edit' => Pages\EditBusinessCard::route('/{record}/edit'),
        ];
    }
}
