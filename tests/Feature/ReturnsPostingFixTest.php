<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\SalesReturn;
use App\Models\PurchaseReturn;
use App\Services\Accounting\PostingService;
use App\Models\AccountingSetting;
use Tests\TestCase;

class ReturnsPostingFixTest extends TestCase
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

    public function test_sales_and_purchase_returns_posting_fix(): void
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

        // Create test user and log them in
        $user = User::create([
            'id' => 1,
            'name' => 'المشرف العام',
            'email' => 'admin@magzany.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        // Create Warehouse
        $warehouse = Warehouse::create([
            'name' => 'مخزن الجيزة للإنتاج',
            'code' => 'WH-GIZA-01',
            'is_active' => true,
        ]);

        // Create Customer & Supplier
        $customer = Customer::create([
            'name' => 'شركة النيل للإنشاءات',
            'code' => 'CUST-NILE',
            'phone' => '0100000000',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'شركة مصانع الأخشاب الوطنية',
            'code' => 'SUP-NATIONAL',
            'phone' => '0111111111',
            'is_active' => true,
        ]);

        $settings = AccountingSetting::firstOrFail();
        $settings->update([
            'auto_post_invoices' => true,
        ]);

        $postingService = app(PostingService::class);

        // Create dummy Sales Invoice
        $salesInvoice = SalesInvoice::create([
            'invoice_number' => 'SINV-2026-0001',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-10',
            'subtotal' => 1000.00,
            'total' => 1026.00,
            'status' => 'confirmed',
        ]);

        // Create dummy Purchase Invoice
        $purchaseInvoice = PurchaseInvoice::create([
            'invoice_number' => 'PINV-2026-0001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-10',
            'subtotal' => 2000.00,
            'total' => 2052.00,
            'status' => 'confirmed',
        ]);

        // 1. Test Sales Return Posting
        // Subtotal = 1000.00
        // Discount = 100.00
        // Tax = 126.00
        // Total = 1000.00 - 100.00 + 126.00 = 1026.00
        $salesReturn = SalesReturn::create([
            'return_number' => 'SR-2026-0001',
            'sales_invoice_id' => $salesInvoice->id,
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'return_date' => '2026-07-15',
            'subtotal' => 1000.00,
            'discount_amount' => 100.00,
            'tax_amount' => 126.00,
            'total' => 1026.00,
            'status' => 'confirmed',
        ]);

        $salesEntry = $postingService->postSalesReturn($salesReturn, 0);
        $this->assertNotNull($salesEntry);
        $salesEntry->load('lines');

        // Verify total debit = total credit = 1126.00
        // Debits: Returns (4900): 1000.00 + VAT (2210): 126.00 = 1126.00
        // Credits: AR (1210): 1026.00 + Discount (4800): 100.00 = 1126.00
        $this->assertEquals(1126.00, (float)$salesEntry->total_debit);
        $this->assertEquals(1126.00, (float)$salesEntry->total_credit);

        // Check specific lines
        $returnsLine = $salesEntry->lines->firstWhere('account_id', Account::where('code', '4900')->value('id'));
        $this->assertNotNull($returnsLine);
        $this->assertEquals(1000.00, (float)$returnsLine->debit);

        $discountLine = $salesEntry->lines->firstWhere('account_id', $settings->sales_discount_account_id);
        $this->assertNotNull($discountLine);
        $this->assertEquals(100.00, (float)$discountLine->credit);

        $arLine = $salesEntry->lines->firstWhere('account_id', $settings->ar_account_id);
        $this->assertNotNull($arLine);
        $this->assertEquals(1026.00, (float)$arLine->credit);

        // 2. Test Purchase Return Posting
        // Subtotal = 2000.00
        // Discount = 200.00
        // Tax = 252.00
        // Total = 2000.00 - 200.00 + 252.00 = 2052.00
        $purchaseReturn = PurchaseReturn::create([
            'return_number' => 'PR-2026-0001',
            'purchase_invoice_id' => $purchaseInvoice->id,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'return_date' => '2026-07-15',
            'subtotal' => 2000.00,
            'discount_amount' => 200.00,
            'tax_amount' => 252.00,
            'total' => 2052.00,
            'status' => 'confirmed',
        ]);

        $purchaseEntry = $postingService->postPurchaseReturn($purchaseReturn);
        $this->assertNotNull($purchaseEntry);
        $purchaseEntry->load('lines');

        // Verify total debit = total credit = 2052.00
        // Debits: AP (2110): 2052.00
        // Credits: Inventory (1310): 1800.00 (net) + VAT Input (1320): 252.00 = 2052.00
        $this->assertEquals(2052.00, (float)$purchaseEntry->total_debit);
        $this->assertEquals(2052.00, (float)$purchaseEntry->total_credit);

        // Check specific lines
        $apLine = $purchaseEntry->lines->firstWhere('account_id', $settings->ap_account_id);
        $this->assertNotNull($apLine);
        $this->assertEquals(2052.00, (float)$apLine->debit);

        $invLine = $purchaseEntry->lines->firstWhere('account_id', $settings->inventory_account_id);
        $this->assertNotNull($invLine);
        $this->assertEquals(1800.00, (float)$invLine->credit);

        $vatInputLine = $purchaseEntry->lines->firstWhere('account_id', $settings->tax_account_input_id);
        $this->assertNotNull($vatInputLine);
        $this->assertEquals(252.00, (float)$vatInputLine->credit);
    }
}
