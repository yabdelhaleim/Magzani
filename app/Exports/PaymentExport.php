<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

/**
 * Export المدفوعات إلى Excel.
 */
class PaymentExport implements
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
        $query = Payment::query()
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if (! empty($this->filters['payable_type'])) {
            $query->where('payable_type', $this->filters['payable_type']);
        }

        if (! empty($this->filters['type'])) {
            // نوع عبر payable_type
            $query->where('payable_type', $this->filters['type']);
        }

        if (! empty($this->filters['payment_method'])) {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        if (! empty($this->filters['method'])) {
            $query->where('payment_method', $this->filters['method']);
        }

        if (! empty($this->filters['from_date'])) {
            $query->whereDate('payment_date', '>=', $this->filters['from_date']);
        }

        if (! empty($this->filters['to_date'])) {
            $query->whereDate('payment_date', '<=', $this->filters['to_date']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'نوع المستفيد',
            'المبلغ',
            'طريقة الدفع',
            'رقم المرجع',
            'ملاحظات',
        ];
    }

    private function translatePayableType(?string $type): string
    {
        if (! $type) {
            return '-';
        }
        // قطع الكلاس لأخذ الاسم فقط
        $parts = explode('\\', $type);
        return match (end($parts)) {
            'Supplier'        => 'مورد',
            'Customer'        => 'عميل',
            'SalesInvoice'    => 'فاتورة مبيعات',
            'PurchaseInvoice' => 'فاتورة مشتريات',
            'Expense'         => 'مصروف',
            default           => end($parts) ?: '-',
        };
    }

    private function translatePaymentMethod(?string $method): string
    {
        return match ($method) {
            'cash'  => 'نقدي',
            'bank'  => 'تحويل بنكي',
            'check' => 'شيك',
            'card'  => 'بطاقة',
            default => $method ?? '-',
        };
    }

    public function map($payment): array
    {
        return [
            $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : '-',
            $this->translatePayableType($payment->payable_type),
            number_format((float) $payment->amount, 2),
            $this->translatePaymentMethod($payment->payment_method),
            $payment->reference_number ?? '-',
            $payment->notes ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '059669'],
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'المدفوعات';
    }
}