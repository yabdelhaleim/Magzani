<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

/**
 * Export حركات المخزون إلى Excel.
 *
 * الاستخدام:
 *   return Excel::download(new InventoryMovementExport($filters), 'movements.xlsx');
 */
class InventoryMovementExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * جلب الحركات مع تطبيق الفلاتر.
     */
    public function collection()
    {
        $query = DB::table('inventory_movements as im')
            ->leftJoin('warehouses as w', 'w.id', '=', 'im.warehouse_id')
            ->leftJoin('products as p', 'p.id', '=', 'im.product_id')
            ->select(
                'im.id',
                'im.movement_number',
                'im.movement_date',
                'im.movement_type',
                'im.quantity',
                'im.quantity_change',
                'im.quantity_before',
                'im.quantity_after',
                'im.unit_cost',
                'im.total_cost',
                'w.name as warehouse_name',
                'p.name as product_name',
                'p.code as product_code',
                'im.notes',
                'im.reference_type',
            )
            ->orderByDesc('im.movement_date')
            ->orderByDesc('im.id');

        if (! empty($this->filters['warehouse_id'])) {
            $query->where('im.warehouse_id', $this->filters['warehouse_id']);
        }

        if (! empty($this->filters['product_id'])) {
            $query->where('im.product_id', $this->filters['product_id']);
        }

        if (! empty($this->filters['movement_type'])) {
            $query->where('im.movement_type', $this->filters['movement_type']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('im.movement_date', '>=', $this->filters['date_from']);
        }

        if (! empty($this->filters['date_to'])) {
            $query->whereDate('im.movement_date', '<=', $this->filters['date_to']);
        }

        return $query->get();
    }

    /**
     * عناوين الأعمدة بالعربية.
     */
    public function headings(): array
    {
        return [
            'رقم الحركة',
            'التاريخ',
            'النوع',
            'المخزن',
            'المنتج',
            'الكود',
            'الكمية',
            'التغيير',
            'رصيد قبل',
            'رصيد بعد',
            'تكلفة الوحدة',
            'إجمالي التكلفة',
            'المرجع',
            'ملاحظات',
        ];
    }

    /**
     * ترجمة نوع الحركة للعربية.
     */
    private function translateType(string $type): string
    {
        return match ($type) {
            'purchase'         => 'شراء',
            'sale'             => 'بيع',
            'return_in'        => 'مرتجع دخول',
            'return_out'       => 'مرتجع خروج',
            'transfer_in'      => 'تحويل دخول',
            'transfer_out'     => 'تحويل خروج',
            'adjustment'       => 'تسوية',
            'adjustment_in'    => 'تسوية إضافة',
            'adjustment_out'   => 'تسوية خصم',
            'damage'           => 'تالف',
            'expired'          => 'منتهي الصلاحية',
            'production'       => 'إنتاج',
            'consumption'      => 'استهلاك',
            'material_in'      => 'مواد دخول',
            'material_out'     => 'مواد خروج',
            default            => $type,
        };
    }

    /**
     * تنسيق كل صف.
     */
    public function map($row): array
    {
        return [
            $row->movement_number,
            $row->movement_date ? Carbon::parse($row->movement_date)->format('Y-m-d H:i') : '-',
            $this->translateType($row->movement_type ?? ''),
            $row->warehouse_name ?? '-',
            $row->product_name ?? '-',
            $row->product_code ?? '-',
            number_format((float) $row->quantity, 3),
            number_format((float) $row->quantity_change, 3),
            number_format((float) $row->quantity_before, 3),
            number_format((float) $row->quantity_after, 3),
            number_format((float) $row->unit_cost, 2),
            number_format((float) $row->total_cost, 2),
            $row->reference_type ?? '-',
            $row->notes ?? '-',
        ];
    }

    /**
     * تنسيق الجدول.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E40AF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'حركات المخزون';
    }
}