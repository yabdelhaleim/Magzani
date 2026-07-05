<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Enums\JournalEntryStatus;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\FinancialReportService;
use App\Services\Accounting\PostingService;
use App\Models\AccountingSetting;
use Tests\TestCase;

class AccountingFeatureTest extends TestCase
{
    protected $tenant;
    protected $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Clean up plans
        Plan::query()->delete();

        // 2. Create a plan with advanced accounting feature
        Plan::create([
            'slug' => 'advanced-accounting',
            'name' => 'باقة الحسابات المتقدمة',
            'price' => 99.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced'],
            'is_active' => true,
        ]);
    }

    protected function createTestTenant(string $planId = 'advanced-accounting')
    {
        $this->tenantId = 't-acc-' . uniqid();
        
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => $planId,
            'is_suspended' => false,
        ]);

        $tenant->domains()->create([
            'domain' => $this->tenantId . '.localhost',
        ]);

        return $tenant;
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->domains()->delete();
                $this->tenant->delete();
            } catch (\Exception $e) {
                // Ignore
            }
        }
        parent::tearDown();
    }

    public function test_accounting_system_workflow(): void
    {
        $this->tenant = $this->createTestTenant('advanced-accounting');

        // Initialize tenant database context
        tenancy()->initialize($this->tenant);

        // 1. Seed Default Chart of Accounts & Settings
        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        // Verify accounts seeded
        $this->assertDatabaseHas('accounts', [
            'code' => '1110', // Cash
            'code' => '3100', // Capital
        ]);

        // 2. Create Fiscal Year and Fiscal Period
        $fiscalYear = FiscalYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
            'is_current' => true,
        ]);

        $fiscalPeriod = FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);

        // Resolve service
        $journalService = app(JournalEntryService::class);
        $reportService = app(FinancialReportService::class);

        $cashAccount = Account::where('code', '1110')->firstOrFail();
        $capitalAccount = Account::where('code', '3100')->firstOrFail();

        // 3. Create Draft Manual Journal Entry
        $entryData = [
            'entry_date' => '2026-07-03',
            'description' => 'إيداع رأس مال نقدي في الصندوق',
            'source_type' => 'manual',
            'source_event_key' => 'test_manual_je_' . uniqid(),
            'lines' => [
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 150000.00,
                    'credit' => 0.00,
                    'description' => 'مدين - الصندوق',
                ],
                [
                    'account_id' => $capitalAccount->id,
                    'debit' => 0.00,
                    'credit' => 150000.00,
                    'description' => 'دائن - رأس المال',
                ]
            ]
        ];

        $entry = $journalService->createDraft($entryData);

        $this->assertEquals(JournalEntryStatus::DRAFT, $entry->status);
        $this->assertEquals(150000.00, (float)$entry->total_debit);
        $this->assertEquals(150000.00, (float)$entry->total_credit);

        // Verify no balances are updated yet (because it's draft)
        $cashBalance = AccountBalance::where('account_id', $cashAccount->id)->first();
        $this->assertNull($cashBalance);

        // 4. Post Journal Entry
        $postedEntry = $journalService->post($entry);
        $this->assertEquals(JournalEntryStatus::POSTED, $postedEntry->status);

        // Verify materialized balances are updated correctly after posting
        $cashBalance = AccountBalance::where('account_id', $cashAccount->id)->firstOrFail();
        $capitalBalance = AccountBalance::where('account_id', $capitalAccount->id)->firstOrFail();

        // Cash normal balance is debit, so balance = debit - credit = 150,000
        $this->assertEquals(150000.00, (float)$cashBalance->balance);
        $this->assertEquals(150000.00, (float)$cashBalance->ytd_debit);
        
        // Capital normal balance is credit, so balance = credit - debit = 150,000
        $this->assertEquals(150000.00, (float)$capitalBalance->balance);
        $this->assertEquals(150000.00, (float)$capitalBalance->ytd_credit);

        // 5. Run Integrity Check Command
        $exitCode = \Illuminate\Support\Facades\Artisan::call('accounting:validate-integrity');
        $this->assertEquals(0, $exitCode);

        // 6. Test Reports
        // Trial Balance
        $trialBalance = $reportService->trialBalance('2026-07-05');
        $this->assertTrue($trialBalance['is_balanced']);
        $this->assertEquals(150000.00, (float)$trialBalance['total_debit']);
        $this->assertEquals(150000.00, (float)$trialBalance['total_credit']);

        // Balance Sheet
        $balanceSheet = $reportService->balanceSheet('2026-07-05');
        $this->assertEquals(150000.00, (float)$balanceSheet['total_assets']);
        $this->assertEquals(150000.00, (float)$balanceSheet['total_liabilities_equity']);

        // 7. Reverse the Journal Entry
        $reversalEntry = $journalService->reverse($postedEntry, 'خطأ في القيمة');
        $this->assertEquals(JournalEntryStatus::REVERSED, $postedEntry->fresh()->status);
        $this->assertEquals(JournalEntryStatus::REVERSED, $reversalEntry->status);

        // Verify balances are offset and return to zero
        $cashBalance = $cashBalance->fresh();
        $capitalBalance = $capitalBalance->fresh();

        $this->assertEquals(0.00, (float)$cashBalance->balance);
        $this->assertEquals(0.00, (float)$capitalBalance->balance);

        // End tenancy context
        tenancy()->end();
    }

    public function test_bug_fixes_and_edge_cases(): void
    {
        $this->tenant = $this->createTestTenant('advanced-accounting');
        tenancy()->initialize($this->tenant);

        // Seed COA and settings
        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        // Create Fiscal Year & Period
        $fiscalYear = FiscalYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
            'is_current' => true,
        ]);

        FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);

        $postingService = app(PostingService::class);
        $settings = AccountingSetting::firstOrFail();

        // Create dummy supplier, warehouse, and customer
        $supplier = \App\Models\Supplier::create([
            'name'  => 'مورد تجريبي',
            'code'  => 'SUP-TEST-001',
            'email' => 'supplier@test.com',
            'phone' => '123456',
        ]);

        $warehouse = \App\Models\Warehouse::create([
            'name'   => 'مخزن رئيسي',
            'code'   => 'WH-TEST-01',
            'status' => 'active',
        ]);

        $customer = \App\Models\Customer::create([
            'name'    => 'عميل تجريبي',
            'code'    => 'CUST-TEST-001',
            'email'   => 'customer@test.com',
            'phone'   => '654321',
            'balance' => 0,
        ]);

        // ==========================================
        // 1. Test Purchase Invoice other_charges with capitalize_freight = false
        // ==========================================
        $settings->update([
            'capitalize_freight'  => false,
            'auto_post_invoices'  => true,
            'auto_post_payments'  => true,
        ]);

        $purchaseInvoice = \App\Models\PurchaseInvoice::create([
            'invoice_number' => 'PINV-2026-0001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-03',
            'subtotal' => 100.00,
            'discount_amount' => 0.00,
            'tax_amount' => 14.00,
            'shipping_cost' => 20.00,
            'other_charges' => 10.00,
            'total' => 144.00,
            'status' => 'confirmed',
        ]);

        $je = $postingService->postPurchaseInvoice($purchaseInvoice);

        $this->assertNotNull($je);
        $this->assertEquals(JournalEntryStatus::POSTED, $je->status);
        $this->assertEquals(144.00, (float)$je->total_debit);
        $this->assertEquals(144.00, (float)$je->total_credit);

        // Verify other_charges debited to 5290
        $miscExpenseAccount = Account::where('code', '5290')->firstOrFail();
        $otherChargesLine = $je->lines->where('account_id', $miscExpenseAccount->id)->first();
        $this->assertNotNull($otherChargesLine);
        $this->assertEquals(10.00, (float)$otherChargesLine->debit);

        // ==========================================
        // 2. Test Sales Return splits VAT and discounts
        // ==========================================
        $salesInvoice = \App\Models\SalesInvoice::create([
            'invoice_number' => 'SINV-2026-0001',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-03',
            'subtotal' => 200.00,
            'total' => 228.00,
            'status' => 'confirmed',
        ]);

        $salesReturn = \App\Models\SalesReturn::create([
            'return_number' => 'RET-2026-0001',
            'sales_invoice_id' => $salesInvoice->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'return_date' => '2026-07-03',
            'subtotal' => 100.00,
            'discount_amount' => 10.00,
            'tax_amount' => 12.60,
            'total' => 102.60,
            'status' => 'confirmed',
        ]);

        $returnJe = $postingService->postSalesReturn($salesReturn);

        $this->assertNotNull($returnJe);
        $this->assertEquals(JournalEntryStatus::POSTED, $returnJe->status);
        $this->assertEquals(112.60, (float)$returnJe->total_debit);
        $this->assertEquals(112.60, (float)$returnJe->total_credit);

        // Verify Sales Returns account (4900) got subtotal (100.00)
        $returnsAccount = Account::where('code', '4900')->firstOrFail();
        $returnLine = $returnJe->lines->where('account_id', $returnsAccount->id)->first();
        $this->assertNotNull($returnLine);
        $this->assertEquals(100.00, (float)$returnLine->debit);

        // Verify Sales Discount account (4800) got discount_amount (10.00)
        $discountLine = $returnJe->lines->where('account_id', $settings->sales_discount_account_id)->first();
        $this->assertNotNull($discountLine);
        $this->assertEquals(10.00, (float)$discountLine->credit);

        // Verify VAT Output (2210) got tax_amount (12.60)
        $vatOutputAccount = Account::where('code', '2210')->firstOrFail();
        $vatLine = $returnJe->lines->where('account_id', $vatOutputAccount->id)->first();
        $this->assertNotNull($vatLine);
        $this->assertEquals(12.60, (float)$vatLine->debit);

        // ==========================================
        // 3. Test Overdue Reminders command runs successfully
        // ==========================================
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $exitCode = \Illuminate\Support\Facades\Artisan::call('accounting:remind-overdue');
        $this->assertEquals(0, $exitCode);

        tenancy()->end();
    }
}

