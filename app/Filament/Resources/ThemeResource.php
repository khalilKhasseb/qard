<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThemeResource\Pages;
use App\Models\Theme;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.cards');
    }

    public static function getModelLabel(): string
    {
        return __('filament.themes.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.themes.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.themes.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.themes.sections.theme_details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.themes.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.themes.fields.owner'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\Toggle::make('is_system_default')
                            ->label(__('filament.themes.fields.is_system_default'))
                            ->helperText(__('filament.themes.fields.system_helper')),
                        Forms\Components\Toggle::make('is_public')
                            ->label(__('filament.themes.fields.is_public'))
                            ->helperText(__('filament.themes.fields.public_helper')),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.themes.sections.colors'))
                    ->schema([
                        Forms\Components\ColorPicker::make('config.colors.primary')
                            ->label(__('filament.themes.fields.primary_color')),
                        Forms\Components\ColorPicker::make('config.colors.secondary')
                            ->label(__('filament.themes.fields.secondary_color')),
                        Forms\Components\ColorPicker::make('config.colors.background')
                            ->label(__('filament.themes.fields.background_color')),
                        Forms\Components\ColorPicker::make('config.colors.text')
                            ->label(__('filament.themes.fields.text_color')),
                        Forms\Components\ColorPicker::make('config.colors.card_bg')
                            ->label(__('filament.themes.fields.card_bg')),
                        Forms\Components\ColorPicker::make('config.colors.border')
                            ->label(__('filament.themes.fields.border_color')),
                    ])->columns(3),

                Schemas\Components\Section::make(__('filament.themes.sections.typography'))
                    ->schema([
                        Forms\Components\Select::make('config.fonts.heading')
                            ->label(__('filament.themes.fields.heading_font'))
                            ->options([
                                'Inter' => 'Inter',
                                'Roboto' => 'Roboto',
                                'Open Sans' => 'Open Sans',
                                'Playfair Display' => 'Playfair Display',
                                'Montserrat' => 'Montserrat',
                                'Poppins' => 'Poppins',
                            ])
                            ->default('Inter'),
                        Forms\Components\Select::make('config.fonts.body')
                            ->label(__('filament.themes.fields.body_font'))
                            ->options([
                                'Inter' => 'Inter',
                                'Roboto' => 'Roboto',
                                'Open Sans' => 'Open Sans',
                                'Lato' => 'Lato',
                                'Source Sans Pro' => 'Source Sans Pro',
                            ])
                            ->default('Inter'),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.themes.sections.layout'))
                    ->schema([
                        Forms\Components\Select::make('config.layout.card_style')
                            ->label(__('filament.themes.fields.card_style'))
                            ->options([
                                'elevated' => __('filament.themes.card_styles.elevated'),
                                'outlined' => __('filament.themes.card_styles.outlined'),
                                'filled' => __('filament.themes.card_styles.filled'),
                            ])
                            ->default('elevated'),
                        Forms\Components\TextInput::make('config.layout.border_radius')
                            ->label(__('filament.themes.fields.border_radius'))
                            ->default('12px')
                            ->maxLength(20),
                        Forms\Components\Select::make('config.layout.alignment')
                            ->label(__('filament.themes.fields.alignment'))
                            ->options([
                                'left' => __('filament.themes.alignments.left'),
                                'center' => __('filament.themes.alignments.center'),
                                'right' => __('filament.themes.alignments.right'),
                            ])
                            ->default('center'),
                        Forms\Components\Select::make('config.layout.spacing')
                            ->label(__('filament.themes.fields.spacing'))
                            ->options([
                                'compact' => __('filament.themes.spacings.compact'),
                                'normal' => __('filament.themes.spacings.normal'),
                                'relaxed' => __('filament.themes.spacings.relaxed'),
                            ])
                            ->default('normal'),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.themes.sections.custom_css'))
                    ->schema([
                        Forms\Components\Textarea::make('config.custom_css')
                            ->label(__('filament.themes.fields.custom_css'))
                            ->rows(6)
                            ->placeholder(__('filament.themes.fields.css_placeholder')),
                    ]),

                Schemas\Components\Section::make(__('filament.themes.sections.preview'))
                    ->schema([
                        Forms\Components\FileUpload::make('preview_image')
                            ->label(__('filament.themes.fields.preview_image'))
                            ->image()
                            ->directory('theme-previews')
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.themes.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.themes.fields.owner'))
                    ->placeholder(__('filament.themes.fields.system'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_system_default')
                    ->label(__('filament.themes.fields.system'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label(__('filament.themes.fields.is_public'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('used_by_cards_count')
                    ->label(__('filament.themes.fields.used_by'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_system_default')
                    ->label(__('filament.themes.filters.system_default')),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label(__('filament.themes.filters.public')),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThemes::route('/'),
            'create' => Pages\CreateTheme::route('/create'),
            'view' => Pages\ViewTheme::route('/{record}'),
            'edit' => Pages\EditTheme::route('/{record}/edit'),
        ];
    }
}
