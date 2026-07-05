<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\WoodStock;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\ManufacturingOrder;
use App\Services\ManufacturingOrderService;
use App\Models\AccountingSetting;
use Tests\TestCase;

class ManufacturingCostTest extends TestCase
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

    public function test_manufacturing_accounting_splits_costs(): void
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

        // Create a warehouse
        $warehouse = \App\Models\Warehouse::create([
            'name' => 'المخزن الرئيسي',
            'code' => 'WH-01',
            'is_active' => true,
        ]);

        // Create a wood stock (raw material)
        $woodStock = WoodStock::create([
            'thickness_cm' => 2.50,
            'width_cm' => 10.00,
            'length_cm' => 300.00,
            'quantity' => 1000,
            'unit_cost' => 2000.00, // Cost per cubic meter
            'received_at' => '2026-07-01',
        ]);

        // Create target product template
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

        // Create user and log them in
        $user = User::create([
            'id' => 1,
            'name' => 'المشرف العام',
            'email' => 'admin@magzany.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        // Enable auto posting for manufacturing
        $settings = AccountingSetting::firstOrFail();
        $settings->update([
            'auto_post_manufacturing' => true,
        ]);

        $orderService = app(ManufacturingOrderService::class);

        // 1. Create Manufacturing Order
        // Component details:
        // quantity: 15 pieces
        // dimensions: 2.5cm thickness, 10cm width, 120cm length
        // volume = 15 * 2.5 * 10 * 120 = 45000 cm3 = 0.045 m3
        // price_per_cubic_meter = 2000.00 (from wood stock)
        // components_cost = 0.045 * 2000 = 90.00
        // Overheads: labor_cost = 40.00, transport_cost = 10.00 -> additionalTotal = 50.00
        // cost_per_unit = 90.00 + 50.00 = 140.00
        // quantity_produced = 10 units
        // total_cost = 140.00 * 10 = 1400.00
        // expected: componentsTotalCost = 900.00, overheadsTotalCost = 500.00
        $order = $orderService->createOrder([
            'product_id' => $product->id,
            'product_name' => 'طبلية خشبية قياسية',
            'quantity_produced' => 10,
            'warehouse_id' => $warehouse->id,
            'waste_cost' => 0,
            'labor_cost' => 40.00,
            'nails_cost' => 0,
            'tips_cost' => 0,
            'transport_cost' => 10.00,
            'fumigation_cost' => 0,
            'profit_margin' => 42.86, // (200 - 140) / 140 * 100
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

        // 2. Confirm Manufacturing Order (Deducts stock and posts confirmation GL)
        $confirmed = $orderService->confirmOrder($order);
        $this->assertEquals('confirmed', $confirmed->status);

        // Verify Confirmation Journal Entry posted
        $confirmEntry = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('source_event_key', "manufacturing:{$order->id}:confirmed")
            ->firstOrFail();

        $confirmEntry->load('lines');
        $this->assertCount(3, $confirmEntry->lines);

        // WIP (DR): 1400.00
        $wipLine = $confirmEntry->lines->firstWhere('account_id', $settings->wip_account_id);
        $this->assertNotNull($wipLine);
        $this->assertEquals(1400.00, (float) $wipLine->debit);
        $this->assertEquals(0.00, (float) $wipLine->credit);

        // Inventory (CR): 900.00 (components total cost for 10 units)
        $invLine = $confirmEntry->lines->firstWhere('account_id', $settings->inventory_account_id);
        $this->assertNotNull($invLine);
        $this->assertEquals(0.00, (float) $invLine->debit);
        $this->assertEquals(900.00, (float) $invLine->credit);

        // Cash/Bank or AP (CR): 500.00 (overhead costs for 10 units)
        $overheadLine = $confirmEntry->lines->firstWhere('account_id', $settings->cash_account_id);
        $this->assertNotNull($overheadLine);
        $this->assertEquals(0.00, (float) $overheadLine->debit);
        $this->assertEquals(500.00, (float) $overheadLine->credit);

        // 3. Complete Manufacturing Order (Moves finished goods to stock and posts complete GL)
        $completed = $orderService->completeOrder($order, $warehouse->id);
        $this->assertEquals('completed', $completed->status);

        // Verify Completion Journal Entry posted
        $completeEntry = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('source_event_key', "manufacturing:{$order->id}:completed")
            ->firstOrFail();

        $completeEntry->load('lines');
        $this->assertCount(2, $completeEntry->lines);

        // Inventory (DR): 1400.00
        $finishedInvLine = $completeEntry->lines->where('account_id', $settings->inventory_account_id)->firstWhere('debit', '>', 0);
        $this->assertNotNull($finishedInvLine);
        $this->assertEquals(1400.00, (float) $finishedInvLine->debit);

        // WIP (CR): 1400.00
        $finishedWipLine = $completeEntry->lines->where('account_id', $settings->wip_account_id)->firstWhere('credit', '>', 0);
        $this->assertNotNull($finishedWipLine);
        $this->assertEquals(1400.00, (float) $finishedWipLine->credit);
    }
}
