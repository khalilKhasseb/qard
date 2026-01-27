<?php

namespace App\Filament\Resources\BusinessCardResource\RelationManagers;

use App\Models\CardSection;
use App\Models\Language;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = 'Card Sections';

    protected static ?string $modelLabel = 'Section';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl('/images/default-section.png'),

                Tables\Columns\TextColumn::make('title')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $defaultLang = 'en'; // Simplified approach

                            return $state[$defaultLang] ?? reset($state) ?? 'Untitled';
                        }

                        return $state ?: 'Untitled';
                    })
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('section_type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => CardSection::SECTION_TYPES[$state] ?? ucfirst($state))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'contact' => 'primary',
                        'social' => 'success',
                        'services' => 'warning',
                        'gallery' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('section_type')
                    ->options(CardSection::SECTION_TYPES)
                    ->multiple(),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->headerActions([
                Actions\CreateAction::make()
                    ->form([
                        Forms\Components\Select::make('section_type')
                            ->options(CardSection::SECTION_TYPES)
                            ->required()
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(function () {
                                $maxOrder = CardSection::query()->where('business_card_id', $this->ownerRecord->id)->max('sort_order');

                                return ($maxOrder ?? 0) + 1;
                            })
                            ->required()
                            ->columnSpan(1),

                        // Multi-language title using KeyValue for compatibility
                        Forms\Components\KeyValue::make('title')
                            ->label('Title (Multi-language)')
                            ->keyLabel('Language Code (en, ar, etc.)')
                            ->valueLabel('Title')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpan(1),

                        // Simplified content based on section type - using KeyValue for now
                        Forms\Components\KeyValue::make('content')
                            ->label('Content (JSON)')
                            ->keyLabel('Field')
                            ->valueLabel('Value')
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('section_type') !== null),

                        Forms\Components\FileUpload::make('image_path')
                            ->image()
                            ->disk('public')
                            ->directory('sections')
                            ->label('Section Image')
                            ->helperText('Optional image for this section')
                            ->columnSpanFull(),
                    ]),
            ])
            ->actions([
                Actions\ViewAction::make()
                    ->form([
                        Forms\Components\TextInput::make('section_type')
                            ->disabled(),
                        Forms\Components\TextInput::make('sort_order')
                            ->disabled(),
                        Forms\Components\KeyValue::make('title')
                            ->disabled(),
                        Forms\Components\KeyValue::make('content')
                            ->disabled(),
                        Forms\Components\Toggle::make('is_active')
                            ->disabled(),
                    ]),

                Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('section_type')
                            ->options(CardSection::SECTION_TYPES)
                            ->required()
                            ->reactive(),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->required(),

                        Forms\Components\KeyValue::make('title')
                            ->label('Title (Multi-language)')
                            ->keyLabel('Language Code (en, ar, etc.)')
                            ->valueLabel('Title'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\KeyValue::make('content')
                            ->label('Content (JSON)')
                            ->keyLabel('Field')
                            ->valueLabel('Value'),

                        Forms\Components\FileUpload::make('image_path')
                            ->image()
                            ->disk('public')
                            ->directory('sections')
                            ->label('Section Image'),
                    ]),

                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('activate')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => true])))
                        ->icon('heroicon-o-eye')
                        ->requiresConfirmation(),

                    Actions\BulkAction::make('deactivate')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_active' => false])))
                        ->icon('heroicon-o-eye-slash')
                        ->requiresConfirmation(),

                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order');
    }
}
