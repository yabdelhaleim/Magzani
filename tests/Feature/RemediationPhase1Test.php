<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\WoodStock;
use App\Models\SalesInvoice;
use App\Models\PurchaseInvoice;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\CashTransaction;
use App\Services\Accounting\PostingService;
use App\Services\Accounting\JournalEntryService;
use App\Services\ManufacturingOrderService;
use App\Services\InvoiceService;
use App\Models\AccountingSetting;
use App\Enums\JournalEntryStatus;
use Tests\TestCase;

class RemediationPhase1Test extends TestCase
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
        $this->tenantId = 't-remedy-' . uniqid();
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

    public function test_remediation_phase1_flow(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        // Run seeders
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

        // Create user and log in
        $user = User::create([
            'id' => 1,
            'name' => 'المشرف العام',
            'email' => 'admin@magzany.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        // Verify account 2140 exists in chart of accounts
        $accruedAccount = Account::where('code', '2140')->first();
        $this->assertNotNull($accruedAccount);
        $this->assertEquals('مصاريف تشغيل مستحقة', $accruedAccount->name_ar);

        // Verify settings has accrued_overheads_account_id mapped to 2140
        $settings = AccountingSetting::firstOrFail();
        $this->assertEquals($accruedAccount->id, $settings->accrued_overheads_account_id);

        // Enable auto posting features
        $settings->update([
            'auto_post_invoices' => true,
            'auto_post_payments' => true,
            'auto_post_expenses' => true,
            'auto_post_manufacturing' => true,
        ]);

        // Create warehouse, product and wood stock
        $warehouse = Warehouse::create([
            'name' => 'المخزن الرئيسي للتصنيع',
            'code' => 'WH-MAIN-MFG',
            'is_active' => true,
        ]);

        $woodStock = WoodStock::create([
            'thickness_cm' => 2.50,
            'width_cm' => 10.00,
            'length_cm' => 300.00,
            'quantity' => 1000,
            'unit_cost' => 2000.00, // per m3
            'received_at' => '2026-07-01',
        ]);

        $product = Product::create([
            'name' => 'طبلية خشبية قياسية',
            'code' => 'PROD-PALLET',
            'sku' => 'PALLET-STD',
            'barcode' => '11223344',
            'product_type' => 'manufactured',
            'is_manufactured' => true,
            'base_unit' => 'piece',
            'category' => 'Pallets',
            'purchase_price' => 150.00,
            'selling_price' => 200.00,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'عميل تجريبي',
            'code' => 'CUST-TEST',
            'phone' => '0100000000',
            'is_active' => true,
        ]);

        $supplier = Supplier::create([
            'name' => 'مورد تجريبي',
            'code' => 'SUPP-TEST',
            'phone' => '0111111111',
            'is_active' => true,
        ]);

        $orderService = app(ManufacturingOrderService::class);
        $postingService = app(PostingService::class);
        $journalService = app(JournalEntryService::class);
        $invoiceService = app(InvoiceService::class);

        // ==========================================
        // 1. TEST GAP 1: Accrued Overheads Posting
        // ==========================================
        
        // Create Manufacturing Order using the service to populate all calculated properties
        $order = $orderService->createOrder([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_produced' => 10,
            'warehouse_id' => $warehouse->id,
            'waste_cost' => 0,
            'labor_cost' => 30.00, // per unit
            'nails_cost' => 0,
            'tips_cost' => 0,
            'transport_cost' => 20.00, // per unit
            'fumigation_cost' => 0,
            'profit_margin' => 42.86,
            'components' => [
                [
                    'wood_stock_id' => $woodStock->id,
                    'quantity' => 15,
                    'thickness_cm' => 2.5,
                    'width_cm' => 10,
                    'length_cm' => 120,
                    'price_per_cubic_meter' => 2000.00,
                    'component_type' => 'موسكي',
                ]
            ]
        ]);

        // Fetch Cash Account before confirmation (should be 0)
        $cashAccount = Account::find($settings->cash_account_id);
        $initialCashBalance = (float) $cashAccount->balance;

        // Confirm Manufacturing Order
        $order = $orderService->confirmOrder($order);
        $this->assertEquals('confirmed', $order->status);

        // Verify that Cash balance is unaffected (Timing Mismatch solved!)
        $cashAccount->refresh();
        $this->assertEquals($initialCashBalance, (float) $cashAccount->balance);

        // Verify the Journal Entry posted for confirmation
        $mfgEntry = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('status', JournalEntryStatus::POSTED)
            ->first();

        $this->assertNotNull($mfgEntry);
        $mfgEntry->load('lines');

        // Total debits/credits must be 1400.00
        $this->assertEquals(1400.00, (float)$mfgEntry->total_debit);
        $this->assertEquals(1400.00, (float)$mfgEntry->total_credit);

        // Verify WIP (1350) is debited with 1400.00
        $wipLine = $mfgEntry->lines->firstWhere('account_id', $settings->wip_account_id);
        $this->assertNotNull($wipLine);
        $this->assertEquals(1400.00, (float)$wipLine->debit);

        // Verify Inventory (1310) is credited with 900.00
        $invLine = $mfgEntry->lines->firstWhere('account_id', $settings->inventory_account_id);
        $this->assertNotNull($invLine);
        $this->assertEquals(900.00, (float)$invLine->credit);

        // Verify Accrued Production Expenses (2140) is credited with 500.00
        $accruedLine = $mfgEntry->lines->firstWhere('account_id', $settings->accrued_overheads_account_id);
        $this->assertNotNull($accruedLine);
        $this->assertEquals(500.00, (float)$accruedLine->credit);

        // Simulate payment voucher (Withdrawal from cash to settle the accrued expense)
        // We will add a cash transaction (withdrawal) of 500.00, setting 2140 as the counter account.
        $cashTransaction = CashTransaction::create([
            'transaction_type' => 'withdrawal',
            'amount' => 500.00,
            'description' => 'دفع أجر عمال وشحن تصنيع',
            'category' => 'salaries',
            'transaction_date' => '2026-07-20',
            'counter_account_id' => $accruedAccount->id,
            'created_by' => $user->id,
        ]);

        $withdrawalEntry = $postingService->postCashTransaction($cashTransaction);
        $this->assertNotNull($withdrawalEntry);
        $withdrawalEntry->load('lines');

        // Check lines of the payment:
        // DR Accrued Production Expenses (2140) -> 500.00
        // CR Cash (1110) -> 500.00
        $payAccruedLine = $withdrawalEntry->lines->firstWhere('account_id', $accruedAccount->id);
        $this->assertNotNull($payAccruedLine);
        $this->assertEquals(500.00, (float)$payAccruedLine->debit);

        $payCashLine = $withdrawalEntry->lines->firstWhere('account_id', $settings->cash_account_id);
        $this->assertNotNull($payCashLine);
        $this->assertEquals(500.00, (float)$payCashLine->credit);


        // ==========================================
        // 2. TEST GAP 5: Reversal Templates
        // ==========================================

        // --- Case A: Sales Invoice Cancellation Reversal ---
        $salesInvoice = SalesInvoice::create([
            'invoice_number' => 'SINV-REMEDY-01',
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-20',
            'subtotal' => 1000.00,
            'tax_amount' => 140.00,
            'total' => 1140.00,
            'status' => 'confirmed',
        ]);

        $salesEntry = $postingService->postSalesInvoice($salesInvoice);
        $this->assertNotNull($salesEntry);
        $salesInvoice->update(['journal_entry_id' => $salesEntry->id]);

        // Cancel Sales Invoice
        $this->assertTrue($invoiceService->cancelSalesInvoice($salesInvoice->id, 'تغيير الطلب'));

        // Refresh and check links
        $salesEntry->refresh();
        $this->assertEquals(JournalEntryStatus::REVERSED, $salesEntry->status);
        $this->assertNotNull($salesEntry->reversed_entry_id);

        $reversalSalesEntry = JournalEntry::findOrFail($salesEntry->reversed_entry_id);
        $this->assertEquals(JournalEntryStatus::POSTED, $reversalSalesEntry->status);
        $this->assertEquals('reversal', $reversalSalesEntry->source_type);
        $this->assertEquals($salesEntry->id, $reversalSalesEntry->reversal_of_id);
        $this->assertEquals($salesEntry->id, $reversalSalesEntry->source_id);

        // Verify reversed lines
        $reversalSalesEntry->load('lines');
        $this->assertEquals(1140.00, (float)$reversalSalesEntry->total_debit);
        $this->assertEquals(1140.00, (float)$reversalSalesEntry->total_credit);


        // --- Case B: Purchase Invoice Cancellation Reversal ---
        $purchaseInvoice = PurchaseInvoice::create([
            'invoice_number' => 'PINV-REMEDY-01',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'invoice_date' => '2026-07-20',
            'subtotal' => 2000.00,
            'tax_amount' => 280.00,
            'total' => 2280.00,
            'status' => 'confirmed',
        ]);

        $purchaseEntry = $postingService->postPurchaseInvoice($purchaseInvoice);
        $this->assertNotNull($purchaseEntry);
        $purchaseInvoice->update(['journal_entry_id' => $purchaseEntry->id]);

        // Cancel Purchase Invoice
        $this->assertTrue($invoiceService->cancelPurchaseInvoice($purchaseInvoice->id, 'تراجع المورد'));

        // Refresh and check links
        $purchaseEntry->refresh();
        $this->assertEquals(JournalEntryStatus::REVERSED, $purchaseEntry->status);
        $this->assertNotNull($purchaseEntry->reversed_entry_id);

        $reversalPurchaseEntry = JournalEntry::findOrFail($purchaseEntry->reversed_entry_id);
        $this->assertEquals(JournalEntryStatus::POSTED, $reversalPurchaseEntry->status);
        $this->assertEquals('reversal', $reversalPurchaseEntry->source_type);
        $this->assertEquals($purchaseEntry->id, $reversalPurchaseEntry->reversal_of_id);

        // Verify reversed lines
        $reversalPurchaseEntry->load('lines');
        $this->assertEquals(2280.00, (float)$reversalPurchaseEntry->total_debit);
        $this->assertEquals(2280.00, (float)$reversalPurchaseEntry->total_credit);


        // --- Case C: Manufacturing Order Cancellation Reversal ---
        // Cancel the confirmed manufacturing order we created earlier
        $order = $orderService->cancelOrder($order, 'خطأ في التخطيط');
        $this->assertEquals('cancelled', $order->status);

        // Refresh manufacturing confirmation entry
        $mfgEntry->refresh();
        $this->assertEquals(JournalEntryStatus::REVERSED, $mfgEntry->status);
        $this->assertNotNull($mfgEntry->reversed_entry_id);

        $reversalMfgEntry = JournalEntry::findOrFail($mfgEntry->reversed_entry_id);
        $this->assertEquals(JournalEntryStatus::POSTED, $reversalMfgEntry->status);
        $this->assertEquals('reversal', $reversalMfgEntry->source_type);
        $this->assertEquals($mfgEntry->id, $reversalMfgEntry->reversal_of_id);
        $this->assertEquals($mfgEntry->id, $reversalMfgEntry->source_id);

        // Verify reversed lines
        $reversalMfgEntry->load('lines');
        $this->assertEquals(1400.00, (float)$reversalMfgEntry->total_debit);
        $this->assertEquals(1400.00, (float)$reversalMfgEntry->total_credit);
    }
}
