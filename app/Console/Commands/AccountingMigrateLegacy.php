<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountingSetting;
use App\Models\Customer;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\Supplier;
use App\Services\Accounting\JournalEntryService;
use App\Services\AccountingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * ترحيل البيانات القديمة — الطريقة A: قيد افتتاحي فقط (مُوصى بها)
 *
 * لا يُرحّل كل العمليات التاريخية — يُنشئ قيداً افتتاحياً بأرصدة فعلية
 * من sub-ledgers، ثم من اليوم فصاعداً الترحيل التلقائي يعمل.
 */
class AccountingMigrateLegacy extends Command
{
    protected $signature = 'accounting:migrate-legacy
                            {--dry-run : عرض الأرصدة المقترحة بدون إنشاء قيد}
                            {--force : تجاوز التحذير إذا وُجد قيد افتتاحي}';

    protected $description = 'إنشاء قيد افتتاحي من أرصدة sub-ledgers (بدون ترحيل تاريخي)';

    private const OPENING_KEY = 'opening_balance:legacy_migration';

    public function handle(JournalEntryService $journalService, AccountingService $accountingService): int
    {
        $dryRun = $this->option('dry-run');
        $force  = $this->option('force');

        if ($dryRun) {
            $this->warn('وضع المعاينة (dry-run) — لن يتم إنشاء أي قيود.');
        }

        // التحقق من المتطلبات
        if (Account::count() === 0) {
            $this->error('دليل الحسابات فارغ. شغّل accounting:seed-default-accounts أولاً.');
            return self::FAILURE;
        }

        $settings = AccountingSetting::first();
        if (!$settings) {
            $this->error('إعدادات المحاسبة غير موجودة.');
            return self::FAILURE;
        }

        $existing = JournalEntry::where('source_event_key', self::OPENING_KEY)->first();
        if ($existing && !$force) {
            $this->error("قيد افتتاحي موجود بالفعل (#{$existing->entry_number}). استخدم --force لإعادة الإنشاء.");
            return self::FAILURE;
        }

        // حساب الأرصدة من sub-ledgers
        $balances = $this->calculateOpeningBalances($settings, $accountingService);

        if (empty($balances['lines'])) {
            $this->warn('لا توجد أرصدة افتتاحية لإدخالها (كل الأرصدة صفر).');
            return self::SUCCESS;
        }

        // عرض الجدول
        $this->table(
            ['الكود', 'الحساب', 'مدين', 'دائن'],
            collect($balances['lines'])->map(fn ($l) => [
                Account::find($l['account_id'])?->code ?? $l['account_id'],
                Account::find($l['account_id'])?->name_ar ?? '—',
                number_format($l['debit'], 2),
                number_format($l['credit'], 2),
            ])->toArray()
        );

        $this->info("إجمالي المدين: " . number_format($balances['total_debit'], 2));
        $this->info("إجمالي الدائن: " . number_format($balances['total_credit'], 2));

        if (abs($balances['total_debit'] - $balances['total_credit']) > 0.01) {
            $this->error('القيد غير متوازن! راجع الأرصدة.');
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('✓ المعاينة اكتملت — شغّل بدون --dry-run للإنشاء الفعلي.');
            return self::SUCCESS;
        }

        if (!$this->confirm('هل تريد إنشاء قيد الأرصدة الافتتاحية؟', true)) {
            return self::SUCCESS;
        }

        $entryDate = FiscalYear::where('is_current', true)->value('start_date')
            ?? now()->startOfYear()->toDateString();

        $entry = $journalService->createAndPost([
            'entry_date'       => $entryDate,
            'description'      => 'قيد افتتاحي — ترحيل أرصدة sub-ledgers',
            'source_type'      => 'opening_balance',
            'source_id'        => null,
            'source_event_key' => self::OPENING_KEY,
            'lines'            => $balances['lines'],
        ]);

        $this->info("✓ تم إنشاء قيد الافتتاح #{$entry->entry_number} (ID: {$entry->id})");
        $this->info('من الآن فصاعداً: كل عملية جديدة تُرحّل تلقائياً. العمليات القديمة تبقى في sub-ledgers كمرجع.');

        return self::SUCCESS;
    }

    private function calculateOpeningBalances(AccountingSetting $settings, AccountingService $accountingService): array
    {
        $lines = [];

        // 1. الصندوق
        $cashBalance = $accountingService->getCashBalance();
        if ($cashBalance > 0 && $settings->cash_account_id) {
            $lines[] = ['account_id' => $settings->cash_account_id, 'debit' => $cashBalance, 'credit' => 0, 'description' => 'رصيد صندوق'];
        }

        // 2. ذمم مدينة (عملاء)
        $arBalance = (float) Customer::where('balance', '>', 0)->sum('balance');
        if ($arBalance > 0 && $settings->ar_account_id) {
            $lines[] = ['account_id' => $settings->ar_account_id, 'debit' => $arBalance, 'credit' => 0, 'description' => 'ذمم عملاء'];
        }

        // 3. المخزون
        $inventoryValue = (float) DB::table('product_warehouse')
            ->join('products', 'products.id', '=', 'product_warehouse.product_id')
            ->selectRaw('COALESCE(SUM(product_warehouse.quantity * products.purchase_price), 0) as val')
            ->value('val');

        if ($inventoryValue > 0 && $settings->inventory_account_id) {
            $lines[] = ['account_id' => $settings->inventory_account_id, 'debit' => $inventoryValue, 'credit' => 0, 'description' => 'رصيد مخزون'];
        }

        // 4. ذمم دائنة (موردين)
        $apBalance = (float) Supplier::sum('current_balance');
        if ($apBalance <= 0) {
            $apBalance = (float) Supplier::sum('balance');
        }
        if ($apBalance > 0 && $settings->ap_account_id) {
            $lines[] = ['account_id' => $settings->ap_account_id, 'debit' => 0, 'credit' => $apBalance, 'description' => 'ذمم موردين'];
        }

        // 5. موازنة الفرق → الأرباح المحتجزة أو رأس المال
        $totalDebit  = array_sum(array_column($lines, 'debit'));
        $totalCredit = array_sum(array_column($lines, 'credit'));
        $diff        = round($totalDebit - $totalCredit, 2);

        if (abs($diff) > 0.01) {
            $equityAccountId = $settings->retained_earnings_id
                ?? Account::where('code', '3200')->value('id')
                ?? Account::where('code', '3100')->value('id');

            if ($equityAccountId) {
                if ($diff > 0) {
                    $lines[] = ['account_id' => $equityAccountId, 'debit' => 0, 'credit' => $diff, 'description' => 'موازنة حقوق ملكية'];
                } else {
                    $lines[] = ['account_id' => $equityAccountId, 'debit' => abs($diff), 'credit' => 0, 'description' => 'موازنة حقوق ملكية'];
                }
            }
        }

        return [
            'lines'        => $lines,
            'total_debit'  => round(array_sum(array_column($lines, 'debit')), 2),
            'total_credit' => round(array_sum(array_column($lines, 'credit')), 2),
        ];
    }
}
