<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\AddonsRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\Addon;
use App\Models\User;
use App\Services\AddonService;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.user_management');
    }

    public static function getModelLabel(): string
    {
        return __('filament.users.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.users.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.users.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.users.sections.user_information'))
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('filament.users.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label(__('filament.users.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->label(__('filament.users.fields.password'))
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_admin')
                            ->label(__('filament.users.fields.is_admin'))
                            ->helperText(__('filament.users.fields.admin_helper'))
                            ->default(false),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label(__('filament.users.fields.email_verified_at'))
                            ->helperText(__('filament.users.fields.email_verified_helper')),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.users.sections.subscription'))
                    ->schema([
                        Forms\Components\Select::make('subscription_tier')
                            ->label(__('filament.users.fields.subscription_tier'))
                            ->options([
                                'free' => __('filament.users.tiers.free'),
                                'pro' => __('filament.users.tiers.pro'),
                                'business' => __('filament.users.tiers.business'),
                            ])
                            ->default('free')
                            ->required(),
                        Forms\Components\Select::make('subscription_status')
                            ->label(__('filament.users.fields.subscription_status'))
                            ->options([
                                'pending' => __('filament.users.statuses.pending'),
                                'active' => __('filament.users.statuses.active'),
                                'canceled' => __('filament.users.statuses.canceled'),
                                'expired' => __('filament.users.statuses.expired'),
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DateTimePicker::make('subscription_expires_at')
                            ->label(__('filament.users.fields.subscription_expires_at'))
                            ->nullable(),
                    ])->columns(3),

                Schemas\Components\Section::make(__('filament.users.sections.preferences'))
                    ->schema([
                        Forms\Components\Select::make('language')
                            ->label(__('filament.users.fields.language'))
                            ->options([
                                'en' => 'English',
                                'ar' => 'العربية',
                            ])
                            ->default('en'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament.users.fields.name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('filament.users.fields.email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean()
                    ->label(__('filament.users.fields.is_admin'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(__('filament.common.verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription_tier')
                    ->label(__('filament.users.fields.subscription_tier'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free' => 'gray',
                        'pro' => 'info',
                        'business' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subscription_status')
                    ->label(__('filament.users.fields.subscription_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'canceled', 'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cards_count')
                    ->counts('cards')
                    ->label(__('filament.users.fields.cards_count')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_login')
                    ->label(__('filament.users.fields.last_login'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('unverified')
                    ->label(__('filament.users.filters.unverified'))
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereNull('email_verified_at')),
                Tables\Filters\Filter::make('verified')
                    ->label(__('filament.users.filters.verified'))
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\SelectFilter::make('subscription_tier')
                    ->label(__('filament.users.fields.subscription_tier'))
                    ->options([
                        'free' => __('filament.users.tiers.free'),
                        'pro' => __('filament.users.tiers.pro'),
                        'business' => __('filament.users.tiers.business'),
                    ]),
                Tables\Filters\SelectFilter::make('subscription_status')
                    ->label(__('filament.users.fields.subscription_status'))
                    ->options([
                        'pending' => __('filament.users.statuses.pending'),
                        'active' => __('filament.users.statuses.active'),
                        'canceled' => __('filament.users.statuses.canceled'),
                        'expired' => __('filament.users.statuses.expired'),
                    ]),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('verify')
                    ->label(__('filament.users.actions.verify'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => is_null($record->email_verified_at))
                    ->action(function (User $record) {
                        $record->email_verified_at = now();
                        $record->save();
                        \Filament\Notifications\Notification::make()
                            ->title(__('filament.users.notifications.verified'))
                            ->body(__('filament.users.notifications.verified_body', ['name' => $record->name]))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                \Filament\Actions\Action::make('unverify')
                    ->label(__('filament.users.actions.unverify'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => ! is_null($record->email_verified_at))
                    ->action(function (User $record) {
                        $record->email_verified_at = null;
                        $record->save();
                        \Filament\Notifications\Notification::make()
                            ->title(__('filament.users.notifications.unverified'))
                            ->body(__('filament.users.notifications.unverified_body', ['name' => $record->name]))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                \Filament\Actions\Action::make('grant_addon')
                    ->label(__('filament.users.actions.grant_addon'))
                    ->icon('heroicon-o-gift')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('addon_id')
                            ->label(__('filament.addons.label'))
                            ->options(Addon::query()->active()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('filament.common.notes'))
                            ->rows(2)
                            ->nullable(),
                    ])
                    ->action(function (User $record, array $data) {
                        $addon = Addon::findOrFail($data['addon_id']);
                        app(AddonService::class)->grantAddon($record, $addon, $data['notes'] ?? null);

                        \Filament\Notifications\Notification::make()
                            ->title(__('filament.users.notifications.addon_granted'))
                            ->body(__('filament.users.notifications.addon_granted_body', [
                                'addon' => $addon->name,
                                'user' => $record->name,
                            ]))
                            ->success()
                            ->send();
                    }),
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('verify_selected')
                        ->label(__('filament.users.actions.verify_selected'))
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            $count = $records->whereNull('email_verified_at')->count();

                            foreach ($records as $record) {
                                if (is_null($record->email_verified_at)) {
                                    $record->update(['email_verified_at' => now()]);
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title(__('filament.users.notifications.bulk_verified'))
                                ->body(__('filament.users.notifications.bulk_verified_body', ['count' => $count]))
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SubscriptionsRelationManager::class,
            AddonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNull('email_verified_at')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $unverifiedCount = static::getModel()::whereNull('email_verified_at')->count();

        return $unverifiedCount > 0 ? 'warning' : 'success';
    }
}
