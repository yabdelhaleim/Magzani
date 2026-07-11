<?php

namespace Tests\Feature\StandardCosting;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ManufacturingCost;
use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderComponent;
use App\Models\AccountingSetting;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Enums\JournalEntryStatus;
use App\Services\ManufacturingOrderService;
use App\Services\StandardCostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Gap 2 — Standard Costing & Cost Variance — Acceptance test suite.
 *
 * Runs all 5 acceptance scenarios from the implementation plan against
 * a single tenant:
 *
 *  1. Tenant WITHOUT standard_costing_enabled → legacy 2-line entry unchanged
 *  2. Tenant WITH standard_costing_enabled, Unfavorable Variance
 *  3. Favorable Variance
 *  4. Zero Variance (exact match)
 *  5. Gap 1 (Accrued Overheads) still works on top of Standard Costing
 *
 * Each test is independent thanks to RefreshDatabase but shares the same
 * tenant context, so the chart of accounts / settings / fiscal period
 * setup runs once.
 */
class Gap2StandardCostingTest extends TestCase
{
    use RefreshDatabase;

    protected string $tenantId = 't-gap2';
    protected $tenant;
    protected $settings;
    protected $varianceAccount;
    protected $wipAccount;
    protected $inventoryAccount;
    protected $warehouse;

    protected function setUp(): void
    {
        parent::setUp();

        // Plan
        Plan::query()->delete();
        Plan::create([
            'slug' => 'gap2-test',
            'name' => 'Gap 2 Test Plan',
            'price' => 0,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced', 'manufacturing'],
            'is_active' => true,
        ]);

        // Tenant
        $this->tenant = Tenant::create([
            'id'   => $this->tenantId,
            'plan_id' => 'gap2-test',
            'is_suspended' => false,
        ]);
        $this->tenant->domains()->create([
            'domain' => $this->tenantId . '.localhost',
        ]);

        tenancy()->initialize($this->tenant);

        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        // Fiscal period
        $fy = \App\Models\FiscalYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_closed' => false,
            'is_current' => true,
        ]);
        \App\Models\FiscalPeriod::create([
            'fiscal_year_id' => $fy->id,
            'name' => 'يوليو 2026',
            'period_number' => 7,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-31',
            'is_closed' => false,
        ]);

        // Resolve accounts once
        $this->varianceAccount  = Account::where('code', '5160')->firstOrFail();
        $this->wipAccount       = Account::where('code', '1350')->firstOrFail();
        $this->inventoryAccount = Account::where('code', '1310')->firstOrFail();

        // Settings: enable auto-post manufacturing
        $this->settings = AccountingSetting::firstOrFail();
        $this->settings->update([
            'auto_post_manufacturing' => true,
            'wip_account_id'          => $this->wipAccount->id,
            'inventory_account_id'    => $this->inventoryAccount->id,
        ]);

        // User
        $user = User::create([
            'name' => 'Gap 2 Tester',
            'email' => 'gap2@test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        // Warehouse
        $this->warehouse = Warehouse::create([
            'name' => 'WH-Test',
            'code' => 'WH-GAP2',
            'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        try {
            tenancy()->end();
            DB::disconnect('tenant');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            $this->tenant->domains()->delete();
            $this->tenant->delete();
        } catch (\Throwable $e) {
            // ignore
        }

        parent::tearDown();
    }

    /**
     * Build a draft→confirmed MO ready to be completed.
     * `actualCostPerUnit` is the total cost we want to land on the order,
     * and `quantityProduced` is the run size.
     *
     * Returns the ManufacturingOrder ready for `completeOrder()`.
     */
    private function buildOrder(float $actualCostPerUnit, int $quantityProduced, ?Product $product = null): ManufacturingOrder
    {
        $product = $product ?? Product::create([
            'code'           => 'P-' . strtoupper(substr(uniqid(), -8)),
            'name'           => 'P-' . uniqid(),
            'sku'            => 'SKU-' . strtoupper(substr(uniqid(), -8)),
            'product_type'   => 'manufactured',
            'is_manufactured'=> true,
            'purchase_price' => $actualCostPerUnit,
            'selling_price'  => $actualCostPerUnit * 1.25,
            'base_unit'      => 'piece',
            'category'       => 'Manufactured Products',
            'is_active'      => true,
        ]);

        // Components split: 60% materials / 40% extra costs (labor/overhead).
        // This lets the variance report show real material vs labor splits.
        $materialPortion = round($actualCostPerUnit * 0.6, 4);
        $laborPortion    = round($actualCostPerUnit * 0.4, 4);

        // The ManufacturingOrderService sums component.total_cost + extras
        // and stores it as cost_per_unit. We pass things in that way.
        $order = app(ManufacturingOrderService::class)->createOrder([
            'product_id'          => $product->id,
            'product_name'        => $product->name,
            'quantity_produced'   => $quantityProduced,
            'components'          => [
                ['component_name' => 'wood', 'component_type' => 'material', 'quantity' => 1, 'unit_cost' => $materialPortion],
            ],
            'extra_costs'         => [
                ['cost_type' => 'labor', 'amount' => $laborPortion, 'description' => 'labor test'],
            ],
            'warehouse_id'        => $this->warehouse->id,
            'profit_margin'       => 25,
            'selling_price_per_unit' => $actualCostPerUnit * 1.25,
        ]);

        // Drop a ManufacturingCost (BOM header) so the standard-cost
        // service has something to resolve. The exact standard is set
        // per-test via setStandardCost().
        ManufacturingCost::create([
            'product_id'   => $product->id,
            'product_name' => $product->name,
            'material_cost'=> $materialPortion,
            'total_cost'   => $actualCostPerUnit,
            'status'       => 'draft',
            'created_by'   => auth()->id() ?? 1,
            'updated_by'   => auth()->id() ?? 1,
        ]);

        // Confirm the MO so it can be completed
        return app(ManufacturingOrderService::class)->confirmOrder($order->fresh());
    }

    /**
     * Helper: set the BOM's standard cost fields (per unit).
     */
    private function setStandardCost(Product $product, float $material, float $labor, float $overhead): void
    {
        $bom = ManufacturingCost::where('product_id', $product->id)->firstOrFail();
        $bom->update([
            'standard_material_cost'        => $material,
            'standard_labor_cost'           => $labor,
            'standard_overhead_cost'        => $overhead,
            'standard_cost'                 => round($material + $labor + $overhead, 4),
            'standard_cost_effective_from'  => now()->subDay()->toDateString(),
            'standard_cost_updated_by'      => auth()->id() ?? 1,
            'standard_cost_updated_at'      => now(),
        ]);
    }

    private function complete(ManufacturingOrder $order): ManufacturingOrder
    {
        return app(ManufacturingOrderService::class)->completeOrder($order->fresh(), $this->warehouse->id);
    }

    /**
     * --------------------------------------------------------------------------
     * TEST 1 — Tenant WITHOUT standard_costing_enabled → unchanged behavior
     * --------------------------------------------------------------------------
     */
    public function test_tenant_with_disabled_standard_costing_uses_legacy_two_line_entry(): void
    {
        $this->assertFalse($this->settings->fresh()->standard_costing_enabled);

        $product = Product::first() ?? Product::create([
            'code'           => 'PLEG-' . strtoupper(substr(uniqid(), -8)),
            'name'           => 'P-legacy-' . uniqid(),
            'sku'            => 'SKU-' . strtoupper(substr(uniqid(), -8)),
            'product_type'   => 'manufactured',
            'is_manufactured'=> true,
            'base_unit'      => 'piece',
            'category'       => 'Manufactured Products',
            'is_active'      => true,
        ]);
        $order = $this->buildOrder(actualCostPerUnit: 100.0, quantityProduced: 5, product: $product);

        $completed = $this->complete($order);

        // 1. Snapshot fields left null
        $this->assertNull($completed->total_variance, 'Variance must be null when standard costing is disabled.');
        $this->assertNull($completed->variance_type);
        $this->assertNull($completed->variance_journal_entry_id);

        // 2. The entry must exist with the legacy 2-line shape — no 5160 line
        $entry = JournalEntry::where('source_event_key', "manufacturing:{$completed->id}:completed")->first();
        $this->assertNotNull($entry, 'Legacy completion entry should exist.');
        $this->assertCount(2, $entry->lines()->get(), 'Legacy entry must be exactly 2 lines.');

        // 3. None of the lines touches the variance account
        $usesVariance = $entry->lines()->where('account_id', $this->varianceAccount->id)->exists();
        $this->assertFalse($usesVariance, 'No line should hit the variance account when standard costing is off.');
    }

    /**
     * --------------------------------------------------------------------------
     * TEST 2 — Unfavorable Variance (actual > standard)
     * --------------------------------------------------------------------------
     */
    public function test_unfavorable_variance_creates_three_line_entry_debit_on_5160(): void
    {
        $this->settings->update(['standard_costing_enabled' => true]);
        $this->assertTrue($this->settings->fresh()->standard_costing_enabled);

        $product = Product::create([
            'code'           => 'PUNF-' . strtoupper(substr(uniqid(), -8)),
            'name'           => 'P-unfav-' . uniqid(),
            'sku'            => 'SKU-UN-' . strtoupper(substr(uniqid(), -8)),
            'product_type'   => 'manufactured',
            'is_manufactured'=> true,
            'base_unit'      => 'piece',
            'category'       => 'Manufactured Products',
            'is_active'      => true,
        ]);

        // Actual = 110 per unit, Standard = 90 per unit → unfavorable +20 per unit
        // For qty=5: actual=550, standard=450, variance=+100 (unfavorable)
        $order = $this->buildOrder(actualCostPerUnit: 110.0, quantityProduced: 5, product: $product);
        $this->setStandardCost($product, 60.0, 20.0, 10.0); // std total = 90.0/unit
        $completed = $this->complete($order);

        // Check persisted snapshot
        $this->assertEquals(550.0, (float) $completed->actual_cost_at_completion);
        $this->assertEquals(450.0, (float) $completed->standard_cost_at_completion);
        $this->assertEquals(100.0, (float) $completed->total_variance);
        $this->assertEquals('unfavorable', $completed->variance_type);
        $this->assertNotNull($completed->variance_journal_entry_id);
        $this->assertNotNull($completed->cost_locked_at);

        // Check GL entry
        $entry = JournalEntry::find($completed->variance_journal_entry_id);
        $this->assertNotNull($entry);
        $this->assertEquals('manufacturing', $entry->source_type);
        $this->assertEquals(3, $entry->lines()->count(), 'Variance entry must be 3 lines.');

        $lines = $entry->lines()->orderBy('id')->get();
        //   DR  Inventory  @ 450
        //   DR  5160       @ 100
        //   CR  WIP        @ 550
        $this->assertEquals($this->inventoryAccount->id, $lines[0]->account_id);
        $this->assertEquals(450.0, (float) $lines[0]->debit);
        $this->assertEquals(0.0,    (float) $lines[0]->credit);

        $this->assertEquals($this->varianceAccount->id, $lines[1]->account_id);
        $this->assertEquals(100.0, (float) $lines[1]->debit);
        $this->assertEquals(0.0,    (float) $lines[1]->credit);

        $this->assertEquals($this->wipAccount->id, $lines[2]->account_id);
        $this->assertEquals(0.0,   (float) $lines[2]->debit);
        $this->assertEquals(550.0, (float) $lines[2]->credit);

        // Balanced: 450 + 100 = 550
        $this->assertEquals(550.0, (float) $lines->sum('debit'));
        $this->assertEquals(550.0, (float) $lines->sum('credit'));
    }

    /**
     * --------------------------------------------------------------------------
     * TEST 3 — Favorable Variance (actual < standard)
     * --------------------------------------------------------------------------
     */
    public function test_favorable_variance_posts_credit_on_5160(): void
    {
        $this->settings->update(['standard_costing_enabled' => true]);

        $product = Product::create([
            'code'           => 'PFAV-' . strtoupper(substr(uniqid(), -8)),
            'name'           => 'P-fav-' . uniqid(),
            'sku'            => 'SKU-FA-' . strtoupper(substr(uniqid(), -8)),
            'product_type'   => 'manufactured',
            'is_manufactured'=> true,
            'base_unit'      => 'piece',
            'category'       => 'Manufactured Products',
            'is_active'      => true,
        ]);

        // Actual = 80 / unit, Standard = 100 / unit → favorable -20 / unit
        // For qty=5: actual=400, standard=500, variance=-100 (favorable)
        $order = $this->buildOrder(actualCostPerUnit: 80.0, quantityProduced: 5, product: $product);
        $this->setStandardCost($product, 70.0, 20.0, 10.0);
        $completed = $this->complete($order);

        $this->assertEquals(-100.0, (float) $completed->total_variance);
        $this->assertEquals('favorable', $completed->variance_type);

        $entry = JournalEntry::find($completed->variance_journal_entry_id);
        $this->assertNotNull($entry);
        $lines = $entry->lines()->orderBy('id')->get();

        // Layout: DR Inventory @ std, CR WIP @ actual, CR 5160 @ |variance|
        $this->assertEquals(500.0, (float) $lines[0]->debit); // std
        $this->assertEquals($this->varianceAccount->id, $lines[1]->account_id);
        $this->assertEquals(100.0, (float) $lines[1]->credit);
        $this->assertEquals(400.0, (float) $lines[2]->credit);
    }

    /**
     * --------------------------------------------------------------------------
     * TEST 4 — Zero Variance (actual = standard exactly)
     * --------------------------------------------------------------------------
     */
    public function test_zero_variance_does_not_create_5160_line(): void
    {
        $this->settings->update(['standard_costing_enabled' => true]);

        $product = Product::create([
            'code'           => 'PZE-' . strtoupper(substr(uniqid(), -8)),
            'name'           => 'P-zero-' . uniqid(),
            'sku'            => 'SKU-ZE-' . strtoupper(substr(uniqid(), -8)),
            'product_type'   => 'manufactured',
            'is_manufactured'=> true,
            'base_unit'      => 'piece',
            'category'       => 'Manufactured Products',
            'is_active'      => true,
        ]);

        // actual = standard = 100 / unit, qty=4 ⇒ actual=400, std=400, var=0
        $order = $this->buildOrder(actualCostPerUnit: 100.0, quantityProduced: 4, product: $product);
        $this->setStandardCost($product, 60.0, 25.0, 15.0); // total=100
        $completed = $this->complete($order);

        // Snapshot says zero — but order still completes
        $this->assertEquals(0.0, (float) $completed->total_variance);
        $this->assertEquals('none', $completed->variance_type);
        $this->assertNotNull($completed->cost_locked_at);

        // We do NOT expect a variance entry link (Q4: no 5160 line at zero variance).
        $this->assertNull($completed->variance_journal_entry_id, 'Zero variance must not produce a 5160 entry.');

        // We DO expect the legacy 2-line entry to still be posted (because the
        // variance service falls back when no variance exists).
        $legacy = JournalEntry::where('source_event_key', "manufacturing:{$completed->id}:completed")->first();
        $this->assertNotNull($legacy, 'Falling back to legacy 2-line entry should still post the WIP→FG JE.');
        $this->assertCount(2, $legacy->lines()->get());

        // No line should touch 5160 anywhere
        $this->assertFalse(
            JournalEntryLine::where('account_id', $this->varianceAccount->id)->exists(),
            'No journal line should ever touch account 5160 when variance is zero.'
        );
    }

    /**
     * --------------------------------------------------------------------------
     * TEST 5 — Gap 1 (Accrued Overheads) still works on top of Standard Costing
     * --------------------------------------------------------------------------
     *
     *   - confirmOrder() posts WIP-debit using settings.accrued_overheads_account_id
     *     (Gap 1 behavior)
     *   - completeOrder() now posts Variance on account 5160 (Gap 2 behavior)
     *   - Gap 1 must remain functional at completion when standard costing is on
     */
    public function test_gap1_accrued_overheads_still_work_with_standard_costing_enabled(): void
    {
        // Enable both Gap 1 + Gap 2
        $accruedAccount = Account::where('code', '2140')->firstOrFail();
        $this->settings->update([
            'standard_costing_enabled'         => true,
            'accrued_overheads_account_id'     => $accruedAccount->id,
        ]);

        $product = Product::create([
            'code'           => 'PG12-' . strtoupper(substr(uniqid(), -8)),
            'name'           => 'P-gap1-2-' . uniqid(),
            'sku'            => 'SKU-G12-' . strtoupper(substr(uniqid(), -8)),
            'product_type'   => 'manufactured',
            'is_manufactured'=> true,
            'base_unit'      => 'piece',
            'category'       => 'Manufactured Products',
            'is_active'      => true,
        ]);

        // buildOrder internally creates and confirms the MO → confirm-side
        // journal must have included WIP-debit via the accrued overhead flow.
        $order = $this->buildOrder(actualCostPerUnit: 100.0, quantityProduced: 5, product: $product);

        // Verify confirm-side JE exists (Gap 1 behavior)
        $confirmEntry = JournalEntry::where('source_event_key', "manufacturing:{$order->id}:confirmed")->first();
        $this->assertNotNull($confirmEntry, 'Confirm-side JE should exist (Gap 1).');
        $this->assertEquals(
            $this->wipAccount->id,
            $confirmEntry->lines()->where('account_id', $this->wipAccount->id)->first()?->account_id
        );

        // Now run completion with a clear variance → must produce 5160 entry
        $this->setStandardCost($product, 60.0, 25.0, 15.0); // std=100, actual=100, var=0
        // To get a non-zero variance: tweak actual per unit so they're different.
        // We override cost_per_unit directly on the order to keep the rest of the flow.
        $order->update(['cost_per_unit' => 110.0, 'total_cost' => 550.0]);

        // Re-confirm? No, the order is already confirmed; re-running confirm
        // is forbidden. The variance service reads cost_per_unit at completion
        // time and computes the variance dynamically — so we can complete now.
        // But we need a fresh load to pick up the updated cost.
        $order = $order->fresh();

        // We need to bypass the safety check: cost_per_unit changed but the
        // order is already confirmed. simulate the actual completion cost.
        $completed = app(ManufacturingOrderService::class)->completeOrder(
            $order,
            $this->warehouse->id,
        );

        // Invariants
        $this->assertTrue($completed->is_completed);
        $this->assertEquals('unfavorable', $completed->variance_type);
        $this->assertGreaterThan(0, (float) $completed->total_variance);
        $this->assertNotNull($completed->variance_journal_entry_id, 'Variance entry should exist (Gap 2).');

        // The Variance JE itself must NOT touch the accrued overheads account.
        $varianceEntry = JournalEntry::find($completed->variance_journal_entry_id);
        $this->assertNotNull($varianceEntry);
        $this->assertFalse(
            $varianceEntry->lines()->where('account_id', $accruedAccount->id)->exists(),
            'Variance JE must not involve the Accrued Overheads account — that is a separate Gap 1 flow.'
        );
    }
}
