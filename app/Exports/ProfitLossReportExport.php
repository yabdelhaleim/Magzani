<?php

namespace App\Exports;

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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProfitLossReportExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected $report;
    protected $startDate;
    protected $endDate;
    protected $expensesByCategory;

    public function __construct($report, $startDate, $endDate, $expensesByCategory = [])
    {
        $this->report = $report;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->expensesByCategory = $expensesByCategory;
    }

    /**
     * جلب البيانات
     */
    public function collection()
    {
        $data = collect();

        // Header info
        $data->push(['type' => 'header', 'title' => 'تقرير الأرباح والخسائر']);
        $data->push(['type' => 'period', 'title' => 'الفترة', 'value' => $this->startDate->format('Y-m-d') . ' إلى ' . $this->endDate->format('Y-m-d')]);

        // Revenue section
        $data->push(['type' => 'section', 'title' => 'الإيرادات']);
        $data->push(['type' => 'item', 'label' => 'صافي المبيعات', 'amount' => $this->report['net_sales'] ?? 0]);

        // Cost of Sales section
        $data->push(['type' => 'section', 'title' => 'تكلفة المبيعات']);
        $data->push(['type' => 'item', 'label' => 'تكلفة المبيعات', 'amount' => $this->report['cost_of_sales'] ?? 0]);

        // Gross Profit
        $data->push(['type' => 'total', 'label' => 'الربح الإجمالي', 'amount' => $this->report['gross_profit'] ?? 0]);

        // Expenses section
        if (!empty($this->expensesByCategory)) {
            $data->push(['type' => 'section', 'title' => 'المصروفات']);
            foreach ($this->expensesByCategory as $category => $amount) {
                $data->push(['type' => 'expense', 'label' => $category, 'amount' => $amount]);
            }
        }

        $data->push(['type' => 'total', 'label' => 'إجمالي المصروفات', 'amount' => $this->report['total_expenses'] ?? 0]);

        // Net Profit
        $data->push(['type' => 'final', 'label' => 'صافي الربح', 'amount' => $this->report['net_profit'] ?? 0]);

        return $data;
    }

    /**
     * Registration events
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge cells for header
                $event->sheet->mergeCells('A1:C1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('A3:C3');
                
                // Set column widths
                $event->sheet->getColumnDimension('A')->setWidth(30);
                $event->sheet->getColumnDimension('B')->setWidth(20);
                $event->sheet->getColumnDimension('C')->setWidth(20);
            },
        ];
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'البند',
            'المبلغ',
            'النسبة',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($row): array
    {
        $netSales = $this->report['net_sales'] ?? 1;
        $percentage = 0;
        
        if ($row['type'] === 'item' || $row['type'] === 'expense' || $row['type'] === 'total' || $row['type'] === 'final') {
            $percentage = $netSales > 0 ? ($row['amount'] / $netSales) * 100 : 0;
        }

        return [
            $row['title'] ?? $row['label'] ?? '',
            number_format($row['amount'] ?? 0, 2),
            $row['type'] !== 'header' && $row['type'] !== 'period' && $row['type'] !== 'section' 
                ? number_format($percentage, 2) . '%' 
                : '',
        ];
    }

    /**
     * تنسيق الجدول
     */
    public function styles(Worksheet $sheet)
    {
        $rowCount = $this->collection()->count();
        $styles = [];

        // Header row
        $styles[1] = [
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7C3AED'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];

        // Period row
        $styles[2] = [
            'font' => [
                'italic' => true,
                'size' => 11,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];

        // Section headers
        $rowNum = 3;
        foreach ($this->collection() as $index => $row) {
            if ($row['type'] === 'section') {
                $styles[$rowNum] = [
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '7C3AED'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                ];
            } elseif ($row['type'] === 'total') {
                $styles[$rowNum] = [
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DBEAFE'],
                    ],
                ];
            } elseif ($row['type'] === 'final') {
                $styles[$rowNum] = [
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '059669'],
                    ],
                ];
            }
            $rowNum++;
        }

        // Borders for all data range
        $styles['A1:C' . ($rowCount + 1)] = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E7EB'],
                ],
            ],
        ];

        return $styles;
    }

    /**
     * عنوان الشيت
     */
    public function title(): string
    {
        return 'تقرير الأرباح والخسائر';
    }
}
