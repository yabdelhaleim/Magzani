<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InventoryReportExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $inventory;
    protected $warehouseId;

    public function __construct($inventory, $warehouseId = null)
    {
        $this->inventory = $inventory;
        $this->warehouseId = $warehouseId;
    }

    /**
     * جلب بيانات المخزون
     */
    public function collection()
    {
        return $this->inventory;
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'كود المنتج',
            'اسم المنتج',
            'الفرع/المخزن',
            'الكمية',
            'سعر التكلفة',
            'سعر البيع',
            'الإجمالي',
            'الحد الأدنى للمخزون',
            'الحالة',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($item): array
    {
        $totalValue = $item->quantity * $item->selling_price;
        $status = $item->quantity <= $item->min_stock ? 'منخفض' : 'طبيعي';
        $warehouseName = $item->warehouse_name ?? 'غير محدد';

        return [
            $item->code ?? $item->sku ?? '',
            $item->name,
            $warehouseName,
            number_format($item->quantity, 2),
            number_format($item->purchase_price ?? 0, 2),
            number_format($item->selling_price ?? 0, 2),
            number_format($totalValue, 2),
            number_format($item->min_stock ?? 0, 2),
            $status,
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
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            // تنسيق الأعمدة
            'A1:I' . ($this->collection()->count() + 1) => [
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
        return 'تقرير المخزون';
    }
}
