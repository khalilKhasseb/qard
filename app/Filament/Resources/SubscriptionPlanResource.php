<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return __('filament.subscription_plans.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.subscription_plans.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.subscription_plans.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.subscription_plans.sections.plan_details'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.subscription_plans.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('filament.subscription_plans.fields.slug'))
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label(__('filament.subscription_plans.fields.description'))
                            ->rows(3)
                            ->nullable(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.subscription_plans.sections.pricing'))
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label(__('filament.subscription_plans.fields.price'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('billing_cycle')
                            ->label(__('filament.subscription_plans.fields.billing_cycle'))
                            ->options([
                                'monthly' => __('filament.subscription_plans.billing_cycles.monthly'),
                                'yearly' => __('filament.subscription_plans.billing_cycles.yearly'),
                                'lifetime' => __('filament.subscription_plans.billing_cycles.lifetime'),
                            ])
                            ->default('monthly')
                            ->required(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.subscription_plans.sections.limits'))
                    ->schema([
                        Forms\Components\TextInput::make('cards_limit')
                            ->label(__('filament.subscription_plans.fields.cards_limit'))
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('themes_limit')
                            ->label(__('filament.subscription_plans.fields.themes_limit'))
                            ->numeric()
                            ->default(1)
                            ->required(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.subscription_plans.sections.ai_translation'))
                    ->schema([
                        Forms\Components\TextInput::make('translation_credits_monthly')
                            ->label(__('filament.subscription_plans.fields.translation_credits_monthly'))
                            ->numeric()
                            ->default(10)
                            ->required()
                            ->helperText(__('filament.subscription_plans.fields.translation_credits_helper')),
                        Forms\Components\Toggle::make('unlimited_translations')
                            ->label(__('filament.subscription_plans.fields.unlimited_translations'))
                            ->default(false),
                        Forms\Components\TextInput::make('per_credit_cost')
                            ->label(__('filament.subscription_plans.fields.per_credit_cost'))
                            ->numeric()
                            ->default(0.01)
                            ->step(0.0001)
                            ->prefix('$')
                            ->required()
                            ->helperText(__('filament.subscription_plans.fields.per_credit_helper')),
                    ])->columns(3),

                Schemas\Components\Section::make(__('filament.subscription_plans.sections.features'))
                    ->schema([
                        Forms\Components\Toggle::make('custom_css_allowed')
                            ->label(__('filament.subscription_plans.fields.custom_css_allowed')),
                        Forms\Components\Toggle::make('analytics_enabled')
                            ->label(__('filament.subscription_plans.fields.analytics_enabled')),
                        Forms\Components\Toggle::make('nfc_enabled')
                            ->label(__('filament.subscription_plans.fields.nfc_enabled')),
                        Forms\Components\Toggle::make('custom_domain_allowed')
                            ->label(__('filament.subscription_plans.fields.custom_domain_allowed')),
                        Forms\Components\Toggle::make('is_active')
                            ->label(__('filament.subscription_plans.fields.is_active'))
                            ->default(true),
                    ])->columns(3),

                Schemas\Components\Section::make(__('filament.subscription_plans.sections.additional_features'))
                    ->schema([
                        Forms\Components\KeyValue::make('features')
                            ->label(__('filament.subscription_plans.fields.features'))
                            ->keyLabel(__('filament.subscription_plans.fields.feature'))
                            ->valueLabel(__('filament.common.value'))
                            ->addButtonLabel(__('filament.subscription_plans.fields.add_feature'))
                            ->nullable()
                            ->deletable(true)
                            ->reorderable(false)
                            ->default([]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.subscription_plans.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label(__('filament.subscription_plans.fields.slug'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('filament.subscription_plans.fields.price'))
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label(__('filament.subscription_plans.fields.billing_cycle'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'monthly' => 'info',
                        'yearly' => 'success',
                        'lifetime' => 'primary',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cards_limit')
                    ->label(__('filament.business_cards.fields.views'))
                    ->numeric()
                    ->label(__('filament.subscription_plans.fields.cards_limit')),
                Tables\Columns\TextColumn::make('themes_limit')
                    ->label(__('filament.subscription_plans.fields.themes_limit'))
                    ->numeric(),
                Tables\Columns\TextColumn::make('translation_credits_monthly')
                    ->label(__('filament.subscription_plans.fields.ai_credits'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('unlimited_translations')
                    ->label(__('filament.subscription_plans.fields.unlimited_ai'))
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('filament.subscription_plans.fields.is_active'))
                    ->boolean(),
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label(__('filament.subscription_plans.fields.subscribers')),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('filament.subscription_plans.fields.is_active')),
                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->label(__('filament.subscription_plans.fields.billing_cycle'))
                    ->options([
                        'monthly' => __('filament.subscription_plans.billing_cycles.monthly'),
                        'yearly' => __('filament.subscription_plans.billing_cycles.yearly'),
                        'lifetime' => __('filament.subscription_plans.billing_cycles.lifetime'),
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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'view' => Pages\ViewSubscriptionPlan::route('/{record}'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}
