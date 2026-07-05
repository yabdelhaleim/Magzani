<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\AccountBalance;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\AccountingAuditLog;
use App\Services\Accounting\JournalEntryService;
use App\Services\Accounting\PartnerLedgerService;
use App\Services\Accounting\AccountingAuditService;
use App\Models\AccountingSetting;
use Tests\TestCase;

class PartnerLedgerTest extends TestCase
{
    protected $tenant;
    protected $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        Plan::query()->delete();

        Plan::create([
            'slug' => 'advanced-accounting',
            'name' => 'باقة الحسابات المتقدمة',
            'price' => 99.00,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced'],
            'is_active' => true,
        ]);
    }

    protected function createTestTenant()
    {
        $this->tenantId = 't-acc-' . uniqid();
        $tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => 'advanced-accounting',
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

    public function test_partner_ledger_and_audit_trail_flow(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        // Seed data
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

        $fiscalPeriod = FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);

        // Create Customer & Supplier
        $customer = Customer::create([
            'name' => 'العميل المميز',
            'code' => 'CUST-007',
            'email' => 'client@test.com',
            'phone' => '01000000000',
            'balance' => 0,
        ]);

        $supplier = Supplier::create([
            'name' => 'المورد الأصلي',
            'code' => 'SUP-007',
            'email' => 'supplier@test.com',
            'phone' => '02000000000',
            'balance' => 0,
        ]);

        $settings = AccountingSetting::firstOrFail();
        $journalService = app(JournalEntryService::class);
        $partnerService = app(PartnerLedgerService::class);
        $auditService = app(AccountingAuditService::class);

        // 1. Post entry for Customer (Debit Accounts Receivable)
        $entry1 = $journalService->createAndPost([
            'entry_date' => '2026-07-05',
            'description' => 'فاتورة مبيعات رقم 1',
            'source_type' => 'sales_invoice',
            'source_id' => 1,
            'source_event_key' => 'sales:1',
            'lines' => [
                ['account_id' => $settings->ar_account_id, 'debit' => 1000.00, 'credit' => 0, 'description' => 'مبيعات لعميل', 'party_type' => 'customer', 'party_id' => $customer->id],
                ['account_id' => $settings->sales_revenue_account_id, 'debit' => 0, 'credit' => 1000.00, 'description' => 'إيراد مبيعات'],
            ],
        ]);

        // 2. Post entry for Supplier (Credit Accounts Payable)
        $entry2 = $journalService->createAndPost([
            'entry_date' => '2026-07-06',
            'description' => 'فاتورة شراء رقم 1',
            'source_type' => 'purchase_invoice',
            'source_id' => 1,
            'source_event_key' => 'purchase:1',
            'lines' => [
                ['account_id' => $settings->inventory_account_id, 'debit' => 800.00, 'credit' => 0, 'description' => 'شراء بضاعة للمخزن'],
                ['account_id' => $settings->ap_account_id, 'debit' => 0, 'credit' => 800.00, 'description' => 'مديونية مورد', 'party_type' => 'supplier', 'party_id' => $supplier->id],
            ],
        ]);

        // Verify Customer ledger
        $custLedger = $partnerService->getLedger('customer', $customer->id, '2026-07-01', '2026-07-10');
        $this->assertEquals(0, $custLedger['opening_balance']);
        $this->assertEquals(1000.00, $custLedger['closing_balance']);
        $this->assertCount(1, $custLedger['lines']);
        $this->assertEquals(1000.00, $custLedger['lines'][0]['debit']);

        // Verify Supplier ledger
        $suppLedger = $partnerService->getLedger('supplier', $supplier->id, '2026-07-01', '2026-07-10');
        $this->assertEquals(0, $suppLedger['opening_balance']);
        $this->assertEquals(800.00, $suppLedger['closing_balance']);
        $this->assertCount(1, $suppLedger['lines']);
        $this->assertEquals(800.00, $suppLedger['lines'][0]['credit']);

        // 3. Write dummy Audit Log and test Audit Service
        $user = User::create([
            'name' => 'المشرف العام',
            'email' => 'admin@magzany.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        AccountingAuditLog::create([
            'user_id' => $user->id,
            'action' => 'posted',
            'auditable_type' => JournalEntry::class,
            'auditable_id' => $entry1->id,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'posted'],
            'ip_address' => '127.0.0.1',
        ]);

        $auditLogs = $auditService->getLogs(['action' => 'posted']);
        $this->assertGreaterThan(0, $auditLogs->total());
        $this->assertEquals('posted', $auditLogs->items()[0]->action);
    }
}
