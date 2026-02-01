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

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('filament.navigation.groups.finance');
    }

    public static function getModelLabel(): string
    {
        return __('filament.payments.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament.payments.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament.payments.navigation_label');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Schemas\Components\Section::make(__('filament.payments.sections.payment_details'))
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label(__('filament.payments.fields.user'))
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\Select::make('subscription_plan_id')
                            ->label(__('filament.payments.fields.plan'))
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                        Forms\Components\TextInput::make('amount')
                            ->label(__('filament.payments.fields.amount'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\Select::make('currency')
                            ->label(__('filament.payments.fields.currency'))
                            ->options([
                                'USD' => 'USD',
                                'ILS' => 'ILS',
                                'JOD' => 'JOD',
                            ])
                            ->default(config('payments.lahza.currency', 'USD'))
                            ->required(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.payments.sections.payment_method'))
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label(__('filament.payments.fields.payment_method'))
                            ->options([
                                'cash' => __('filament.payments.methods.cash'),
                                'lahza' => __('filament.payments.methods.lahza'),
                                'bank_transfer' => __('filament.payments.methods.bank_transfer'),
                            ])
                            ->default('lahza')
                            ->required(),
                        Forms\Components\Select::make('status')
                            ->label(__('filament.payments.fields.status'))
                            ->options([
                                'pending' => __('filament.payments.statuses.pending'),
                                'completed' => __('filament.payments.statuses.completed'),
                                'failed' => __('filament.payments.statuses.failed'),
                                'refunded' => __('filament.payments.statuses.refunded'),
                            ])
                            ->default('pending')
                            ->required(),
                    ])->columns(2),

                Schemas\Components\Section::make(__('filament.payments.sections.transaction_information'))
                    ->schema([
                        Forms\Components\TextInput::make('transaction_id')
                            ->label(__('filament.payments.fields.transaction_id'))
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable(),
                        Forms\Components\TextInput::make('gateway_reference')
                            ->label(__('filament.payments.fields.gateway_reference'))
                            ->disabled()
                            ->dehydrated(false)
                            ->copyable()
                            ->nullable(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('filament.payments.fields.notes'))
                            ->rows(3)
                            ->nullable(),
                        Forms\Components\DateTimePicker::make('paid_at')
                            ->label(__('filament.payments.fields.paid_at'))
                            ->nullable(),
                        Forms\Components\KeyValue::make('metadata')
                            ->label(__('filament.payments.fields.metadata'))
                            ->nullable()
                            ->deletable()
                            ->addable()
                            ->keyLabel(__('filament.common.key'))
                            ->valueLabel(__('filament.common.value')),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_id')
                    ->label(__('filament.payments.fields.transaction_id'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label(__('filament.payments.fields.user'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan.name')
                    ->label(__('filament.payments.fields.plan'))
                    ->placeholder('N/A'),
                Tables\Columns\TextColumn::make('amount')
                    ->label(__('filament.payments.fields.amount'))
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label(__('filament.payments.fields.payment_method'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'lahza' => 'primary',
                        'bank_transfer' => 'info',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('filament.payments.fields.status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label(__('filament.payments.fields.paid_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('filament.payments.fields.not_paid')),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('filament.common.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('filament.payments.fields.status'))
                    ->options([
                        'pending' => __('filament.payments.statuses.pending'),
                        'completed' => __('filament.payments.statuses.completed'),
                        'failed' => __('filament.payments.statuses.failed'),
                        'refunded' => __('filament.payments.statuses.refunded'),
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label(__('filament.payments.fields.payment_method'))
                    ->options([
                        'cash' => __('filament.payments.methods.cash'),
                        'lahza' => __('filament.payments.methods.lahza'),
                        'bank_transfer' => __('filament.payments.methods.bank_transfer'),
                    ]),
            ])
            ->actions([
                Actions\Action::make('verify_with_lahza')
                    ->label(__('filament.payments.actions.verify'))
                    ->icon('heroicon-o-shield-check')
                    ->color('primary')
                    ->visible(fn (Payment $record): bool => $record->isPending() && $record->payment_method === 'lahza')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.payments.actions.verify_heading'))
                    ->modalDescription(__('filament.payments.actions.verify_description'))
                    ->action(function (Payment $record) {
                        $paymentService = new PaymentService;

                        try {
                            $paymentService->confirmPaymentAndActivateSubscription($record, [
                                'notes' => 'Verified manually via admin panel',
                                'confirmed_by' => auth()->user()->name,
                            ]);

                            Notification::make()
                                ->title(__('filament.payments.notifications.verified'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('filament.payments.notifications.verification_failed'))
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Actions\Action::make('confirm')
                    ->label(__('filament.payments.actions.confirm'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Payment $record): bool => $record->isPending() && $record->payment_method !== 'lahza')
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.payments.actions.confirm_heading'))
                    ->modalDescription(__('filament.payments.actions.confirm_description'))
                    ->action(function (Payment $record) {
                        $paymentService = new PaymentService;
                        $paymentService->confirmPaymentAndActivateSubscription($record, [
                            'confirmed_by' => auth()->user()->name,
                        ]);

                        Notification::make()
                            ->title(__('filament.payments.notifications.confirmed'))
                            ->success()
                            ->send();
                    }),
                Actions\Action::make('refund')
                    ->label(__('filament.payments.actions.refund'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->visible(fn (Payment $record): bool => $record->isCompleted())
                    ->requiresConfirmation()
                    ->modalHeading(__('filament.payments.actions.refund_heading'))
                    ->modalDescription(__('filament.payments.actions.refund_description'))
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label(__('filament.payments.actions.refund_amount'))
                            ->numeric()
                            ->prefix('$')
                            ->default(fn (Payment $record) => $record->amount)
                            ->required(),
                        Forms\Components\Textarea::make('reason')
                            ->label(__('filament.payments.actions.refund_reason'))
                            ->required(),
                    ])
                    ->action(function (Payment $record, array $data) {
                        $paymentService = new PaymentService;

                        try {
                            if ($record->payment_method === 'lahza') {
                                $lahzaGateway = new \App\Services\LahzaPaymentGateway;
                                $lahzaGateway->refundPayment($record, $data['amount']);
                            }

                            $paymentService->refundAndCancelSubscription($record, $data['amount']);

                            Notification::make()
                                ->title(__('filament.payments.notifications.refunded'))
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title(__('filament.payments.notifications.refund_failed'))
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
