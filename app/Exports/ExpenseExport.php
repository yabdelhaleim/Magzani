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
 * Export المصروفات إلى Excel.
 *
 * يستخدم الـ DB schema الفعلي:
 *   - expense_number, expense_category_id (FK), amount, expense_date,
 *     description, reference, created_by
 */
class ExpenseExport implements
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

    public function collection()
    {
        $query = DB::table('expenses as e')
            ->leftJoin('expense_categories as ec', 'ec.id', '=', 'e.expense_category_id')
            ->leftJoin('users as u', 'u.id', '=', 'e.created_by')
            ->select(
                'e.id',
                'e.expense_number',
                'e.expense_date',
                'e.amount',
                'e.reference',
                'e.description',
                'ec.name as category_name',
                'u.name as user_name',
            )
            ->orderByDesc('e.expense_date')
            ->orderByDesc('e.id');

        if (! empty($this->filters['expense_category_id'])) {
            $query->where('e.expense_category_id', $this->filters['expense_category_id']);
        }

        if (! empty($this->filters['from_date'])) {
            $query->whereDate('e.expense_date', '>=', $this->filters['from_date']);
        }

        if (! empty($this->filters['to_date'])) {
            $query->whereDate('e.expense_date', '<=', $this->filters['to_date']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'رقم المصروف',
            'التاريخ',
            'التصنيف',
            'المبلغ',
            'رقم المرجع',
            'الوصف',
            'أنشأ بواسطة',
        ];
    }

    public function map($row): array
    {
        return [
            $row->expense_number ?? '-',
            $row->expense_date ? Carbon::parse($row->expense_date)->format('Y-m-d') : '-',
            $row->category_name ?? '-',
            number_format((float) $row->amount, 2),
            $row->reference ?? '-',
            $row->description ?? '-',
            $row->user_name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DC2626'],
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'المصروفات';
    }
}