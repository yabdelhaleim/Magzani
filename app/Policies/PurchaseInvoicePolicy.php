<?php

namespace App\Policies;

use App\Models\PurchaseInvoice;
use App\Models\User;

class PurchaseInvoicePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('purchases.view');
    }

    public function view(User $user, PurchaseInvoice $invoice): bool
    {
        return $user->hasPermission('purchases.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('purchases.create');
    }

    public function update(User $user, PurchaseInvoice $invoice): bool
    {
        if ($invoice->status === 'cancelled') {
            return false;
        }
        return $user->hasPermission('purchases.edit');
    }

    public function delete(User $user, PurchaseInvoice $invoice): bool
    {
        return $user->hasPermission('purchases.cancel');
    }
}