<?php

namespace App\Services\Accounting;

use App\Models\AccountingSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use RuntimeException;

class VatSettlementService
{
    public function __construct(
        private JournalEntryService $journalService
    ) {}

    /**
     * حساب أرصدة ضريبة المخرجات والمدخلات لفترة معينة
     */
    public function calculate(string $from, string $to): array
    {
        $settings = AccountingSetting::first();

        if (!$settings || !$settings->tax_account_output_id || !$settings->tax_account_input_id) {
            throw new RuntimeException('حسابات ضريبة القيمة المضافة غير مُعدّة في الإعدادات المحاسبية.');
        }

        $outputBalance = $this->accountBalanceInPeriod($settings->tax_account_output_id, $from, $to);
        $inputBalance  = $this->accountBalanceInPeriod($settings->tax_account_input_id, $from, $to);

        // Output VAT: credit nature (liability) — net credit balance
        // Input VAT: debit nature (asset) — net debit balance
        $outputVat = max(0, $outputBalance['credit'] - $outputBalance['debit']);
        $inputVat  = max(0, $inputBalance['debit'] - $inputBalance['credit']);
        $netPayable = round($outputVat - $inputVat, 2);

        return [
            'from'              => $from,
            'to'                => $to,
            'output_vat'        => $outputVat,
            'input_vat'         => $inputVat,
            'net_payable'       => $netPayable,
            'net_refundable'    => $netPayable < 0 ? abs($netPayable) : 0,
            'output_account_id' => $settings->tax_account_output_id,
            'input_account_id'  => $settings->tax_account_input_id,
            'cash_account_id'   => $settings->cash_account_id,
        ];
    }

    /**
     * إنشاء قيد تسوية ضريبة القيمة المضافة
     */
    public function settle(string $from, string $to, ?string $settlementDate = null): JournalEntry
    {
        $data = $this->calculate($from, $to);
        $settlementDate = $settlementDate ?? $to;
        $eventKey = "vat_settlement:{$from}:{$to}";

        $existing = JournalEntry::where('source_event_key', $eventKey)->first();
        if ($existing) {
            return $existing;
        }

        $lines = [];

        if ($data['output_vat'] > 0) {
            $lines[] = [
                'account_id'  => $data['output_account_id'],
                'debit'       => $data['output_vat'],
                'credit'      => 0,
                'description' => 'تسوية ضريبة مخرجات',
            ];
        }

        if ($data['input_vat'] > 0) {
            $lines[] = [
                'account_id'  => $data['input_account_id'],
                'debit'       => 0,
                'credit'      => $data['input_vat'],
                'description' => 'تسوية ضريبة مدخلات',
            ];
        }

        $net = $data['net_payable'];

        if (abs($net) > 0.01) {
            if ($net > 0) {
                // صافي مستحق للهيئة
                $lines[] = [
                    'account_id'  => $data['cash_account_id'],
                    'debit'       => 0,
                    'credit'      => $net,
                    'description' => 'سداد ضريبة مستحقة',
                ];
            } else {
                // رصيد مدين (استرداد)
                $lines[] = [
                    'account_id'  => $data['cash_account_id'],
                    'debit'       => abs($net),
                    'credit'      => 0,
                    'description' => 'استرداد ضريبة مدخلات',
                ];
            }
        }

        if (empty($lines)) {
            throw new RuntimeException('لا توجد أرصدة ضريبية للتسوية في هذه الفترة.');
        }

        return $this->journalService->createAndPost([
            'entry_date'       => $settlementDate,
            'description'      => "تسوية ضريبة القيمة المضافة — {$from} إلى {$to}",
            'source_type'      => 'vat_settlement',
            'source_id'        => null,
            'source_event_key' => $eventKey,
            'reference'        => "VAT-{$from}-{$to}",
            'lines'            => $lines,
        ]);
    }

    private function accountBalanceInPeriod(int $accountId, string $from, string $to): array
    {
        $debit = (float) JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                ->whereBetween('entry_date', [$from, $to]))
            ->sum('debit');

        $credit = (float) JournalEntryLine::where('account_id', $accountId)
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')
                ->whereBetween('entry_date', [$from, $to]))
            ->sum('credit');

        return ['debit' => $debit, 'credit' => $credit];
    }
}
