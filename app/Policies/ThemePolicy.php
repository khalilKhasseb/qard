<?php

namespace App\Policies;

use App\Models\Theme;
use App\Models\User;

class ThemePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Theme $theme): bool
    {
        return $theme->is_public
            || $theme->is_system_default
            || $user->id === $theme->user_id;
    }

    public function create(User $user): bool
    {
        return $user->canCreateTheme();
    }

    public function update(User $user, Theme $theme): bool
    {
        return ! $theme->is_system_default && $user->id === $theme->user_id;
    }

    public function delete(User $user, Theme $theme): bool
    {
        return ! $theme->is_system_default && $user->id === $theme->user_id;
    }

    public function duplicate(User $user, Theme $theme): bool
    {
        return $this->view($user, $theme) && $user->canCreateTheme();
    }

    public function restore(User $user, Theme $theme): bool
    {
        return $user->id === $theme->user_id;
    }

    public function forceDelete(User $user, Theme $theme): bool
    {
        return ! $theme->is_system_default && $user->id === $theme->user_id;
    }
}
