<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Payment;
use App\Models\User;

class CashPaymentGateway implements PaymentGatewayInterface
{
    public function createPayment(User $user, float $amount, array $data = []): Payment
    {
        return Payment::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $data['subscription_plan_id'] ?? null,
            'amount' => $amount,
            'currency' => $data['currency'] ?? 'USD',
            'payment_method' => 'cash',
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
            'metadata' => $data['metadata'] ?? null,
        ]);
    }

    public function processPayment(Payment $payment): bool
    {
        return true;
    }

    public function confirmPayment(Payment $payment, array $confirmationData = []): bool
    {
        $payment->update([
            'status' => 'completed',
            'paid_at' => now(),
            'notes' => $confirmationData['notes'] ?? $payment->notes,
            'metadata' => array_merge(
                $payment->metadata ?? [],
                [
                    'confirmed_by' => $confirmationData['confirmed_by'] ?? null,
                    'confirmation_date' => now()->toISOString(),
                    'receipt_number' => $confirmationData['receipt_number'] ?? null,
                ]
            ),
        ]);

        return true;
    }

    public function refundPayment(Payment $payment, ?float $amount = null): bool
    {
        $refundAmount = $amount ?? $payment->amount;

        $payment->update([
            'status' => 'refunded',
            'metadata' => array_merge(
                $payment->metadata ?? [],
                [
                    'refund_amount' => $refundAmount,
                    'refund_date' => now()->toISOString(),
                ]
            ),
        ]);

        return true;
    }

    public function getPaymentStatus(Payment $payment): string
    {
        return $payment->status;
    }

    public function supportsRecurring(): bool
    {
        return false;
    }

    public function getGatewayName(): string
    {
        return 'cash';
    }
}
