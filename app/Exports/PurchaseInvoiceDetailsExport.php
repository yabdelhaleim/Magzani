<?php

namespace App\Exports;

use App\Models\PurchaseInvoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PurchaseInvoiceDetailsExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected $invoice;

    public function __construct(PurchaseInvoice $invoice)
    {
        $this->invoice = $invoice->load(['supplier', 'warehouse', 'items.product']);
    }

    /**
     * جلب الأصناف
     */
    public function collection()
    {
        return $this->invoice->items;
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            '#',
            'الصنف',
            'الكمية',
            'سعر الوحدة',
            'الإجمالي',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($item): array
    {
        static $index = 1;
        
        return [
            $index++,
            $item->product->name,
            number_format($item->qty, 2),
            number_format($item->price, 2),
            number_format($item->total, 2),
        ];
    }

    /**
     * تنسيق الجدول
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // تنسيق الهيدر الرئيسي (Row 7 - Items Header)
            7 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '9333EA'],
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
        return 'فاتورة ' . $this->invoice->invoice_number;
    }

    /**
     * أحداث إضافية
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // إضافة معلومات الفاتورة في البداية
                $sheet->insertNewRowBefore(1, 6);
                
                // عنوان الفاتورة
                $sheet->setCellValue('A1', 'فاتورة شراء');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                
                // معلومات الفاتورة
                $sheet->setCellValue('A3', 'رقم الفاتورة:');
                $sheet->setCellValue('B3', $this->invoice->invoice_number);
                
                $sheet->setCellValue('A4', 'التاريخ:');
                $sheet->setCellValue('B4', $this->invoice->invoice_date->format('Y-m-d'));
                
                $sheet->setCellValue('D3', 'المورد:');
                $sheet->setCellValue('E3', $this->invoice->supplier->name);
                
                $sheet->setCellValue('D4', 'المخزن:');
                $sheet->setCellValue('E4', $this->invoice->warehouse->name);
                
                // تنسيق معلومات الفاتورة
                $sheet->getStyle('A3:A4')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                $sheet->getStyle('D3:D4')->applyFromArray([
                    'font' => ['bold' => true],
                ]);
                
                // عنوان جدول الأصناف
                $sheet->setCellValue('A6', 'الأصناف:');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                ]);
                
                // إضافة الإجماليات في النهاية
                $lastRow = $sheet->getHighestRow() + 2;
                
                $sheet->setCellValue("D{$lastRow}", 'المجموع الفرعي:');
                $sheet->setCellValue("E{$lastRow}", number_format($this->invoice->subtotal, 2));
                
                if ($this->invoice->discount > 0) {
                    $lastRow++;
                    $sheet->setCellValue("D{$lastRow}", 'الخصم:');
                    $sheet->setCellValue("E{$lastRow}", '- ' . number_format($this->invoice->discount, 2));
                }
                
                if ($this->invoice->tax > 0) {
                    $lastRow++;
                    $sheet->setCellValue("D{$lastRow}", 'الضريبة:');
                    $sheet->setCellValue("E{$lastRow}", '+ ' . number_format($this->invoice->tax, 2));
                }
                
                $lastRow++;
                $sheet->setCellValue("D{$lastRow}", 'الإجمالي النهائي:');
                $sheet->setCellValue("E{$lastRow}", number_format($this->invoice->total, 2) . ' جنيه');
                
                // تنسيق الإجماليات
                $sheet->getStyle("D{$lastRow}:E{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                ]);
                
                // ملاحظات
                if ($this->invoice->notes) {
                    $lastRow += 2;
                    $sheet->setCellValue("A{$lastRow}", 'ملاحظات:');
                    $lastRow++;
                    $sheet->setCellValue("A{$lastRow}", $this->invoice->notes);
                    $sheet->mergeCells("A{$lastRow}:E{$lastRow}");
                }
            },
        ];
    }
}