<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPayments extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Latest Payments';

    public static function getSort(): int
    {
        return 5;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label('Transaction')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User'),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Date'),
            ])
            ->paginated(false);
    }
}
