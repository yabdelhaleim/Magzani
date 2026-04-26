<?php

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SupplierStatementExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $supplier;
    protected $filters;

    public function __construct(Supplier $supplier, array $filters = [])
    {
        $this->supplier = $supplier;
        $this->filters = $filters;
    }

    /**
     * جلب بيانات الكشف
     */
    public function collection()
    {
        // Purchase invoices
        $invoices = DB::table('purchase_invoices')
            ->where('supplier_id', $this->supplier->id);
        if (!empty($this->filters['date_from'])) {
            $invoices->whereDate('invoice_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $invoices->whereDate('invoice_date', '<=', $this->filters['date_to']);
        }
        $invoices = $invoices->select(
            'id',
            'invoice_number as reference',
            'invoice_date as date',
            'total as amount',
            DB::raw("'purchase' as type"),
            'notes',
            'created_at'
        );

        // Payments
        $payments = DB::table('supplier_payments')
            ->where('supplier_id', $this->supplier->id);
        if (!empty($this->filters['date_from'])) {
            $payments->whereDate('payment_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $payments->whereDate('payment_date', '<=', $this->filters['date_to']);
        }
        $payments = $payments->select(
            'id',
            'payment_number as reference',
            'payment_date as date',
            'amount',
            DB::raw("'payment' as type"),
            'notes',
            'created_at'
        );

        // Purchase returns
        $returns = DB::table('purchase_returns')
            ->where('supplier_id', $this->supplier->id);
        if (!empty($this->filters['date_from'])) {
            $returns->whereDate('return_date', '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $returns->whereDate('return_date', '<=', $this->filters['date_to']);
        }
        $returns = $returns->select(
            'id',
            'return_number as reference',
            'return_date as date',
            'total_return_amount as amount',
            DB::raw("'return' as type"),
            'notes',
            'created_at'
        );

        // Union all
        $query = $invoices->union($payments)->union($returns);

        // Apply type filter if provided (applies after union)
        if (!empty($this->filters['type'])) {
            $query->where('type', $this->filters['type']);
        }

        // Get ordered results
        return $query->orderBy('date', 'asc')->orderBy('created_at', 'asc')->get();
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'التاريخ',
            'النوع',
            'المرجع',
            ' مدين (قبض)',
            'دائن (صرف)',
            'الرصيد المتراكم',
            'ملاحظات',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($transaction): array
    {
        static $runningBalance = 0;
        
        $typeMap = [
            'purchase' => 'فاتورة مشتريات',
            'payment' => 'دفعة',
            'return' => 'إرجاع',
        ];

        $amount = (float) $transaction->amount;
        $debit = 0;
        $credit = 0;

        // Purchases increase debt (debit)
        if ($transaction->type === 'purchase') {
            $debit = $amount;
            $runningBalance += $amount;
        } 
        // Payments and returns decrease debt (credit)
        else {
            $credit = $amount;
            $runningBalance -= $amount;
        }

        return [
            Carbon::parse($transaction->date)->format('Y-m-d'),
            $typeMap[$transaction->type] ?? $transaction->type,
            $transaction->reference,
            $debit > 0 ? number_format($debit, 2) : '-',
            $credit > 0 ? number_format($credit, 2) : '-',
            number_format($runningBalance, 2),
            $transaction->notes ?? '-',
        ];
    }

    /**
     * تنسيق الجدول
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            'A2:G' . ($this->collection()->count() + 2) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ],
        ];
    }

    /**
     * عنوان الشيت
     */
    public function title(): string
    {
        return 'كشف حساب المورد';
    }
}
