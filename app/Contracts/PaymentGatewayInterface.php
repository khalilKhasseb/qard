<?php

namespace App\Contracts;

use App\Models\Payment;
use App\Models\User;

interface PaymentGatewayInterface
{
    public function createPayment(User $user, float $amount, array $data = []): Payment;

    public function processPayment(Payment $payment): bool;

    public function confirmPayment(Payment $payment, array $confirmationData = []): bool;

    public function refundPayment(Payment $payment, ?float $amount = null): bool;

    public function getPaymentStatus(Payment $payment): string;

    public function supportsRecurring(): bool;

    public function getGatewayName(): string;
}
