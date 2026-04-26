<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class CustomerStatementExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $customer;
    protected $filters;

    public function __construct(Customer $customer, array $filters = [])
    {
        $this->customer = $customer;
        $this->filters = $filters;
    }

    /**
     * جلب بيانات الفواتير
     */
    public function collection()
    {
        $query = DB::table('sales_invoices')
            ->where('customer_id', $this->customer->id)
            ->orderBy('invoice_date', 'asc')
            ->orderBy('created_at', 'asc');

        // Apply date filters
        if (!empty($this->filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('payment_status', $this->filters['status']);
        }

        return $query->get([
            'id',
            'invoice_number as reference',
            'invoice_date as date',
            'total',
            'paid',
            'notes',
            'created_at'
        ]);
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'التاريخ',
            'المرجع',
            'مدين (الإجمالي)',
            'دائن (المدفوع)',
            'الرصيد المتراكم',
            'ملاحظات',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($invoice): array
    {
        static $runningBalance = 0;
        
        $debit = (float) $invoice->total;
        $credit = (float) $invoice->paid;
        $runningBalance += ($debit - $credit);

        return [
            Carbon::parse($invoice->date)->format('Y-m-d'),
            $invoice->reference,
            number_format($debit, 2),
            number_format($credit, 2),
            number_format($runningBalance, 2),
            $invoice->notes ?? '-',
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
                    'startColor' => ['rgb' => '4F63D2'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            'A2:F' . ($this->collection()->count() + 2) => [
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
        return 'كشف حساب العميل';
    }
}
