<?php

namespace App\Policies;

use App\Models\BusinessCard;
use App\Models\User;

class BusinessCardPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, BusinessCard $businessCard): bool
    {
        return $user->id === $businessCard->user_id;
    }

    public function create(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canCreateCard();
    }

    public function update(User $user, BusinessCard $businessCard): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $businessCard->user_id;
    }

    public function delete(User $user, BusinessCard $businessCard): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $businessCard->user_id;
    }

    public function restore(User $user, BusinessCard $businessCard): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $businessCard->user_id;
    }

    public function forceDelete(User $user, BusinessCard $businessCard): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $businessCard->user_id;
    }
}
