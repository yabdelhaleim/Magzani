<?php

namespace App\Services\Accounting;

use App\Models\JournalEntryLine;
use App\Models\Customer;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PartnerLedgerService
{
    /**
     * Get detailed ledger for a customer or supplier (partner).
     *
     * @param string $partnerType 'customer'|'supplier'
     * @param int $partnerId
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getLedger(string $partnerType, int $partnerId, string $from, string $to): array
    {
        // 1. Resolve Partner
        if ($partnerType === 'customer') {
            $partner = Customer::findOrFail($partnerId);
            $normalBalance = 'debit';
        } else {
            $partner = Supplier::findOrFail($partnerId);
            $normalBalance = 'credit';
        }

        // 2. Calculate Opening Balance (all transactions before 'from')
        $openingData = JournalEntryLine::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entry_lines.party_type', $partnerType)
            ->where('journal_entry_lines.party_id', $partnerId)
            ->where('journal_entries.status', 'posted')
            ->where('journal_entries.entry_date', '<', $from)
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        $openDebit = (float)($openingData->total_debit ?? 0);
        $openCredit = (float)($openingData->total_credit ?? 0);

        $openingBal = ($normalBalance === 'debit')
            ? ($openDebit - $openCredit)
            : ($openCredit - $openDebit);

        // 3. Retrieve movements within the period
        $rawLines = JournalEntryLine::join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->join('accounts', 'accounts.id', '=', 'journal_entry_lines.account_id')
            ->where('journal_entry_lines.party_type', $partnerType)
            ->where('journal_entry_lines.party_id', $partnerId)
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$from, $to])
            ->select(
                'journal_entries.id as journal_entry_id',
                'journal_entries.entry_date',
                'journal_entries.entry_number',
                'journal_entries.description as entry_description',
                'journal_entry_lines.description as line_description',
                'journal_entry_lines.debit',
                'journal_entry_lines.credit',
                'journal_entries.source_type',
                'journal_entries.source_id',
                'accounts.name_ar as account_name',
                'accounts.code as account_code'
            )
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->get();

        $lines = [];
        $runBalance = $openingBal;
        $totalDebitPeriod = 0.0;
        $totalCreditPeriod = 0.0;

        foreach ($rawLines as $line) {
            $debit = (float)$line->debit;
            $credit = (float)$line->credit;

            $totalDebitPeriod += $debit;
            $totalCreditPeriod += $credit;

            $runBalance += ($normalBalance === 'debit')
                ? ($debit - $credit)
                : ($credit - $debit);

            $lines[] = [
                'journal_entry_id' => $line->journal_entry_id,
                'date'             => $line->entry_date,
                'entry_no'         => $line->entry_number,
                'description'      => $line->line_description ?? $line->entry_description,
                'debit'            => $debit,
                'credit'           => $credit,
                'balance'          => round($runBalance, 2),
                'source_type' => $line->source_type,
                'source_id'   => $line->source_id,
                'account_name'     => $line->account_name,
                'account_code'     => $line->account_code,
            ];
        }

        return [
            'partner'         => $partner,
            'partner_type'    => $partnerType,
            'from'            => $from,
            'to'              => $to,
            'opening_balance' => round($openingBal, 2),
            'lines'           => $lines,
            'closing_balance' => round($runBalance, 2),
            'total_debit'     => round($totalDebitPeriod, 2),
            'total_credit'    => round($totalCreditPeriod, 2),
        ];
    }
}
