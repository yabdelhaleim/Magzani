<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view settings.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view settings
        return true;
    }

    /**
     * Determine if the user can update company settings.
     */
    public function updateCompany(User $user): bool
    {
        // Only users with 'admin' role can update company settings
        return $user->roles()->where('name', 'admin')->exists() ||
               $user->roles()->where('name', 'manager')->exists();
    }

    /**
     * Determine if the user can update system settings.
     */
    public function updateSystem(User $user): bool
    {
        // Only users with 'admin' role can update system settings
        return $user->roles()->where('name', 'admin')->exists();
    }

    /**
     * Determine if the user can manage users.
     */
    public function manageUsers(User $user): bool
    {
        // Only users with 'admin' role can manage users
        return $user->roles()->where('name', 'admin')->exists();
    }

    /**
     * Determine if the user can perform backup operations.
     */
    public function manageBackup(User $user): bool
    {
        // Only users with 'admin' role can manage backups
        return $user->roles()->where('name', 'admin')->exists();
    }
}
