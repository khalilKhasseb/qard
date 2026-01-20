<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SubscriptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'subscriptions';

    protected static ?string $recordTitleAttribute = 'status';

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Forms\Components\Select::make('subscription_plan_id')
                    ->relationship('subscriptionPlan', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'pending' => 'Pending',
                        'canceled' => 'Canceled',
                        'expired' => 'Expired',
                    ])
                    ->default('active')
                    ->required(),
                Forms\Components\DateTimePicker::make('starts_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('ends_at')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('trial_ends_at')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('canceled_at')
                    ->nullable(),
                Forms\Components\Toggle::make('auto_renew')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('subscriptionPlan.name')
                    ->label('Plan')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'pending',
                        'danger' => ['canceled', 'expired'],
                    ]),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\IconColumn::make('auto_renew')
                    ->boolean(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Subscription'),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
