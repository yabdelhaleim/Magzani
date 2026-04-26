<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $report;
    protected $startDate;
    protected $endDate;

    public function __construct($report, $startDate, $endDate)
    {
        $this->report = $report;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    /**
     * جلب البيانات
     */
    public function collection()
    {
        // Build a collection to display summary and details
        $data = collect();

        // Add summary rows
        $data->push([
            'type' => 'summary',
            'title' => 'ملخص التقرير المالي',
            'total_sales' => $this->report['total_sales'] ?? 0,
            'total_purchases' => $this->report['total_purchases'] ?? 0,
            'net_sales' => $this->report['net_sales'] ?? 0,
            'cost_of_sales' => $this->report['cost_of_sales'] ?? 0,
            'gross_profit' => $this->report['gross_profit'] ?? 0,
            'total_expenses' => $this->report['total_expenses'] ?? 0,
            'net_profit' => $this->report['net_profit'] ?? 0,
        ]);

        return $data;
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'البند',
            'المبيعات',
            'المشتريات',
            'صافي المبيعات',
            'تكلفة المبيعات',
            'الإجمالي الربحي',
            'المصروفات',
            'صافي الربح',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($row): array
    {
        if ($row['type'] === 'summary') {
            return [
                'ملخص التقرير المالي',
                number_format($row['total_sales'], 2),
                number_format($row['total_purchases'], 2),
                number_format($row['net_sales'], 2),
                number_format($row['cost_of_sales'], 2),
                number_format($row['gross_profit'], 2),
                number_format($row['total_expenses'], 2),
                number_format($row['net_profit'], 2),
            ];
        }

        return [];
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
                    'startColor' => ['rgb' => '059669'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            // تنسيق الصف الثاني (الملخص)
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ECFDF5'],
                ],
            ],
            'A1:H' . ($this->collection()->count() + 1) => [
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
        return 'التقرير المالي';
    }
}
