<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestPayments extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function getSort(): int
    {
        return 5;
    }

    public function getHeading(): string
    {
        return __('filament.widgets.latest_payments.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Payment::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label(__('filament.widgets.latest_payments.transaction'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.widgets.latest_payments.user')),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label(__('filament.widgets.latest_payments.plan'))
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('filament.widgets.latest_payments.amount'))
                    ->money(fn ($record) => $record->currency),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.widgets.latest_payments.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.widgets.latest_payments.date'))
                    ->dateTime(),
            ])
            ->paginated(false);
    }
}
