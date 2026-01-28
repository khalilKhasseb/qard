<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
// use Filament\Tables\Actions\Action;
use Filament\Widgets\TableWidget as BaseWidget;

class UnverifiedUsersTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Unverified Users')
            ->description('Users who need manual email verification')
            ->query(
                User::query()->whereNull('email_verified_at')->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Registered')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (User $record) {
                        $record->update(['email_verified_at' => now()]);

                        \Filament\Notifications\Notification::make()
                            ->title('User verified')
                            ->body("Email for {$record->name} has been verified.")
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Verify User Email')
                    ->modalDescription(fn (User $record) => "Verify email address for {$record->name}?"),
                Action::make('view')
                    ->url(fn (User $record): string => \App\Filament\Resources\UserResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('All users are verified!')
            ->emptyStateDescription('There are no unverified users at the moment.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
