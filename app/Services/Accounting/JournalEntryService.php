<?php

namespace App\Services\Accounting;

use App\Enums\JournalEntryStatus;
use App\Enums\JournalEntrySource;
use App\Exceptions\Accounting\ClosedPeriodException;
use App\Exceptions\Accounting\NonLeafAccountException;
use App\Exceptions\Accounting\UnbalancedEntryException;
use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\AccountingAuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * JournalEntryService
 *
 * المسؤول عن إنشاء، اعتماد، وعكس قيود اليومية.
 * يضمن:
 *  - Double-Entry Invariant: total_debit == total_credit دائماً عند الاعتماد
 *  - Idempotency: مفتاح source_event_key الفريد يمنع التكرار
 *  - Period Validation: لا يُقبَل القيد في فترة مغلقة
 *  - Reversal Only: تعديل القيد المعتمد يتم عبر الإلغاء والعكس فقط
 */
class JournalEntryService
{
    public function __construct(
        private AccountBalanceService $balanceService
    ) {}
    // ─────────────────────────────────────────────────────────
    // CONSTANTS
    // ─────────────────────────────────────────────────────────

    private const TOLERANCE = 0.01; // حد الاختلاف المسموح به للتقريب

    // ─────────────────────────────────────────────────────────
    // CORE: CREATE DRAFT
    // ─────────────────────────────────────────────────────────

    /**
     * إنشاء قيد يومية في حالة مسودة (Draft) بدون اعتماد
     *
     * @param array{
     *   entry_date: string|Carbon,
     *   description: string,
     *   source_type: string,
     *   source_id: int|null,
     *   source_event_key: string,
     *   reference: string|null,
     *   lines: array<array{account_id:int, debit:float, credit:float, description:string|null, cost_center_id:int|null, party_type:string|null, party_id:int|null}>
     * } $data
     */
    public function createDraft(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            // ── Idempotency check ──────────────────────────────
            $eventKey = $data['source_event_key'];
            if ($existing = JournalEntry::where('source_event_key', $eventKey)->first()) {
                Log::info("[JournalEntryService] Idempotency: entry already exists for key={$eventKey}, id={$existing->id}");
                return $existing;
            }

            // ── Validate lines ─────────────────────────────────
            $this->validateLines($data['lines'] ?? []);

            // ── Find fiscal period ─────────────────────────────
            $entryDate = Carbon::parse($data['entry_date']);
            $period    = $this->findOpenPeriod($entryDate);

            // ── Calculate totals ───────────────────────────────
            [$totalDebit, $totalCredit] = $this->calculateTotals($data['lines']);

            // ── Create entry header ────────────────────────────
            $entry = JournalEntry::create([
                'entry_number'     => $this->generateEntryNumber(),
                'entry_date'       => $entryDate->toDateString(),
                'fiscal_period_id' => $period?->id,
                'description'      => $data['description'],
                'reference'        => $data['reference'] ?? null,
                'status'           => JournalEntryStatus::DRAFT,
                'source_type'      => $data['source_type'],
                'source_id'        => $data['source_id'] ?? null,
                'source_event_key' => $eventKey,
                'total_debit'      => $totalDebit,
                'total_credit'     => $totalCredit,
                'currency_code'    => $data['currency_code'] ?? $this->getDefaultCurrency(),
                'created_by'       => Auth::id(),
            ]);

            // ── Create lines ───────────────────────────────────
            $this->createLines($entry, $data['lines']);

            $this->writeAuditLog($entry, 'created', 'إنشاء قيد مسودة');

            return $entry->load('lines');
        });
    }

    // ─────────────────────────────────────────────────────────
    // CORE: POST (DRAFT → POSTED)
    // ─────────────────────────────────────────────────────────

    /**
     * اعتماد قيد مسودة وتحويله إلى POSTED
     * يُفشل العملية إذا لم يكن مُوازَناً أو الفترة مغلقة
     */
    public function post(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            if ($entry->status !== JournalEntryStatus::DRAFT) {
                throw new RuntimeException(
                    "لا يمكن اعتماد قيد بحالة [{$entry->status->value}]. يجب أن يكون في حالة مسودة."
                );
            }

            // ── Balance check ──────────────────────────────────
            $this->assertBalanced($entry);

            // ── Period check ───────────────────────────────────
            if ($entry->fiscal_period_id) {
                $period = $entry->fiscalPeriod;
                if ($period && $period->is_closed) {
                    throw new ClosedPeriodException($period->name);
                }
            }

            // ── Post ───────────────────────────────────────────
            $entry->update([
                'status'    => JournalEntryStatus::POSTED,
                'posted_at' => now(),
                'posted_by' => Auth::id(),
            ]);

            // ✅ تحديث الأرصدة المتراكمة تلقائياً عند اعتماد القيد
            $this->balanceService->applyLines($entry->lines);

            $this->writeAuditLog($entry, 'posted', 'اعتماد القيد اليومي');

            return $entry->fresh('lines', 'fiscalPeriod');
        });
    }

    /**
     * إنشاء قيد واعتماده مباشرة في خطوة واحدة (الأكثر استخداماً في PostingService)
     */
    public function createAndPost(array $data): JournalEntry
    {
        $entry = $this->createDraft($data);

        // إذا كان موجوداً بالفعل (idempotency) وهو مُعتمَد، نُعيده مباشرة
        if ($entry->status === JournalEntryStatus::POSTED) {
            return $entry;
        }

        return $this->post($entry);
    }

    // ─────────────────────────────────────────────────────────
    // CORE: REVERSE
    // ─────────────────────────────────────────────────────────

    /**
     * عكس قيد مُعتمَد (Reversal)
     * يُنشئ قيداً جديداً بأرقام مقلوبة ويُشير إلى الأصل
     *
     * @param string $reason  سبب العكس (مطلوب للمراجعة)
     * @param Carbon|string|null $reversalDate  تاريخ قيد العكس (الافتراضي: اليوم)
     */
    public function reverse(
        JournalEntry $originalEntry,
        string $reason,
        Carbon|string|null $reversalDate = null
    ): JournalEntry {
        return DB::transaction(function () use ($originalEntry, $reason, $reversalDate) {
            if ($originalEntry->status !== JournalEntryStatus::POSTED) {
                throw new RuntimeException(
                    "لا يمكن عكس قيد بحالة [{$originalEntry->status->value}]. يجب أن يكون مُعتمَداً."
                );
            }

            if ($originalEntry->reversed_entry_id) {
                throw new RuntimeException(
                    "هذا القيد مُعكوس بالفعل (رقم قيد العكس: {$originalEntry->reversed_entry_id})."
                );
            }

            $date = $reversalDate ? Carbon::parse($reversalDate) : now();

            // ── Build reversal lines (flip debit/credit) ───────
            $reversalLines = $originalEntry->lines->map(function ($line) {
                return [
                    'account_id'     => $line->account_id,
                    'debit'          => $line->credit,
                    'credit'         => $line->debit,
                    'description'    => $line->description,
                    'cost_center_id' => $line->cost_center_id,
                    'party_type'     => $line->party_type,
                    'party_id'       => $line->party_id,
                ];
            })->toArray();

            // ── Create reversal entry ──────────────────────────
            $reversalEntry = $this->createAndPost([
                'entry_date'       => $date->toDateString(),
                'description'      => "عكس: {$originalEntry->description} — {$reason}",
                'source_type'      => $originalEntry->source_type,
                'source_id'        => $originalEntry->source_id,
                'source_event_key' => $originalEntry->source_event_key . ':reversal:' . now()->timestamp,
                'reference'        => $originalEntry->entry_number,
                'lines'            => $reversalLines,
            ]);

            // ── Link entries to each other ─────────────────────
            $originalEntry->update(['reversed_entry_id' => $reversalEntry->id]);
            $reversalEntry->update([
                'reversal_of_id' => $originalEntry->id,
                'status'         => JournalEntryStatus::REVERSED,
            ]);
            $originalEntry->update(['status' => JournalEntryStatus::REVERSED]);

            $this->writeAuditLog($originalEntry, 'reversed', "تم عكس القيد — {$reason}");
            $this->writeAuditLog($reversalEntry,  'reversal_created', "قيد عكسي للقيد #{$originalEntry->id}");

            return $reversalEntry->fresh('lines');
        });
    }

    /**
     * عكس تلقائي لقيد معتمد عند إلغاء مستند المصدر
     */
    public function revert(
        JournalEntry $originalEntry,
        string $reason = 'إلغاء مستند المصدر',
        Carbon|string|null $reversalDate = null
    ): JournalEntry {
        return DB::transaction(function () use ($originalEntry, $reason, $reversalDate) {
            if ($originalEntry->status !== JournalEntryStatus::POSTED) {
                throw new \RuntimeException(
                    "لا يمكن عكس قيد بحالة [{$originalEntry->status->value}]. يجب أن يكون مُعتمَداً."
                );
            }

            if ($originalEntry->reversed_entry_id) {
                throw new \RuntimeException(
                    "هذا القيد مُعكوس بالفعل (رقم قيد العكس: {$originalEntry->reversed_entry_id})."
                );
            }

            $date = $reversalDate ? Carbon::parse($reversalDate) : now();

            // ── Build reversal lines (flip debit/credit) ───────
            $reversalLines = $originalEntry->lines->map(function ($line) {
                return [
                    'account_id'     => $line->account_id,
                    'debit'          => $line->credit,
                    'credit'         => $line->debit,
                    'description'    => "عكس: " . $line->description,
                    'cost_center_id' => $line->cost_center_id,
                    'party_type'     => $line->party_type,
                    'party_id'       => $line->party_id,
                ];
            })->toArray();

            // ── Create reversal entry ──────────────────────────
            $reversalEntry = $this->createAndPost([
                'entry_date'       => $date->toDateString(),
                'description'      => "عكس تلقائي: {$originalEntry->description} — {$reason}",
                'source_type'      => 'reversal',
                'source_id'        => $originalEntry->id,
                'source_event_key' => 'reversal:' . $originalEntry->id . ':' . $date->timestamp . ':' . uniqid(),
                'reference'        => $originalEntry->entry_number,
                'lines'            => $reversalLines,
            ]);

            // ── Link entries to each other ─────────────────────
            $originalEntry->update([
                'reversed_entry_id' => $reversalEntry->id,
                'status'            => JournalEntryStatus::REVERSED,
            ]);

            $reversalEntry->update([
                'reversal_of_id' => $originalEntry->id,
                'status'         => JournalEntryStatus::POSTED,
            ]);

            $this->writeAuditLog($originalEntry, 'reversed', "تم عكس القيد تلقائياً — {$reason}");
            $this->writeAuditLog($reversalEntry,  'reversal_created', "قيد عكسي تلقائي للقيد #{$originalEntry->id}");

            return $reversalEntry->fresh('lines');
        });
    }

    // ─────────────────────────────────────────────────────────
    // VALIDATION HELPERS
    // ─────────────────────────────────────────────────────────

    /**
     * التحقق من أن القيد مُوازَن (Debit == Credit) ضمن هامش التقريب
     */
    public function assertBalanced(JournalEntry $entry): void
    {
        $debit  = $entry->lines->sum('debit');
        $credit = $entry->lines->sum('credit');

        if (abs($debit - $credit) > self::TOLERANCE) {
            throw new UnbalancedEntryException(
                totalDebit: round($debit, 2),
                totalCredit: round($credit, 2),
            );
        }
    }

    /**
     * التحقق الأساسي من أسطر القيد قبل الحفظ
     */
    private function validateLines(array $lines): void
    {
        if (count($lines) < 2) {
            throw new RuntimeException('القيد اليومي يجب أن يحتوي على سطرَين على الأقل (مدين ودائن).');
        }

        $accountIds = array_unique(array_column($lines, 'account_id'));
        $accounts   = Account::whereIn('id', $accountIds)->get()->keyBy('id');

        foreach ($lines as $i => $line) {
            if (empty($line['account_id'])) {
                throw new RuntimeException("السطر #{$i}: account_id مطلوب.");
            }

            // التحقق أن الحساب ورقي (leaf) — لا يُسمح بالترحيل لحساب أب
            $account = $accounts->get($line['account_id']);
            if ($account && !$account->is_leaf) {
                throw new NonLeafAccountException($account->code, $account->name_ar);
            }

            $debit  = (float) ($line['debit']  ?? 0);
            $credit = (float) ($line['credit'] ?? 0);

            if ($debit < 0 || $credit < 0) {
                throw new RuntimeException("السطر #{$i}: لا يُسمح بأرقام سالبة في المدين أو الدائن.");
            }

            if ($debit > 0 && $credit > 0) {
                throw new RuntimeException("السطر #{$i}: لا يمكن أن يحمل السطر الواحد مدين ودائن في نفس الوقت.");
            }

            if ($debit === 0.0 && $credit === 0.0) {
                throw new RuntimeException("السطر #{$i}: يجب أن يحمل إما مدين أو دائن.");
            }
        }
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────

    private function createLines(JournalEntry $entry, array $lines): void
    {
        foreach ($lines as $i => $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'line_number'      => $i + 1,
                'account_id'       => $line['account_id'],
                'debit'            => (float) ($line['debit']  ?? 0),
                'credit'           => (float) ($line['credit'] ?? 0),
                'description'      => $line['description']    ?? null,
                'cost_center_id'   => $line['cost_center_id'] ?? null,
                'party_type'       => $line['party_type']     ?? null,
                'party_id'         => $line['party_id']       ?? null,
            ]);
        }
    }

    private function calculateTotals(array $lines): array
    {
        $debit  = 0.0;
        $credit = 0.0;

        foreach ($lines as $line) {
            $debit  += (float) ($line['debit']  ?? 0);
            $credit += (float) ($line['credit'] ?? 0);
        }

        return [round($debit, 2), round($credit, 2)];
    }

    /**
     * البحث عن الفترة المالية المفتوحة لتاريخ معين
     * لا يُلزم بوجود فترة (يعمل بدونها إذا لم تكن مُضبطة بعد)
     */
    private function findOpenPeriod(Carbon $date): ?FiscalPeriod
    {
        return FiscalPeriod::where('start_date', '<=', $date->toDateString())
            ->where('end_date',   '>=', $date->toDateString())
            ->where('is_closed', false)
            ->first();
    }

    /**
     * توليد رقم قيد تسلسلي: JE-2026-000001
     */
    private function generateEntryNumber(): string
    {
        $settings = AccountingSetting::first();
        $prefix   = $settings?->numbering_prefix_je ?? 'JE';
        $year     = now()->year;

        $lastEntry = JournalEntry::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $seq = $lastEntry ? ((int) substr($lastEntry->entry_number, -6)) + 1 : 1;

        return "{$prefix}-{$year}-" . str_pad($seq, 6, '0', STR_PAD_LEFT);
    }

    private function getDefaultCurrency(): string
    {
        return AccountingSetting::value('default_currency') ?? 'EGP';
    }

    /**
     * تسجيل سجل مراجعة للعملية
     */
    private function writeAuditLog(JournalEntry $entry, string $action, string $notes): void
    {
        try {
            AccountingAuditLog::create([
                'auditable_type' => JournalEntry::class,
                'auditable_id'   => $entry->id,
                'action'         => $action,
                'old_values'     => null,
                'new_values'     => ['status' => $entry->status?->value],
                'ip_address'     => request()->ip(),
                'user_id'        => Auth::id(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("[JournalEntryService] AuditLog failed: " . $e->getMessage());
        }
    }
}
