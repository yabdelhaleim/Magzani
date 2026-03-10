<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Collection;

class SupplierStatementService
{
    /**
     * جلب كشف حساب المورد مع جميع الحركات
     * 
     * @param Supplier $supplier
     * @param array $filters
     * @return Collection
     */
    public function get(Supplier $supplier, array $filters = []): Collection
    {
        // جلب فواتير الشراء
        $purchasesQuery = $supplier->purchaseInvoices()
            ->where('status', '!=', 'cancelled')
            ->select(
                'id',
                'invoice_date as date',
                'total as debit',
                \DB::raw('0 as credit'),
                \DB::raw("'فاتورة شراء' as type"),
                'notes'
            );

        // جلب المدفوعات
        $paymentsQuery = $supplier->payments()
            ->select(
                'id',
                'payment_date as date',
                \DB::raw('0 as debit'),
                'amount as credit',
                \DB::raw("CONCAT('سداد - ', IFNULL(method, 'نقدي')) as type"),
                'notes'
            );

        // تطبيق الفلاتر
        if (isset($filters['from_date'])) {
            $purchasesQuery->whereDate('invoice_date', '>=', $filters['from_date']);
            $paymentsQuery->whereDate('payment_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $purchasesQuery->whereDate('invoice_date', '<=', $filters['to_date']);
            $paymentsQuery->whereDate('payment_date', '<=', $filters['to_date']);
        }

        // تطبيق فلتر النوع
        $purchases = collect([]);
        $payments = collect([]);

        if (!isset($filters['type']) || $filters['type'] === 'invoice' || $filters['type'] === '') {
            $purchases = $purchasesQuery->get();
        }

        if (!isset($filters['type']) || $filters['type'] === 'payment' || $filters['type'] === '') {
            $payments = $paymentsQuery->get();
        }

        // دمج النتائج وترتيبها حسب التاريخ
        return $purchases->merge($payments)
            ->sortBy('date')
            ->values();
    }

    /**
     * جلب كشف حساب مع الرصيد الجاري
     * 
     * @param Supplier $supplier
     * @param array $filters
     * @return array
     */
    public function getWithRunningBalance(Supplier $supplier, array $filters = []): array
    {
        $statement = $this->get($supplier, $filters);
        
        $runningBalance = 0;
        $processedStatement = $statement->map(function ($transaction) use (&$runningBalance) {
            $runningBalance += $transaction->debit - $transaction->credit;
            $transaction->running_balance = $runningBalance;
            return $transaction;
        });

        return [
            'statement' => $processedStatement,
            'final_balance' => $runningBalance
        ];
    }

    /**
     * تصدير كشف الحساب إلى PDF
     * 
     * @param Supplier $supplier
     * @param array $filters
     * @return mixed
     */
    public function exportToPdf(Supplier $supplier, array $filters = [])
    {
        $data = $this->getWithRunningBalance($supplier, $filters);
        
        // يمكن استخدام مكتبة مثل DomPDF أو TCPDF هنا
        // return PDF::loadView('suppliers.statement-pdf', [
        //     'supplier' => $supplier,
        //     'statement' => $data['statement'],
        //     'finalBalance' => $data['final_balance']
        // ])->download('statement-' . $supplier->id . '.pdf');
        
        // للتنفيذ المستقبلي
        throw new \Exception('ميزة التصدير إلى PDF قيد التطوير');
    }

    /**
     * تصدير كشف الحساب إلى Excel
     * 
     * @param Supplier $supplier
     * @param array $filters
     * @return mixed
     */
    public function exportToExcel(Supplier $supplier, array $filters = [])
    {
        // يمكن استخدام مكتبة مثل Laravel Excel هنا
        // return Excel::download(
        //     new SupplierStatementExport($supplier, $filters),
        //     'statement-' . $supplier->id . '.xlsx'
        // );
        
        // للتنفيذ المستقبلي
        throw new \Exception('ميزة التصدير إلى Excel قيد التطوير');
    }
}