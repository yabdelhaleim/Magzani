<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use App\Models\MaterialBatch;
use App\Models\MaterialDispensing;
use App\Models\FiscalYear;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\ManufacturingOrder;
use App\Services\ManufacturingOrderService;
use App\Models\AccountingSetting;
use Tests\TestCase;

class GenericManufacturingTest extends TestCase
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

    /**
     * Replicate legacy wood scenario as generic material:
     * 1 لوح = 0.024 م3 (UoM conversion)
     * quantity = 500 boards
     * unit_cost = 1000 per m3 (meaning cost per board is 0.024 * 1000 = 24.00)
     * total cost of batch = 500 * 24.00 = 12000.00
     */
    public function test_legacy_wood_scenario_replicated_as_generic_material(): void
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

        FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);

        $warehouse = \App\Models\Warehouse::create([
            'name' => 'مخزن جاو الرئيسي',
            'code' => 'WH-JOO',
            'is_active' => true,
        ]);

        // Create Units of Measure
        $m3 = UnitOfMeasure::create(['name' => 'متر مكعب', 'code' => 'm3', 'type' => 'volume', 'is_active' => true]);
        $board = UnitOfMeasure::create(['name' => 'لوح خشب', 'code' => 'board', 'type' => 'volume', 'is_active' => true]);

        // Conversion: 1 board = 0.024 m3
        UomConversion::create([
            'from_uom_id' => $board->id,
            'to_uom_id' => $m3->id,
            'factor' => 0.024,
        ]);

        // Create generic raw material product (Wood Block)
        $woodProduct = Product::create([
            'name' => 'لوح خشب موسكي',
            'code' => 'RAW-MOSKY',
            'sku' => 'MOSKY-BOARD',
            'product_type' => 'raw_material',
            'base_unit' => 'm3',
            'purchase_price' => 1000.00, // per m3
            'selling_price' => 1200.00,
            'is_active' => true,
        ]);

        // Create material batch: 500 boards at cost 24.00 per board (which corresponds to 1000.00 per m3)
        $batch = app(\App\Services\MaterialStockService::class)->createStock([
            'product_id' => $woodProduct->id,
            'warehouse_id' => $warehouse->id,
            'uom_id' => $board->id,
            'quantity' => 500,
            'unit_cost' => 24.00, // 0.024 * 1000
            'received_at' => '2026-07-01',
        ]);

        // Create target finished product
        $palletProduct = Product::create([
            'name' => 'طبلية خشبية مصنعة',
            'code' => 'PROD-PALLET',
            'sku' => 'PALLET-GEN',
            'product_type' => 'manufactured',
            'is_manufactured' => true,
            'base_unit' => 'piece',
            'purchase_price' => 500.00,
            'selling_price' => 700.00,
            'is_active' => true,
        ]);

        // Setup user
        $user = User::create([
            'id' => 1,
            'name' => 'المشرف العام',
            'email' => 'admin@magzany.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        // Map settings
        $settings = AccountingSetting::firstOrFail();
        $settings->update([
            'auto_post_manufacturing' => true,
            'accrued_overheads_account_id' => Account::where('code', '2140')->value('id') ?? $settings->cash_account_id,
        ]);

        $orderService = app(ManufacturingOrderService::class);

        // Create order: we require 15 boards per pallet.
        // Component cost per unit produced = 15 boards * 24.00 = 360.00
        // Overheads: labor_cost = 40.00, transport_cost = 10.00
        // Total cost per unit produced = 360.00 + 50.00 = 410.00
        // Quantity produced = 10 pallets
        // Total components cost for order = 10 * 360.00 = 3600.00
        // Total overheads for order = 10 * 50.00 = 500.00
        // Grand total cost = 10 * 410.00 = 4100.00
        $order = $orderService->createOrder([
            'product_id' => $palletProduct->id,
            'product_name' => 'طبلية خشبية مصنعة',
            'quantity_produced' => 10,
            'warehouse_id' => $warehouse->id,
            'labor_cost' => 40.00,
            'transport_cost' => 10.00,
            'components' => [
                [
                    'material_batch_id' => $batch->id,
                    'uom_id' => $board->id,
                    'quantity' => 15,
                    'unit_cost' => 24.00,
                    'component_name' => 'لوح خشب موسكي',
                ]
            ]
        ]);

        // Assert order values
        $this->assertEquals(410.00, (float) $order->cost_per_unit);
        $this->assertEquals(4100.00, (float) $order->total_cost);

        // Confirm order
        $confirmed = $orderService->confirmOrder($order);
        $this->assertEquals('confirmed', $confirmed->status);

        // Assert remaining quantity in batch: 500 - (15 * 10) = 350
        $batch->refresh();
        $this->assertEquals(350, (float) $batch->remaining_qty);

        // Verify Journal Entry posted
        $confirmEntry = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('source_event_key', "manufacturing:{$order->id}:confirmed")
            ->firstOrFail();

        $confirmEntry->load('lines');
        
        // WIP (DR): 4100.00
        $wipLine = $confirmEntry->lines->firstWhere('account_id', $settings->wip_account_id);
        $this->assertNotNull($wipLine);
        $this->assertEquals(4100.00, (float) $wipLine->debit);

        // Inventory (CR): 3600.00
        $invLine = $confirmEntry->lines->firstWhere('account_id', $settings->inventory_account_id);
        $this->assertNotNull($invLine);
        $this->assertEquals(3600.00, (float) $invLine->credit);

        // Accrued Production Expenses (CR): 500.00 (Gap 1 check)
        $accruedAccount = Account::where('code', '2140')->firstOrFail();
        $overheadLine = $confirmEntry->lines->firstWhere('account_id', $accruedAccount->id);
        $this->assertNotNull($overheadLine);
        $this->assertEquals(500.00, (float) $overheadLine->credit);

        // Complete order
        $completed = $orderService->completeOrder($order, $warehouse->id);
        $this->assertEquals('completed', $completed->status);

        // Verify WIP -> Finished Goods Journal Entry
        $completeEntry = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('source_event_key', "manufacturing:{$order->id}:completed")
            ->firstOrFail();

        $completeEntry->load('lines');
        $finishedInvLine = $completeEntry->lines->where('account_id', $settings->inventory_account_id)->firstWhere('debit', '>', 0);
        $this->assertNotNull($finishedInvLine);
        $this->assertEquals(4100.00, (float) $finishedInvLine->debit);
    }

    /**
     * Test Gap 5: Revert journal entries and release stock on cancellation
     */
    public function test_revert_entry_on_cancel(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        $fiscalYear = FiscalYear::create([
            'name' => '2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_closed' => false, 'is_current' => true
        ]);
        FiscalPeriod::create([
            'fiscal_year_id' => $fiscalYear->id, 'name' => 'يوليو 2026', 'period_number' => 7, 'start_date' => '2026-07-01', 'end_date' => '2026-07-31', 'is_closed' => false
        ]);

        $warehouse = \App\Models\Warehouse::create(['name' => 'مخزن رئيسي', 'code' => 'WH-01', 'is_active' => true]);
        $uom = UnitOfMeasure::create(['name' => 'كيلو', 'code' => 'kg', 'type' => 'weight', 'is_active' => true]);

        $rawProd = Product::create([
            'name' => 'بودرة خام', 'code' => 'RAW-POWDER', 'sku' => 'POWDER-1', 'product_type' => 'raw_material', 'base_unit' => 'kg', 'purchase_price' => 10.00, 'selling_price' => 15.00, 'is_active' => true
        ]);

        $batch = app(\App\Services\MaterialStockService::class)->createStock([
            'product_id' => $rawProd->id, 'warehouse_id' => $warehouse->id, 'uom_id' => $uom->id, 'quantity' => 100, 'unit_cost' => 10.00, 'received_at' => '2026-07-01'
        ]);

        $finishedProd = Product::create([
            'name' => 'منتج معبأ', 'code' => 'PROD-PACK', 'sku' => 'PACK-1', 'product_type' => 'manufactured', 'is_manufactured' => true, 'base_unit' => 'piece', 'purchase_price' => 50.00, 'selling_price' => 75.00, 'is_active' => true
        ]);

        $user = User::create(['id' => 1, 'name' => 'المشرف العام', 'email' => 'admin@magzany.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $this->actingAs($user);

        $settings = AccountingSetting::firstOrFail();
        $settings->update([
            'auto_post_manufacturing' => true,
            'accrued_overheads_account_id' => Account::where('code', '2140')->value('id') ?? $settings->cash_account_id,
        ]);

        $orderService = app(ManufacturingOrderService::class);

        $order = $orderService->createOrder([
            'product_id' => $finishedProd->id,
            'product_name' => 'منتج معبأ',
            'quantity_produced' => 5,
            'warehouse_id' => $warehouse->id,
            'labor_cost' => 20.00,
            'components' => [
                [
                    'material_batch_id' => $batch->id,
                    'uom_id' => $uom->id,
                    'quantity' => 4,
                    'unit_cost' => 10.00,
                    'component_name' => 'بودرة خام',
                ]
            ]
        ]);

        // Confirm
        $orderService->confirmOrder($order);
        $batch->refresh();
        $this->assertEquals(80, (float) $batch->remaining_qty); // 100 - (4 * 5) = 80

        // Cancel
        $cancelled = $orderService->cancelOrder($order, 'خطأ في الكميات المطلوبة');
        $this->assertEquals('cancelled', $cancelled->status);

        // Assert stock was returned
        $batch->refresh();
        $this->assertEquals(100, (float) $batch->remaining_qty);

        // Verify revert entry exists (Gap 5 check)
        $originalEntry = JournalEntry::where('source_type', 'manufacturing')
            ->where('source_id', $order->id)
            ->where('source_event_key', "manufacturing:{$order->id}:confirmed")
            ->firstOrFail();

        $this->assertNotNull($originalEntry->reversed_entry_id);

        $revertEntry = JournalEntry::findOrFail($originalEntry->reversed_entry_id);
        $this->assertNotNull($revertEntry);
        $this->assertStringContainsString('عكس', $revertEntry->description);
    }

    /**
     * Test confirming an order with insufficient batch stock throws an exception
     */
    public function test_insufficient_stock_throws_exception(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        $warehouse = \App\Models\Warehouse::create(['name' => 'مخزن رئيسي', 'code' => 'WH-01', 'is_active' => true]);
        $uom = UnitOfMeasure::create(['name' => 'كيلو', 'code' => 'kg', 'type' => 'weight', 'is_active' => true]);

        $rawProd = Product::create([
            'name' => 'بودرة خام', 'code' => 'RAW-POWDER', 'sku' => 'POWDER-1', 'product_type' => 'raw_material', 'base_unit' => 'kg', 'purchase_price' => 10.00, 'selling_price' => 15.00, 'is_active' => true
        ]);

        $batch = app(\App\Services\MaterialStockService::class)->createStock([
            'product_id' => $rawProd->id, 'warehouse_id' => $warehouse->id, 'uom_id' => $uom->id, 'quantity' => 10, 'unit_cost' => 10.00, 'received_at' => '2026-07-01'
        ]);

        $finishedProd = Product::create([
            'name' => 'منتج معبأ', 'code' => 'PROD-PACK', 'sku' => 'PACK-1', 'product_type' => 'manufactured', 'is_manufactured' => true, 'base_unit' => 'piece', 'purchase_price' => 50.00, 'selling_price' => 75.00, 'is_active' => true
        ]);

        $user = User::create(['id' => 1, 'name' => 'المشرف العام', 'email' => 'admin@magzany.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $this->actingAs($user);

        $orderService = app(ManufacturingOrderService::class);

        $order = $orderService->createOrder([
            'product_id' => $finishedProd->id,
            'product_name' => 'منتج معبأ',
            'quantity_produced' => 5,
            'warehouse_id' => $warehouse->id,
            'components' => [
                [
                    'material_batch_id' => $batch->id,
                    'uom_id' => $uom->id,
                    'quantity' => 3, // Requires 3 * 5 = 15, but batch only has 10!
                    'unit_cost' => 10.00,
                    'component_name' => 'بودرة خام',
                ]
            ]
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('تتجاوز الرصيد المتبقي');

        $orderService->confirmOrder($order);
    }

    /**
     * Test multiple consecutive dispensings from the same batch
     */
    public function test_multiple_consecutive_dispensings(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        $warehouse = \App\Models\Warehouse::create(['name' => 'مخزن رئيسي', 'code' => 'WH-01', 'is_active' => true]);
        $uom = UnitOfMeasure::create(['name' => 'كيلو', 'code' => 'kg', 'type' => 'weight', 'is_active' => true]);

        $rawProd = Product::create([
            'name' => 'بودرة خام', 'code' => 'RAW-POWDER', 'sku' => 'POWDER-1', 'product_type' => 'raw_material', 'base_unit' => 'kg', 'purchase_price' => 10.00, 'selling_price' => 15.00, 'is_active' => true
        ]);

        $batch = app(\App\Services\MaterialStockService::class)->createStock([
            'product_id' => $rawProd->id, 'warehouse_id' => $warehouse->id, 'uom_id' => $uom->id, 'quantity' => 100, 'unit_cost' => 10.00, 'received_at' => '2026-07-01'
        ]);

        $finishedProd = Product::create([
            'name' => 'منتج معبأ', 'code' => 'PROD-PACK', 'sku' => 'PACK-1', 'product_type' => 'manufactured', 'is_manufactured' => true, 'base_unit' => 'piece', 'purchase_price' => 50.00, 'selling_price' => 75.00, 'is_active' => true
        ]);

        $user = User::create(['id' => 1, 'name' => 'المشرف العام', 'email' => 'admin@magzany.com', 'password' => bcrypt('password'), 'role' => 'admin']);
        $this->actingAs($user);

        $orderService = app(ManufacturingOrderService::class);

        // First order: requires 4 * 5 = 20 kg
        $order1 = $orderService->createOrder([
            'product_id' => $finishedProd->id,
            'product_name' => 'منتج معبأ',
            'quantity_produced' => 5,
            'warehouse_id' => $warehouse->id,
            'components' => [
                [
                    'material_batch_id' => $batch->id,
                    'uom_id' => $uom->id,
                    'quantity' => 4,
                    'unit_cost' => 10.00,
                    'component_name' => 'بودرة خام',
                ]
            ]
        ]);
        $orderService->confirmOrder($order1);
        $batch->refresh();
        $this->assertEquals(80, (float) $batch->remaining_qty);

        // Second order: requires 2 * 15 = 30 kg
        $order2 = $orderService->createOrder([
            'product_id' => $finishedProd->id,
            'product_name' => 'منتج معبأ 2',
            'quantity_produced' => 15,
            'warehouse_id' => $warehouse->id,
            'components' => [
                [
                    'material_batch_id' => $batch->id,
                    'uom_id' => $uom->id,
                    'quantity' => 2,
                    'unit_cost' => 10.00,
                    'component_name' => 'بودرة خام',
                ]
            ]
        ]);
        $orderService->confirmOrder($order2);
        $batch->refresh();
        $this->assertEquals(50, (float) $batch->remaining_qty);
    }

    /**
     * Test MaterialBatch scopeWithStock returns only active batches
     */
    public function test_material_batch_scope_with_stock(): void
    {
        $this->tenant = $this->createTestTenant();
        tenancy()->initialize($this->tenant);

        $warehouse = \App\Models\Warehouse::create(['name' => 'مخزن رئيسي', 'code' => 'WH-01', 'is_active' => true]);
        $uom = UnitOfMeasure::create(['name' => 'كيلو', 'code' => 'kg', 'type' => 'weight', 'is_active' => true]);
        $rawProd = Product::create([
            'name' => 'بودرة خام', 'code' => 'RAW-POWDER', 'sku' => 'POWDER-1', 'product_type' => 'raw_material', 'base_unit' => 'kg', 'purchase_price' => 10.00, 'selling_price' => 15.00, 'is_active' => true
        ]);

        // Batch with stock
        $batch1 = app(\App\Services\MaterialStockService::class)->createStock([
            'product_id' => $rawProd->id, 'warehouse_id' => $warehouse->id, 'uom_id' => $uom->id, 'quantity' => 10, 'unit_cost' => 10.00, 'received_at' => '2026-07-01'
        ]);

        // Batch without stock (exhausted)
        $batch2 = app(\App\Services\MaterialStockService::class)->createStock([
            'product_id' => $rawProd->id, 'warehouse_id' => $warehouse->id, 'uom_id' => $uom->id, 'quantity' => 0, 'unit_cost' => 10.00, 'received_at' => '2026-07-01'
        ]);

        $batchesWithStock = MaterialBatch::withStock()->get();

        $this->assertTrue($batchesWithStock->contains($batch1));
        $this->assertFalse($batchesWithStock->contains($batch2));
    }
}
