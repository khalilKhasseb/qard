<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserSubscriptionResource\Pages;
use App\Models\UserSubscription;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserSubscriptionResource extends Resource
{
    protected static ?string $model = UserSubscription::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-queue-list';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return __('filament.user_subscriptions.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.user_subscriptions.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.user_subscriptions.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.user_subscriptions.sections.subscription_details'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.user_subscriptions.fields.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('subscription_plan_id')
                            ->label(__('filament.user_subscriptions.fields.plan'))
                            ->relationship('subscriptionPlan', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label(__('filament.user_subscriptions.fields.status'))
                            ->options([
                                'active' => __('filament.user_subscriptions.statuses.active'),
                                'pending' => __('filament.user_subscriptions.statuses.pending'),
                                'canceled' => __('filament.user_subscriptions.statuses.canceled'),
                                'expired' => __('filament.user_subscriptions.statuses.expired'),
                            ])
                            ->default('active')
                            ->required(),
                    ])->columns(3),

                Schemas\Components\Section::make(__('filament.user_subscriptions.sections.dates'))
                    ->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label(__('filament.user_subscriptions.fields.starts_at'))
                            ->required(),
                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label(__('filament.user_subscriptions.fields.ends_at'))
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label(__('filament.user_subscriptions.fields.trial_ends_at'))
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('canceled_at')
                            ->label(__('filament.user_subscriptions.fields.canceled_at'))
                            ->nullable(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.user_subscriptions.sections.settings'))
                    ->schema([
                        Forms\Components\Toggle::make('auto_renew')
                            ->label(__('filament.user_subscriptions.fields.auto_renew'))
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.user_subscriptions.fields.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label(__('filament.user_subscriptions.fields.plan'))
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label(__('filament.user_subscriptions.fields.status'))
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => ['canceled', 'expired'],
                    ]),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label(__('filament.user_subscriptions.fields.starts_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->label(__('filament.user_subscriptions.fields.ends_at'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_renew')
                    ->label(__('filament.user_subscriptions.fields.auto_renew'))
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament.user_subscriptions.fields.status'))
                    ->options([
                        'active' => __('filament.user_subscriptions.statuses.active'),
                        'pending' => __('filament.user_subscriptions.statuses.pending'),
                        'canceled' => __('filament.user_subscriptions.statuses.canceled'),
                        'expired' => __('filament.user_subscriptions.statuses.expired'),
                    ]),
                Tables\Filters\SelectFilter::make('user')
                    ->label(__('filament.user_subscriptions.fields.user'))
                    ->relationship('user', 'name'),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserSubscriptions::route('/'),
            'create' => Pages\CreateUserSubscription::route('/create'),
            'edit' => Pages\EditUserSubscription::route('/{record}/edit'),
        ];
    }
}
