<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Http\Request;

class CustomersExport implements 
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
        $query = Customer::query();

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

        // Apply type filter
        if (!empty($this->filters['type'])) {
            $query->where('customer_type', $this->filters['type']);
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

        return $query->orderBy('created_at', 'desc')->get(['id', 'code', 'name', 'phone', 'email', 'balance', 'credit_limit', 'customer_type', 'is_active', 'created_at']);
    }

    /**
     * عناوين الأعمدة
     */
    public function headings(): array
    {
        return [
            'الكود',
            'اسم العميل',
            'الهاتف',
            'البريد الإلكتروني',
            'نوع العميل',
            'الرصيد',
            'حد الائتمان',
            'الحالة',
            'تاريخ الإنشاء',
        ];
    }

    /**
     * تنسيق البيانات
     */
    public function map($customer): array
    {
        return [
            $customer->code,
            $customer->name,
            $customer->phone ?? '-',
            $customer->email ?? '-',
            $customer->customer_type == 'retail' ? 'تجزئة' : ($customer->customer_type == 'wholesale' ? 'جملة' : 'VIP'),
            number_format($customer->balance, 2),
            number_format($customer->credit_limit, 2),
            $customer->is_active ? 'نشط' : 'غير نشط',
            $customer->created_at->format('Y-m-d'),
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
            'A2:I' . ($this->collection()->count() + 2) => [
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
        return 'قائمة العملاء';
    }
}
