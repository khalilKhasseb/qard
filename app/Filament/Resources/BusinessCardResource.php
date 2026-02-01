<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessCardResource\Pages;
use App\Filament\Resources\BusinessCardResource\RelationManagers;
use App\Models\BusinessCard;
use App\Models\Language;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessCardResource extends Resource
{
    protected static ?string $model = BusinessCard::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.cards');
    }

    public static function getModelLabel(): string
    {
        return __('filament.business_cards.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.business_cards.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.business_cards.navigation_label');
    }

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
                        Schemas\Components\Section::make(__('filament.business_cards.sections.card_information'))
                            ->description(__('filament.business_cards.sections.card_description'))
                            ->schema([

                                Forms\Components\Select::make('user_id')
                                    ->label(__('filament.business_cards.fields.user'))
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\Select::make('language_id')
                                    ->label(__('filament.business_cards.fields.language'))
                                    ->relationship('language', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive(),

                                // Multi-language Title with enhanced UI
                                Schemas\Components\Group::make([
                                    Schemas\Components\Tabs::make('title_tabs')
                                        ->tabs(
                                            Language::active()
                                                ->get()
                                                ->map(
                                                    fn (Language $language) => Schemas\Components\Tabs\Tab::make($language->name)
                                                        ->schema([
                                                            Forms\Components\TextInput::make("title.{$language->code}")
                                                                ->label(__('filament.business_cards.fields.title')." ({$language->name})")
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
                                                    fn (Language $language) => Schemas\Components\Tabs\Tab::make($language->name)
                                                        ->schema([
                                                            Forms\Components\TextInput::make("subtitle.{$language->code}")
                                                                ->label(__('filament.business_cards.fields.subtitle')." ({$language->name})")
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
                        Schemas\Components\Section::make(__('filament.business_cards.sections.media_images'))
                            ->description(__('filament.business_cards.sections.media_description'))
                            ->schema([
                                Forms\Components\FileUpload::make('cover_image_path')
                                    ->image()
                                    ->disk('public')
                                    ->directory(fn ($record) => $record ? "users/{$record->user_id}/cards/{$record->id}/cover" : 'covers')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios([
                                        '16:9',
                                        '4:3',
                                        '1:1',
                                    ])
                                    ->maxSize(5120) // 5MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->label(__('filament.business_cards.fields.cover_image'))
                                    ->helperText(__('filament.business_cards.fields.cover_helper'))
                                    ->columnSpan(1),

                                Forms\Components\FileUpload::make('profile_image_path')
                                    ->image()
                                    ->disk('public')
                                    ->directory(fn ($record) => $record ? "users/{$record->user_id}/cards/{$record->id}/profile" : 'profiles')
                                    ->visibility('public')
                                    ->imageEditor()
                                    ->imageEditorAspectRatios(['1:1'])
                                    ->maxSize(2048) // 2MB
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->label(__('filament.business_cards.fields.profile_image'))
                                    ->helperText(__('filament.business_cards.fields.profile_helper'))
                                    ->columnSpan(1),
                            ])

                            ->collapsible(),

                    ])->columnSpanFull(),

                // Appearance & Design Section
                Schemas\Components\Section::make(__('filament.business_cards.sections.design_appearance'))
                    ->description(__('filament.business_cards.sections.design_description'))
                    ->schema([
                        Forms\Components\Select::make('template_id')
                            ->label(__('filament.business_cards.fields.template'))
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\Select::make('theme_id')
                            ->label(__('filament.business_cards.fields.theme'))
                            ->relationship('theme', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->columnSpan(1),

                        // Enhanced theme overrides with KeyValue
                        Forms\Components\KeyValue::make('theme_overrides')
                            ->label(__('filament.business_cards.fields.theme_customizations'))
                            ->keyLabel(__('filament.business_cards.fields.css_property'))
                            ->valueLabel(__('filament.common.value'))
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

                // URLs & Access Section
                Schemas\Components\Section::make(__('filament.business_cards.sections.urls_access'))
                    ->description(__('filament.business_cards.sections.urls_description'))
                    ->schema([
                        Forms\Components\TextInput::make('custom_slug')
                            ->label(__('filament.business_cards.fields.custom_slug'))
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->prefix('u/')
                            ->maxLength(255)
                            ->regex('/^[a-zA-Z0-9\-_]+$/')
                            ->helperText(__('filament.business_cards.fields.slug_helper'))
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('share_url')
                            ->label(__('filament.business_cards.fields.share_url'))
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('c/')
                            ->helperText(__('filament.business_cards.fields.share_helper'))
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('nfc_identifier')
                            ->label(__('filament.business_cards.fields.nfc_identifier'))
                            ->unique(ignoreRecord: true)
                            ->nullable()
                            ->maxLength(255)
                            ->helperText(__('filament.business_cards.fields.nfc_helper'))
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('full_url_preview')
                            ->label(__('filament.business_cards.fields.full_url_preview'))
                            ->content(function (?BusinessCard $record): string {
                                if (! $record) {
                                    return __('filament.business_cards.fields.url_generated_after_save');
                                }

                                return $record->full_url;
                            })
                            ->columnSpan(1),
                    ])
                    ->columns(2)
                    ->collapsible(),

                // Status & Visibility Section
                Schemas\Components\Section::make(__('filament.business_cards.sections.status_visibility'))
                    ->description(__('filament.business_cards.sections.status_description'))
                    ->schema([
                        Forms\Components\Toggle::make('is_published')
                            ->label(__('filament.business_cards.fields.is_published'))
                            ->helperText(__('filament.business_cards.fields.published_helper'))
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_primary')
                            ->label(__('filament.business_cards.fields.is_primary'))
                            ->helperText(__('filament.business_cards.fields.primary_helper'))
                            ->default(false)
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('views_count')
                            ->label(__('filament.business_cards.fields.views_count'))
                            ->content(
                                fn (?BusinessCard $record): string => $record?->views_count ?? '0'
                            )
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('shares_count')
                            ->label(__('filament.business_cards.fields.shares_count'))
                            ->content(
                                fn (?BusinessCard $record): string => $record?->shares_count ?? '0'
                            )
                            ->columnSpan(1),

                        Forms\Components\Placeholder::make('sections_count')
                            ->label(__('filament.business_cards.fields.sections_count'))
                            ->content(
                                fn (?BusinessCard $record): string => $record?->sections()->count() ?? '0'
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
                    ->label(__('filament.business_cards.fields.avatar'))
                    ->circular()
                    ->disk('public')
                    ->visibility('public')
                    ->size(50)
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.business_cards.fields.owner'))
                    ->searchable(['users.name', 'users.email'])
                    ->sortable()
                    ->description(fn (BusinessCard $record): ?string => $record->user->email),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('filament.business_cards.fields.title'))
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $defaultLang = 'en';

                            return $state[$defaultLang] ?? reset($state) ?? '-';
                        }

                        return $state ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (BusinessCard $record): ?string {
                        if (is_array($record->title)) {
                            return implode(', ', $record->title);
                        }

                        return (string) $record->title;
                    }),

                Tables\Columns\TextColumn::make('subtitle')
                    ->label(__('filament.business_cards.fields.subtitle'))
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) {
                            $defaultLang = 'en';

                            return $state[$defaultLang] ?? reset($state) ?? '-';
                        }

                        return $state ?? '-';
                    })
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('language.name')
                    ->label(__('filament.business_cards.fields.primary_language'))
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label(__('filament.business_cards.fields.published'))
                    ->trueColor('success')
                    ->falseColor('danger'),

                Tables\Columns\IconColumn::make('is_primary')
                    ->boolean()
                    ->label(__('filament.business_cards.fields.primary'))
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->numeric()
                    ->sortable()
                    ->label(__('filament.business_cards.fields.views'))
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('shares_count')
                    ->numeric()
                    ->sortable()
                    ->label(__('filament.business_cards.fields.shares'))
                    ->alignCenter()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('sections_count')
                    ->counts('sections')
                    ->label(__('filament.business_cards.fields.sections'))
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label(__('filament.business_cards.filters.published_status'))
                    ->trueLabel(__('filament.business_cards.filters.published'))
                    ->falseLabel(__('filament.business_cards.filters.draft'))
                    ->placeholder(__('filament.common.all')),

                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label(__('filament.business_cards.filters.primary_card'))
                    ->trueLabel(__('filament.business_cards.filters.primary_cards'))
                    ->falseLabel(__('filament.business_cards.filters.secondary_cards'))
                    ->placeholder(__('filament.common.all')),

                Tables\Filters\SelectFilter::make('user')
                    ->label(__('filament.business_cards.fields.user'))
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('language')
                    ->label(__('filament.business_cards.fields.language'))
                    ->relationship('language', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('has_cover_image')
                    ->label(__('filament.business_cards.filters.has_cover_image'))
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('cover_image_path')),

                Tables\Filters\Filter::make('popular')
                    ->label(__('filament.business_cards.filters.popular'))
                    ->query(fn (Builder $query): Builder => $query->where('views_count', '>=', 10)),
            ])
            ->actions([
                Actions\Action::make('preview')
                    ->label(__('filament.business_cards.actions.preview'))
                    ->icon('heroicon-o-eye')
                    ->url(fn (BusinessCard $record): string => $record->full_url)
                    ->openUrlInNewTab()
                    ->tooltip(__('filament.business_cards.actions.preview_card')),

                Actions\Action::make('duplicate')
                    ->label(__('filament.business_cards.actions.duplicate'))
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (BusinessCard $record) {
                        $newCard = $record->replicate();

                        $newCard->custom_slug = null;
                        $newCard->share_url = null;
                        $newCard->is_primary = false;
                        $newCard->is_published = false;
                        $newCard->views_count = 0;
                        $newCard->shares_count = 0;
                        $newCard->save();

                        // Duplicate sections
                        foreach ($record->sections as $section) {
                            $newSection = $section->replicate();
                            $newSection->business_card_id = $newCard->id;
                            $newSection->save();
                        }
                    })
                    ->requiresConfirmation()
                    ->tooltip(__('filament.business_cards.actions.duplicate_card')),

                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\BulkAction::make('publish')
                        ->label(__('filament.business_cards.actions.publish'))
                        ->icon('heroicon-o-eye')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_published' => true])))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),

                    Actions\BulkAction::make('unpublish')
                        ->label(__('filament.business_cards.actions.unpublish'))
                        ->icon('heroicon-o-eye-slash')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['is_published' => false])))
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
