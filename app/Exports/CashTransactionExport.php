<?php

namespace App\Exports;

use App\Models\CashTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

/**
 * Export حركات النقدية إلى Excel.
 */
class CashTransactionExport implements
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
        $query = CashTransaction::query()
            ->orderByDesc('transaction_date')
            ->orderByDesc('id');

        if (! empty($this->filters['transaction_type'])) {
            $query->where('transaction_type', $this->filters['transaction_type']);
        }

        if (! empty($this->filters['type'])) {
            $query->where('transaction_type', $this->filters['type']);
        }

        if (! empty($this->filters['from_date'])) {
            $query->whereDate('transaction_date', '>=', $this->filters['from_date']);
        }

        if (! empty($this->filters['to_date'])) {
            $query->whereDate('transaction_date', '<=', $this->filters['to_date']);
        }

        if (! empty($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'رقم الحركة',
            'النوع',
            'المبلغ',
            'التصنيف',
            'المرجع',
            'الوصف',
            'أنشأ بواسطة',
        ];
    }

    private function translateType(?string $type): string
    {
        return match ($type) {
            'deposit'        => 'إيداع',
            'withdrawal'     => 'سحب',
            'transfer_in'    => 'تحويل داخل',
            'transfer_out'   => 'تحويل خارج',
            'adjustment'     => 'تسوية',
            'payment'        => 'دفعة',
            'receipt'        => 'سند قبض',
            'expense'        => 'مصروف',
            default          => $type ?? '-',
        };
    }

    public function map($tx): array
    {
        return [
            $tx->transaction_date ? Carbon::parse($tx->transaction_date)->format('Y-m-d') : '-',
            $tx->transaction_number ?? '-',
            $this->translateType($tx->transaction_type),
            number_format((float) $tx->amount, 2),
            $tx->category ?? '-',
            $tx->reference ?? '-',
            $tx->description ?? '-',
            $tx->created_by ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7C3AED'],
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'حركات النقدية';
    }
}