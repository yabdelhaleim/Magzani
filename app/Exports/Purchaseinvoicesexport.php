<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PurchaseInvoicesExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * جلب البيانات
     */
    public function collection()
    {
        $query = PurchaseInvoice::with(['supplier', 'warehouse', 'items.product'])
            ->orderBy('invoice_date', 'desc');

        // تطبيق الفلاتر
        if (!empty($this->filters['supplier_id'])) {
            $query->where('supplier_id', $this->filters['supplier_id']);
        }

        if (!empty($this->filters['warehouse_id'])) {
            $query->where('warehouse_id', $this->filters['warehouse_id']);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('invoice_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('invoice_date', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'رقم الفاتورة',
            'التاريخ',
            'المورد',
            'المخزن',
            'المجموع الفرعي',
            'الخصم',
            'الضريبة',
            'الإجمالي',
            'الحالة',
            'عدد الأصناف',
            'الملاحظات',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($invoice): array
    {
        $statusMap = [
            'pending' => 'معلقة',
            'paid' => 'مدفوعة',
            'cancelled' => 'ملغاة',
        ];

        return [
            $invoice->invoice_number,
            $invoice->invoice_date->format('Y-m-d'),
            $invoice->supplier->name,
            $invoice->warehouse->name,
            number_format($invoice->subtotal, 2),
            number_format($invoice->discount, 2),
            number_format($invoice->tax, 2),
            number_format($invoice->total, 2),
            $statusMap[$invoice->status] ?? $invoice->status,
            $invoice->items->count(),
            $invoice->notes ?? '-',
        ];
    }

    /**
     * تنسيق الجدول
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // تنسيق الهيدر
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '9333EA'], // Purple
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * عنوان الشيت
     */
    public function title(): string
    {
        return 'فواتير الشراء';
    }
}