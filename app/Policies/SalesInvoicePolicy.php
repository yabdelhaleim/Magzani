<?php

namespace App\Policies;

use App\Models\SalesInvoice;
use App\Models\User;

/**
 * صلاحيات فواتير المبيعات.
 *
 * المدير (admin): كل الصلاحيات.
 * الموظف (employee): يمكنه العرض فقط.
 */
class SalesInvoicePolicy
{
    /**
     * قبل كل check: المدير يتجاوز كل الصلاحيات.
     */
    public function before(User $user, $ability)
    {
        if ($user->isAdmin()) {
            return true;
        }
    }

    /**
     * عرض قائمة الفواتير
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('invoices.view');
    }

    /**
     * عرض فاتورة محددة
     */
    public function view(User $user, SalesInvoice $invoice): bool
    {
        return $user->hasPermission('invoices.view');
    }

    /**
     * إنشاء فاتورة جديدة
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('invoices.create');
    }

    /**
     * تعديل فاتورة
     */
    public function update(User $user, SalesInvoice $invoice): bool
    {
        // لا يمكن تعديل فاتورة ملغاة
        if ($invoice->status === 'cancelled') {
            return false;
        }

        return $user->hasPermission('invoices.edit');
    }

    /**
     * حذف/إلغاء فاتورة
     */
    public function delete(User $user, SalesInvoice $invoice): bool
    {
        return $user->hasPermission('invoices.cancel');
    }

    /**
     * طباعة فاتورة
     */
    public function print(User $user, SalesInvoice $invoice): bool
    {
        return $user->hasPermission('invoices.print');
    }
}