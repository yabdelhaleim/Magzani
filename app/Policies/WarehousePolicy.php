<?php

namespace App\Policies;

use App\Models\Warehouse;
use App\Models\User;

class WarehousePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('warehouses.create');
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.edit');
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasPermission('warehouses.delete');
    }
}