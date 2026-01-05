<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThemeResource\Pages;
use App\Models\Theme;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ThemeResource extends Resource
{
    protected static ?string $model = Theme::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-paint-brush';

    protected static string | \UnitEnum | null $navigationGroup = 'Cards';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Theme Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->label('Owner'),
                        Forms\Components\Toggle::make('is_system_default')
                            ->label('System Default')
                            ->helperText('System themes are available to all users'),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public')
                            ->helperText('Public themes can be used by other users'),
                    ])->columns(2),

                Forms\Components\Section::make('Colors')
                    ->schema([
                        Forms\Components\ColorPicker::make('config.colors.primary')
                            ->label('Primary Color'),
                        Forms\Components\ColorPicker::make('config.colors.secondary')
                            ->label('Secondary Color'),
                        Forms\Components\ColorPicker::make('config.colors.background')
                            ->label('Background Color'),
                        Forms\Components\ColorPicker::make('config.colors.text')
                            ->label('Text Color'),
                        Forms\Components\ColorPicker::make('config.colors.card_bg')
                            ->label('Card Background'),
                        Forms\Components\ColorPicker::make('config.colors.border')
                            ->label('Border Color'),
                    ])->columns(3),

                Forms\Components\Section::make('Typography')
                    ->schema([
                        Forms\Components\Select::make('config.fonts.heading')
                            ->label('Heading Font')
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
                            ->label('Body Font')
                            ->options([
                                'Inter' => 'Inter',
                                'Roboto' => 'Roboto',
                                'Open Sans' => 'Open Sans',
                                'Lato' => 'Lato',
                                'Source Sans Pro' => 'Source Sans Pro',
                            ])
                            ->default('Inter'),
                    ])->columns(2),

                Forms\Components\Section::make('Layout')
                    ->schema([
                        Forms\Components\Select::make('config.layout.card_style')
                            ->label('Card Style')
                            ->options([
                                'elevated' => 'Elevated (Shadow)',
                                'outlined' => 'Outlined (Border)',
                                'filled' => 'Filled (Flat)',
                            ])
                            ->default('elevated'),
                        Forms\Components\TextInput::make('config.layout.border_radius')
                            ->label('Border Radius')
                            ->default('12px')
                            ->maxLength(20),
                        Forms\Components\Select::make('config.layout.alignment')
                            ->label('Text Alignment')
                            ->options([
                                'left' => 'Left',
                                'center' => 'Center',
                                'right' => 'Right',
                            ])
                            ->default('center'),
                        Forms\Components\Select::make('config.layout.spacing')
                            ->label('Spacing')
                            ->options([
                                'compact' => 'Compact',
                                'normal' => 'Normal',
                                'relaxed' => 'Relaxed',
                            ])
                            ->default('normal'),
                    ])->columns(2),

                Forms\Components\Section::make('Custom CSS')
                    ->schema([
                        Forms\Components\Textarea::make('config.custom_css')
                            ->label('Custom CSS')
                            ->rows(6)
                            ->placeholder('/* Add your custom CSS here */'),
                    ]),

                Forms\Components\Section::make('Preview')
                    ->schema([
                        Forms\Components\FileUpload::make('preview_image')
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_system_default')
                    ->boolean()
                    ->label('System'),
                Tables\Columns\IconColumn::make('is_public')
                    ->boolean()
                    ->label('Public'),
                Tables\Columns\TextColumn::make('used_by_cards_count')
                    ->numeric()
                    ->sortable()
                    ->label('Used By'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_system_default')
                    ->label('System Default'),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public'),
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
