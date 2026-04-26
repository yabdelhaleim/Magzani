<?php

namespace App\Exports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class SuppliersExport implements 
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected $filters;

    public function __construct(Request $request)
    {
        $this->filters = $request->all();
    }

    /**
     * جلب البيانات
     */
    public function collection()
    {
        $query = Supplier::query();

        // Apply search
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if (isset($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        // Apply balance filter
        if (!empty($this->filters['balance'])) {
            if ($this->filters['balance'] == 'positive') {
                $query->where('balance', '>', 0);
            } elseif ($this->filters['balance'] == 'negative') {
                $query->where('balance', '<', 0);
            } elseif ($this->filters['balance'] == 'zero') {
                $query->where('balance', 0);
            }
        }

        return $query->orderBy('created_at', 'desc')->get(['id', 'code', 'name', 'phone', 'email', 'balance', 'is_active', 'created_at']);
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'الكود',
            'اسم المورد',
            'الهاتف',
            'البريد الإلكتروني',
            'الرصيد',
            'الحالة',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($supplier): array
    {
        return [
            $supplier->code,
            $supplier->name,
            $supplier->phone ?? '-',
            $supplier->email ?? '-',
            number_format($supplier->balance, 2),
            $supplier->is_active ? 'نشط' : 'غير نشط',
            $supplier->created_at->format('Y-m-d'),
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
            'A2:H' . ($this->collection()->count() + 2) => [
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
        return 'قائمة الموردين';
    }
}
