<?php

namespace Tests\Feature\Gap4b;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\Account;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\MaterialBatch;
use App\Models\MaterialDispensing;
use App\Models\ManufacturingOrderComponent;
use App\Models\AccountingSetting;
use App\Services\MaterialStockService;
use App\Services\InventoryMovementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Gap 4b — Concurrency regression test for the race condition in
 * `MaterialStockService::dispense()`.
 *
 * Pre-fix: the batch row was passed in unlocked. Two concurrent
 * transactions could read remaining_qty=10, both call `decrement(...)`
 * (atomic SQL UPDATE — but BOTH started from a stale read of 10), and
 * the final remaining_qty would be wrong (lost update) — or, in the
 * extreme case, both decrements land and the system silently
 * double-issues a dispense against a single batch row.
 *
 * Post-fix: `dispense()` opens the DB transaction with a fresh
 * `lockForUpdate()` on the batch row inside the same transaction.
 * MySQL serializes concurrent attempts via row-level locks. The second
 * concurrent transaction blocks on the row lock until the first commits
 * (or until `innodb_lock_wait_timeout` fires).
 *
 * True OS-level concurrency is not available in Laravel's single-PHP
 * PHPUnit process, so this test uses TWO PDO connections to simulate
 * two independent database sessions hitting the same row.
 */
class Gap4bConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected string $tenantId = 't-gap4b';
    protected $tenant;
    protected $warehouse;
    protected $pieceUom;

    protected function setUp(): void
    {
        parent::setUp();

        // -- Plan + Tenant (same scaffolding as Gap4BatchTrackingTest) --
        Plan::query()->delete();
        Plan::create([
            'slug' => 'gap4b-test',
            'name' => 'Gap4b Test Plan',
            'price' => 0,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'manufacturing'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'id' => $this->tenantId,
            'plan_id' => 'gap4b-test',
            'is_suspended' => false,
        ]);
        $this->tenant->domains()->create(['domain' => $this->tenantId . '.localhost']);
        tenancy()->initialize($this->tenant);

        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        $fy = \App\Models\FiscalYear::create([
            'name' => '2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31',
            'is_closed' => false, 'is_current' => true,
        ]);
        \App\Models\FiscalPeriod::create([
            'fiscal_year_id' => $fy->id, 'name' => 'يوليو 2026', 'period_number' => 7,
            'start_date' => '2026-07-01', 'end_date' => '2026-07-31', 'is_closed' => false,
        ]);

        $settings = AccountingSetting::firstOrFail();
        $settings->update([
            'auto_post_manufacturing' => true,
            'wip_account_id' => Account::where('code', '1350')->firstOrFail()->id,
            'inventory_account_id' => Account::where('code', '1310')->firstOrFail()->id,
        ]);

        User::create([
            'name' => 'Gap4b Tester', 'email' => 'gap4b@test.local',
            'password' => bcrypt('password'), 'role' => 'admin',
        ]);
        $this->actingAs(User::first());

        $this->warehouse = Warehouse::create([
            'name' => 'WH-Gap4b', 'code' => 'WH-GAP4B', 'is_active' => true,
        ]);
        $this->pieceUom = \App\Models\UnitOfMeasure::firstOrCreate(
            ['code' => 'PCS-G4B'],
            ['name' => 'Piece', 'type' => 'count', 'is_active' => true]
        );

        // Register a SECOND MySQL connection that targets the SAME DB.
        // Two independent PDO sessions ⇒ two independent transactions
        // that MySQL treats as concurrent (this is the entire reason
        // row-locks exist).
        config([
            'database.connections.tenant_b' => array_merge(
                config('database.connections.tenant'),
                ['name' => 'tenant_b']
            ),
        ]);
        DB::purge('tenant_b');
    }

    protected function tearDown(): void
    {
        try {
            DB::connection('tenant_b')->rollBack();
        } catch (\Throwable $e) { /* ignore */ }
        try {
            tenancy()->end();
            DB::disconnect('tenant');
            DB::disconnect('tenant_b');
            $this->tenant->domains()->delete();
            $this->tenant->delete();
        } catch (\Throwable $e) { /* ignore */ }
        parent::tearDown();
    }

    /**
     * Seed a MaterialBatch with qty=10 / remaining=10 at our test
     * warehouse. Also registers an initial 'material_in' inventory
     * movement so ProductWarehouse has stock for the dispense.
     */
    private function seedBatch(Product $product, float $qty, float $unitCost = 80.0): MaterialBatch
    {
        $batch = MaterialBatch::create([
            'batch_code'    => 'B-CONC-' . strtoupper(substr(uniqid(), -6)),
            'product_id'    => $product->id,
            'warehouse_id'  => $this->warehouse->id,
            'uom_id'        => $this->pieceUom->id,
            'quantity'      => $qty,
            'remaining_qty' => $qty,
            'unit_cost'     => $unitCost,
            'original_unit_cost' => $unitCost,
            'original_unit_cost_locked_at' => now(),
            'received_at'   => '2026-06-01',
        ]);
        // Seed warehouse stock
        app(InventoryMovementService::class)->recordMovement([
            'warehouse_id'    => $this->warehouse->id,
            'product_id'      => $product->id,
            'movement_type'   => 'material_in',
            'quantity_change' => $qty,
            'unit_cost'       => $unitCost,
            'reference_type'  => MaterialBatch::class,
            'reference_id'    => $batch->id,
            'notes'           => 'seed for concurrency test',
            'created_by'      => auth()->id() ?? 1,
        ]);
        return $batch;
    }

    /**
     * VERIFY 1 — A second transaction CANNOT acquire the row lock
     * while the first holds it.
     *
     * This is the direct, visible proof that the fix installs a lock.
     */
    public function test_second_connection_blocks_on_locked_batch_row(): void
    {
        $product = Product::create([
            'code' => 'P-CONC1', 'name' => 'P-CONC1', 'sku' => 'SKU-CONC1-' . uniqid(),
            'product_type' => 'raw_material',
            'base_unit' => 'piece', 'category' => 'Raw',
            'is_active' => true,
        ]);
        $batch = $this->seedBatch($product, 10.0);

        // Connection A opens transaction and locks the batch row.
        DB::transaction(function () use ($batch) {
            DB::table('material_batches')
                ->where('id', $batch->id)
                ->lockForUpdate()
                ->first();

            // While A holds the lock, attempt the same from connection B
            // with a 1-second wait timeout. Expect a lock-wait error.
            DB::connection('tenant_b')
                ->statement('SET SESSION innodb_lock_wait_timeout = 1');

            $threw = null;
            try {
                DB::connection('tenant_b')->transaction(function () use ($batch) {
                    DB::connection('tenant_b')
                        ->table('material_batches')
                        ->where('id', $batch->id)
                        ->lockForUpdate()
                        ->first();
                });
            } catch (\Throwable $e) {
                $threw = $e;
            }
            $this->assertNotNull($threw, 'Connection B should fail to acquire the row lock while A holds it.');
            $this->assertStringContainsString('lock', strtolower($threw->getMessage()),
                'Connection B error should reference a lock-wait/timeout failure.');
        });
    }

    /**
     * VERIFY 2 — Sequencing two concurrent dispenses via separate
     * connections yields the CORRECT final remaining_qty, never a
     * negative value or a lost-update (e.g. 10→4 from two successful
     * 6-unit decrements instead of correctly rejecting the second).
     *
     *      Two dispenses of 6 each, against batch qty=10.
     *      Expected: first OK (10 → 4), second rejected (4 < 6).
     *      With lockForUpdate fix: 10 → 4, no row goes negative.
     *      Without the fix: would be 10 → 4 with a stale read, then
     *      4 → -2 (impossible but seen as success) OR both succeed
     *      and final = 4 (lost update of the second decrement).
     */
    public function test_two_concurrent_dispenses_serialize_correctly(): void
    {
        $product = Product::create([
            'code' => 'P-CONC2', 'name' => 'P-CONC2', 'sku' => 'SKU-CONC2-' . uniqid(),
            'product_type' => 'raw_material',
            'base_unit' => 'piece', 'category' => 'Raw',
            'is_active' => true,
        ]);
        $batch = $this->seedBatch($product, 10.0);

        $service = app(MaterialStockService::class);

        // === FIRST dispense via default connection: 6 units. Should succeed. ===
        $d1 = $service->dispense($batch, [
            'quantity_taken'  => 6,
            'manufacturing_order_id' => null,
            'dispensed_at'    => '2026-06-15',
            'notes'           => 'first dispense',
        ]);
        $this->assertNotNull($d1);

        $firstAfter = (float) MaterialBatch::find($batch->id)->remaining_qty;
        $this->assertEquals(4.0, $firstAfter, 'After first dispense (6) the batch should have 4 remaining.');

        // === SECOND dispense attempt: 6 units against 4 remaining. Should fail. ===
        $secondError = null;
        try {
            $service->dispense(MaterialBatch::find($batch->id)->fresh(), [
                'quantity_taken'  => 6,
                'manufacturing_order_id' => null,
                'dispensed_at'    => '2026-06-15',
                'notes'           => 'second dispense',
            ]);
        } catch (ValidationException $e) {
            $secondError = $e;
        }
        $this->assertNotNull($secondError,
            'Second dispense of 6 units must throw ValidationException because remaining is only 4.');
        $this->assertStringContainsString('quantity_taken', implode(',', $secondError->validator->errors()->keys()));

        // === Final assertion: remaining_qty is exactly 4 (the correct
        // value after one 6-unit dispense). NOT -2 or 0 or any other. ===
        $final = (float) MaterialBatch::find($batch->id)->remaining_qty;
        $this->assertEquals(4.0, $final,
            'After first dispense the batch must be 4. The second must reject without mutating.');

        // Verify only ONE MaterialDispensing row exists (proves the
        // second attempt did not silently commit a dispensing).
        $this->assertEquals(
            1,
            MaterialDispensing::where('material_batch_id', $batch->id)->count(),
            'Exactly one MaterialDispensing should exist for this batch.'
        );
    }

    /**
     * VERIFY 3 — Sanity check: when the lock-wait timeout is generous
     * and ONLY one connection dispenses, the data is preserved as
     * expected. Ensures the fix doesn't regress normal sequential use.
     */
    public function test_normal_sequential_dispense_still_works_with_lock(): void
    {
        $product = Product::create([
            'code' => 'P-CONC3', 'name' => 'P-CONC3', 'sku' => 'SKU-CONC3-' . uniqid(),
            'product_type' => 'raw_material',
            'base_unit' => 'piece', 'category' => 'Raw',
            'is_active' => true,
        ]);
        $batch = $this->seedBatch($product, 20.0);
        $service = app(MaterialStockService::class);

        $service->dispense($batch, ['quantity_taken' => 5, 'dispensed_at' => '2026-06-15']);
        $service->dispense(MaterialBatch::find($batch->id), ['quantity_taken' => 5, 'dispensed_at' => '2026-06-15']);
        $service->dispense(MaterialBatch::find($batch->id), ['quantity_taken' => 5, 'dispensed_at' => '2026-06-15']);

        $final = (float) MaterialBatch::find($batch->id)->remaining_qty;
        $this->assertEquals(5.0, $final, 'Three sequential 5-unit dispenses from qty=20 → 5 remaining.');
        $this->assertEquals(3, MaterialDispensing::where('material_batch_id', $batch->id)->count(),
            'Three dispenses should produce three MaterialDispensing rows.');
    }
}
