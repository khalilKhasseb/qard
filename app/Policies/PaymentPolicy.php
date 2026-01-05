<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $user->id === $payment->user_id;
    }

    /**
     * Determine whether the user can create payments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the payment.
     * Only admins can update payments (status changes, confirmation).
     */
    public function update(User $user, Payment $payment): bool
    {
        return false; // Payments are immutable for users
    }

    /**
     * Determine whether the user can delete the payment.
     * Payments should not be deleted, only marked as failed/cancelled.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the payment.
     */
    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the payment.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }
}
