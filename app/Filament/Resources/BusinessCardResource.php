<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessCardResource\Pages;
use App\Filament\Resources\BusinessCardResource\RelationManagers;
use App\Models\BusinessCard;
use App\Models\CardSection;
use App\Models\Language;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;

class BusinessCardResource extends Resource
{
    protected static ?string $model = BusinessCard::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static string|\UnitEnum|null $navigationGroup = 'Cards';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([

                Grid::make([
                    'default' => 2,
                    'sm' => 1,  
                    'lg' => 2,
                    'xl' => 2,
                    
                ])
                
                ->schema([
                    // Card Basic Information Section
                    Schemas\Components\Section::make('Card Information')
                        ->description('Basic card details and ownership')
                        ->schema([


                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                                // ->columnSpan(1),

                            Forms\Components\Select::make('language_id')
                                ->relationship('language', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->reactive(),
                                // ->columnSpan(1),

                            // Multi-language Title with enhanced UI
                            Schemas\Components\Group::make([
                                Schemas\Components\Tabs::make('title_tabs')
                                    ->tabs(
                                        Language::active()
                                            ->get()
                                            ->map(
                                                fn(Language $language) =>
                                                Schemas\Components\Tabs\Tab::make($language->name)
                                                    ->schema([
                                                        Forms\Components\TextInput::make("title.{$language->code}")
                                                            ->label("Title ({$language->name})")
                                                            ->required($language->is_default)
                                                            ->maxLength(255)
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, $set, $get) use ($language) {
                                                                if ($language->is_default && empty($get('custom_slug'))) {
                                                                    $set('custom_slug', \Illuminate\Support\Str::slug($state));
                                                                }
                                                            }),
                                                    ])
                                            )
                                            ->toArray()
                                    )
                                    ->columnSpanFull(),
                            ]),

                            // Multi-language Subtitle
                            Schemas\Components\Group::make([
                                Schemas\Components\Tabs::make('subtitle_tabs')
                                    ->tabs(
                                        Language::active()
                                            ->get()
                                            ->map(
                                                fn(Language $language) =>
                                                Schemas\Components\Tabs\Tab::make($language->name)
                                                    ->schema([
                                                        Forms\Components\TextInput::make("subtitle.{$language->code}")
                                                            ->label("Subtitle ({$language->name})")
                                                            ->maxLength(255),
                                                    ])
                                            )
                                            ->toArray()
                                    )
                                    ->columnSpanFull(),
                            ]),
                        ])
                       
                        ->collapsible(),

                    // Media Section
                    Schemas\Components\Section::make('Media & Images')
                        ->description('Upload card images and media')
                        ->schema([
                            Forms\Components\FileUpload::make('cover_image_path')
                                ->image()
                                ->disk('public')
                                ->directory('covers')
                                ->imageEditor()
                                ->imageEditorAspectRatios([
                                    '16:9',
                                    '4:3',
                                    '1:1',
                                ])
                                ->maxSize(5120) // 5MB
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->label('Cover Image')
                                ->helperText('Recommended: 1200x675px (16:9) for best display')
                                ->columnSpan(1),

                            Forms\Components\FileUpload::make('profile_image_path')
                                ->image()
                                ->disk('public')
                                ->directory('profiles')
                                ->imageEditor()
                                ->imageEditorAspectRatios(['1:1'])
                                ->maxSize(2048) // 2MB
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                ->label('Profile Image')
                                ->helperText('Recommended: 400x400px (1:1) square format')
                                ->columnSpan(1),
                        ])
                       
                        ->collapsible()

                ])->columnSpanFull(),

                // Appearance & Design Section
                Schemas\Components\Section::make('Design & Appearance')
                    ->description('Customize the card appearance')
                    ->schema([
                        Forms\Components\Select::make('template_id')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\Select::make('theme_id')
                            ->relationship('theme', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->columnSpan(1),

                        // Enhanced theme overrides with KeyValue
                        Forms\Components\KeyValue::make('theme_overrides')
                            ->label('Theme Customizations')
                            ->keyLabel('CSS Property')
                            ->valueLabel('Value')
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                // URLs & Access Section
                Schemas\Components\Section::make('URLs & Access')
                    ->description('Configure card URLs and access settings')
                    ->schema([
                        Forms\Components\TextInput::make('custom_slug')
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->prefix('u/')
                            ->maxLength(255)
                            ->regex('/^[a-zA-Z0-9\-_]+$/')
                            ->helperText('Only letters, numbers, hyphens, and underscores allowed')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('share_url')
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('c/')
                            ->helperText('Auto-generated unique identifier')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nfc_identifier')
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->maxLength(255)
                            ->helperText('NFC tag identifier for contactless sharing')
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('full_url_preview')
                            ->label('Card URL Preview')
                            ->content(function (?BusinessCard $record): string {
                                if (!$record)
                                    return 'Will be generated after saving';
                                return $record->full_url;
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Status & Visibility Section
                Schemas\Components\Section::make('Status & Visibility')
                    ->description('Control card visibility and status')
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Make this card publicly accessible')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_primary')
                            ->label('Primary Card')
                            ->helperText('Set as the default card for this user')
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('views_count')
                            ->label('Total Views')
                            ->content(
                                fn(?BusinessCard $record): string =>
                                $record?->views_count ?? '0'
                            )
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('shares_count')
                            ->label('Total Shares')
                            ->content(
                                fn(?BusinessCard $record): string =>
                                $record?->shares_count ?? '0'
                            )
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('sections_count')
                            ->label('Sections Count')
                            ->content(
                                fn(?BusinessCard $record): string =>
                                $record?->sections()->count() ?? '0'
                            )
                            ->columnSpan(1)
                            ->dehydrated(false)
                            ->visible(false),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_image_path')
                    ->label('Avatar')
                    ->circular()
                    ->disk('public')
                    ->visibility('public')
                    ->size(50)
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->description(fn(BusinessCard $record): ?string => $record->user->email),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $defaultLang = 'en'; // Simplified approach
                            return $state[$defaultLang] ?? reset($state) ?? '-';
                        }
                        return $state ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (BusinessCard $record): ?string {
                        if (is_array($record->title)) {
                            return implode(", ", $record->title);
                        }
                        return (string) $record->title;
                    }),

                Tables\Columns\TextColumn::make('subtitle')
                    ->label('Subtitle')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $defaultLang = 'en'; // Simplified approach
                            return $state[$defaultLang] ?? reset($state) ?? '-';
                        }
                        return $state ?? '-';
                    })
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('language.name')
                    ->label('Primary Language')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Published')
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean()
                    ->label('Primary')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable()
                    ->label('Views')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('shares_count')
                    ->numeric()
                    ->sortable()
                    ->label('Shares')
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label('Sections')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Published Status')
                    ->trueLabel('Published')
                    ->falseLabel('Draft')
                    ->placeholder('All'),

                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Card')
                    ->trueLabel('Primary Cards')
                    ->falseLabel('Secondary Cards')
                    ->placeholder('All'),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('language')
                    ->relationship('language', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('has_cover_image')
                    ->label('Has Cover Image')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('cover_image_path')),

                Tables\Filters\Filter::make('popular')
                    ->label('Popular Cards (10+ views)')
                    ->query(fn(Builder $query): Builder => $query->where('views_count', '>=', 10)),
            ])
            ->actions([
                Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->url(fn(BusinessCard $record): string => $record->full_url)
                    ->openUrlInNewTab()
                    ->tooltip('Preview Card'),

                Actions\Action::make('duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (BusinessCard $record) {
                        $newCard = $record->replicate(except: ['sections_count']);
                    
                        $newCard->custom_slug = null;
                        $newCard->share_url = null;
                        $newCard->is_primary = false;
                        $newCard->is_published = false;
                        $newCard->views_count = 0;
                        $newCard->shares_count = 0;
                        $newCard->save();

                        // Duplicate sections
                        if($record->has('sections')) {
                        foreach ($record->sections as $section) {
                            $newSection = $section->replicate();
                            $newSection->business_card_id = $newCard->id;
                            $newSection->save();
                        }
                        }
                    })
                    ->requiresConfirmation()
                    ->tooltip('Duplicate Card'),

                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('publish')
                        ->icon('heroicon-o-eye')
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['is_published' => true])))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Actions\BulkAction::make('unpublish')
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn($records) => $records->each(fn($record) => $record->update(['is_published' => false])))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SectionsRelationManager::class,
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

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
