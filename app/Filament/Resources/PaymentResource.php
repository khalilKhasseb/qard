<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use App\Services\PaymentService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static string|\UnitEnum|null $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make('Payment Details')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('subscription_plan_id')
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('currency')
                            ->options([
                                'USD' => 'USD',
                                'ILS' => 'ILS',
                                'JOD' => 'JOD',
                            ])
                            ->default(config('payments.lahza.currency', 'USD'))
                            ->required(),
                    ])->columns(2),

                Schemas\Components\Section::make('Payment Method')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->options([
                                'cash' => 'Cash',
                                'lahza' => 'Lahza Gateway',
                                'bank_transfer' => 'Bank Transfer',
                            ])
                            ->default('lahza')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'completed' => 'Completed',
                                'failed' => 'Failed',
                                'refunded' => 'Refunded',
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Schemas\Components\Section::make('Transaction Information')
                    ->schema([
                        Forms\Components\TextInput::make('transaction_id')
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable(),
                        Forms\Components\TextInput::make('gateway_reference')
                            ->label('Lahza Reference')
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable()
                            ->nullable(),
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->nullable(),
                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->nullable()
                            ->deletable()
                            ->addable()
                            ->keyLabel('Key')
                            ->valueLabel('Value'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('amount')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'lahza' => 'primary',
                        'bank_transfer' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Not paid'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'lahza' => 'Lahza Gateway',
                        'bank_transfer' => 'Bank Transfer',
                    ]),
            ])
            ->actions([
                Actions\Action::make('verify_with_lahza')
                    ->label('Verify')
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->visible(fn (Payment $record): bool => $record->isPending() && $record->payment_method === 'lahza')
                    ->requiresConfirmation()
                    ->modalHeading('Verify Payment with Lahza')
                    ->modalDescription('This will verify the payment status with Lahza and activate the subscription if successful.')
                    ->action(function (Payment $record) {
                        $paymentService = new PaymentService;

                        try {
                            $paymentService->confirmPaymentAndActivateSubscription($record, [
                                'notes' => 'Verified manually via admin panel',
                                'confirmed_by' => auth()->user()->name,
                            ]);

                            Notification::make()
                                ->title('Payment verified and subscription activated')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Verification failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Actions\Action::make('confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record): bool => $record->isPending() && $record->payment_method !== 'lahza')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Payment')
                    ->modalDescription('Are you sure you want to confirm this payment? This will activate the user\'s subscription.')
                    ->action(function (Payment $record) {
                        $paymentService = new PaymentService;
                        $paymentService->confirmPaymentAndActivateSubscription($record, [
                            'confirmed_by' => auth()->user()->name,
                        ]);

                        Notification::make()
                            ->title('Payment confirmed')
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Payment $record): bool => $record->isCompleted())
                    ->requiresConfirmation()
                    ->modalHeading('Refund Payment')
                    ->modalDescription('This will refund the payment and cancel the user\'s subscription.')
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Refund Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(fn (Payment $record) => $record->amount)
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason for Refund')
                            ->required(),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $paymentService = new PaymentService;

                        try {
                            if ($record->payment_method === 'lahza') {
                                // Use Lahza refund endpoint
                                $lahzaGateway = new \App\Services\LahzaPaymentGateway;
                                $lahzaGateway->refundPayment($record, $data['amount']);
                            }

                            $paymentService->refundAndCancelSubscription($record, $data['amount']);

                            Notification::make()
                                ->title('Payment refunded')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Refund failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'view' => Pages\ViewPayment::route('/{record}'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
