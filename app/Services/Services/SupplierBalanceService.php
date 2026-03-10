<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierBalanceService
{
    /**
     * حساب رصيد المورد بشكل دقيق
     * 
     * @param Supplier $supplier
     * @return array
     */
    public function calculate(Supplier $supplier): array
    {
        // إجمالي المشتريات (مدين)
        $totalPurchases = $supplier->purchaseInvoices()
            ->where('status', '!=', 'cancelled') // استبعاد الفواتير الملغاة
            ->sum('total');

        // إجمالي المدفوعات (دائن)
        $totalPaid = $supplier->payments()
            ->sum('amount');

        // الرصيد = المشتريات - المدفوعات
        $balance = $totalPurchases - $totalPaid;

        return [
            'total_purchases' => round($totalPurchases, 2),
            'total_paid' => round($totalPaid, 2),
            'balance' => round($balance, 2)
        ];
    }

    /**
     * تحديث رصيد المورد في قاعدة البيانات
     * 
     * @param Supplier $supplier
     * @return bool
     */
    public function updateBalance(Supplier $supplier): bool
    {
        $calculation = $this->calculate($supplier);
        
        return $supplier->update([
            'balance' => $calculation['balance']
        ]);
    }

    /**
     * الحصول على المورد مع رصيده المحدث
     * 
     * @param Supplier $supplier
     * @return Supplier
     */
    public function getSupplierWithBalance(Supplier $supplier): Supplier
    {
        $this->updateBalance($supplier);
        
        return $supplier->fresh();
    }

    /**
     * حساب رصيد المورد في فترة زمنية محددة
     * 
     * @param Supplier $supplier
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return array
     */
    public function calculateForPeriod(Supplier $supplier, ?string $fromDate = null, ?string $toDate = null): array
    {
        $purchasesQuery = $supplier->purchaseInvoices()
            ->where('status', '!=', 'cancelled');

        $paymentsQuery = $supplier->payments();

        // تطبيق الفترة الزمنية
        if ($fromDate) {
            $purchasesQuery->whereDate('invoice_date', '>=', $fromDate);
            $paymentsQuery->whereDate('payment_date', '>=', $fromDate);
        }

        if ($toDate) {
            $purchasesQuery->whereDate('invoice_date', '<=', $toDate);
            $paymentsQuery->whereDate('payment_date', '<=', $toDate);
        }

        $totalPurchases = $purchasesQuery->sum('total');
        $totalPaid = $paymentsQuery->sum('amount');
        $balance = $totalPurchases - $totalPaid;

        return [
            'total_purchases' => round($totalPurchases, 2),
            'total_paid' => round($totalPaid, 2),
            'balance' => round($balance, 2),
            'from_date' => $fromDate,
            'to_date' => $toDate
        ];
    }
}