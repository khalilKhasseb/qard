<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\SubscriptionsRelationManager;
use App\Models\User;
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

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_admin')
                            ->label('Administrator')
                            ->helperText('Administrators have full access to the admin panel')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->helperText('Set this to manually verify the user\'s email'),
                    ])->columns(2),

                Schemas\Components\Section::make('Subscription')
                    ->schema([
                        Forms\Components\Select::make('subscription_tier')
                            ->options([
                                'free' => 'Free',
                                'pro' => 'Pro',
                                'business' => 'Business',
                            ])
                            ->default('free')
                            ->required(),
                        Forms\Components\Select::make('subscription_status')
                            ->options([
                                'pending' => 'Pending',
                                'active' => 'Active',
                                'canceled' => 'Canceled',
                                'expired' => 'Expired',
                            ])
                            ->default('pending')
                            ->required(),
                        Forms\Components\DateTimePicker::make('subscription_expires_at')
                            ->nullable(),
                    ])->columns(3),

                Schemas\Components\Section::make('Preferences')
                    ->schema([
                        Forms\Components\Select::make('language')
                            ->options([
                                'en' => 'English',
                                'ar' => 'Arabic',
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
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_admin')
                    ->boolean()
                    ->label('Admin')
                    ->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                Tables\Columns\TextColumn::make('subscription_tier')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free' => 'gray',
                        'pro' => 'info',
                        'business' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subscription_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success',
                        'canceled', 'expired' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('cards_count')
                    ->counts('cards')
                    ->label('Cards'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('last_login')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('unverified')
                    ->label('Unverified Users')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereNull('email_verified_at')),
                Tables\Filters\Filter::make('verified')
                    ->label('Verified Users')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder => $query->whereNotNull('email_verified_at')),
                Tables\Filters\SelectFilter::make('subscription_tier')
                    ->options([
                        'free' => 'Free',
                        'pro' => 'Pro',
                        'business' => 'Business',
                    ]),
                Tables\Filters\SelectFilter::make('subscription_status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'canceled' => 'Canceled',
                        'expired' => 'Expired',
                    ]),
            ])
            ->recordActions([
                  \Filament\Actions\Action::make('verify')
                    ->label('Verify Email')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => is_null($record->email_verified_at))
                    ->action(function (User $record) {
                        $record->update(['email_verified_at' => now()]);
                        \Filament\Notifications\Notification::make()
                            ->title('User verified successfully')
                            ->body("Email for {$record->name} has been verified.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                \Filament\Actions\Action::make('unverify')
                    ->label('Unverify Email')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (User $record): bool => !is_null($record->email_verified_at))
                    ->action(function (User $record) {
                        $record->update(['email_verified_at' => null]);
                        \Filament\Notifications\Notification::make()
                            ->title('User unverified')
                            ->body("Email verification for {$record->name} has been removed.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
            ])
            ->toolbarActions([
                 \Filament\Actions\BulkActionGroup::make([
                   \Filament\Actions\BulkAction::make('verify_selected')
                        ->label('Verify Selected')
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
                                ->title('Users verified')
                                ->body("{$count} users have been verified.")
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
