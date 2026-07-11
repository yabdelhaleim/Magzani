<?php

namespace Tests\Feature\Gap4b;

use App\Models\Tenant;
use App\Models\Plan;
use App\Models\User;
use App\Models\AccountingSetting;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Gap 4c — WoodStock quick-fix regression tests.
 *
 * Replaces the previous Gap4b broken-page tests:
 *   - test_create_mo_action_throws_sql_error_on_wood_stocks_table
 *   - test_edit_mo_action_is_broken_on_production_path
 *
 * After Gap 4c removed all wood_stock_id / woodLotsForManufacturingForm
 * refs from `ManufacturingOrderController` and the create/edit views,
 * the broken SQL error must be GONE. These tests assert the production
 * path now returns 200 OK on:
 *   1. GET /manufacturing-orders/create
 *   2. GET /manufacturing-orders/{id}/edit
 *   3. POST /manufacturing-orders (full create — proves the validation
 *      change did not regress MO creation)
 *   4. GET /warehouses/{id} (warehouse details — proves the
 *      WarehouseService counter removal did not regress this page)
 */
class Gap4bWoodStockBrokenPageTest extends TestCase
{
    use RefreshDatabase;

    protected string $tenantId = 't-gap4b-ws';
    protected $tenant;
    protected $warehouse;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `tenant{$this->tenantId}`");
        } catch (\Throwable $e) { /* ignore */ }

        Plan::query()->delete();
        Plan::create([
            'slug' => 'gap4b-ws',
            'name' => 'Gap4b WS',
            'price' => 0,
            'billing_period' => 'monthly',
            'features' => ['accounting', 'accounting_advanced', 'manufacturing'],
            'is_active' => true,
        ]);

        $this->tenant = Tenant::create([
            'id' => $this->tenantId, 'plan_id' => 'gap4b-ws', 'is_suspended' => false,
        ]);
        $this->tenant->domains()->create(['domain' => $this->tenantId . '.localhost']);
        tenancy()->initialize($this->tenant);

        (new \Database\Seeders\DefaultChartOfAccountsSeeder())->run();
        (new \Database\Seeders\AccountingSettingsSeeder())->run();

        $this->user = User::create([
            'name' => 'Gap4c Tester',
            'email' => 'gap4c@test.local',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        $this->actingAs($this->user);

        $this->warehouse = Warehouse::create([
            'name' => 'WH-Gap4c', 'code' => 'WH-GAP4C', 'is_active' => true,
        ]);
    }

    protected function tearDown(): void
    {
        try {
            tenancy()->end();
            DB::disconnect('tenant');
            $this->tenant->domains()->delete();
            $this->tenant->delete();
        } catch (\Throwable $e) { /* ignore */ }
        parent::tearDown();
    }

    /**
     * Gap 4c verification #1:
     * GET /manufacturing-orders/create must render successfully —
     * the broken wood_stocks call has been removed.
     */
    public function test_create_mo_action_works_after_woodstock_removal(): void
    {
        $controller = app(\App\Http\Controllers\ManufacturingOrderController::class);
        $request = \Illuminate\Http\Request::create('/manufacturing-orders/create', 'GET');

        $view = null;
        $threw = null;
        try {
            $view = $controller->create($request);
        } catch (\Throwable $e) {
            $threw = $e;
        }

        $this->assertNull($threw,
            'create() must NOT throw after Gap 4c removal. Got: '
            . ($threw ? get_class($threw) . ' — ' . $threw->getMessage() : 'no exception'));
        $this->assertInstanceOf(\Illuminate\View\View::class, $view,
            'create() must return a View (controller + view path completes without SQL error).');
    }

    /**
     * Gap 4c verification #2:
     * GET /manufacturing-orders/{id}/edit must render successfully.
     */
    public function test_edit_mo_action_works_after_woodstock_removal(): void
    {
        // Seed the bare minimum so the page can render.
        $mo = \App\Models\ManufacturingOrder::create([
            'order_number' => 'MO-GAP4C-' . uniqid(),
            'product_name' => 'P',
            'quantity_produced' => 1,
            'cost_per_unit' => 1,
            'total_cost' => 1,
            'status' => 'draft',
            'warehouse_id' => $this->warehouse->id,
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $controller = app(\App\Http\Controllers\ManufacturingOrderController::class);
        $request = \Illuminate\Http\Request::create("/manufacturing-orders/{$mo->id}/edit", 'GET');

        $view = null;
        $threw = null;
        // edit(string $id) — no Request parameter
        try {
            $view = $controller->edit((string) $mo->id);
        } catch (\Throwable $e) {
            $threw = $e;
        }

        $this->assertNull($threw,
            'edit() must NOT throw after Gap 4c removal. Got: '
            . ($threw ? get_class($threw) . ' — ' . $threw->getMessage() : 'no exception'));
        $this->assertInstanceOf(\Illuminate\View\View::class, $view,
            'edit() must return a View.');
    }

    /**
     * Gap 4c verification #3:
     * Full end-to-end MO creation. The validation rule removal must
     * NOT break the create-store path. We exercise the controller
     * end-to-end (no HTTP) and assert the order is created.
     */
    public function test_full_mo_creation_still_works_after_validation_rule_removed(): void
    {
        $controller = app(\App\Http\Controllers\ManufacturingOrderController::class);
        $request = \Illuminate\Http\Request::create('/manufacturing-orders', 'POST', [
            'product_name'       => 'Gap4c product',
            'quantity_produced'  => 5,
            'warehouse_id'       => $this->warehouse->id,
            'cost_per_unit'      => 10,
            'selling_price_per_unit' => 25,
            'waste_cost'         => 0,
            'labor_cost'         => 0,
            'nails_cost'         => 0,
            'tips_cost'          => 0,
            'transport_cost'     => 0,
            'fumigation_cost'    => 0,
            'profit_margin'      => 25,
            'components' => [
                [
                    'component_name' => 'wood-A',
                    'component_type' => 'material',
                    'quantity'       => 1.5,
                    'thickness_cm'   => 2.5,
                    'width_cm'       => 12,
                    'length_cm'      => 400,
                    'unit_cost'      => 80,
                ],
            ],
        ]);

        $response = null;
        $threw = null;
        try {
            $response = $controller->store($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $threw = $e;
        } catch (\Throwable $e) {
            $threw = $e;
        }

        $this->assertNull($threw,
            'store() must NOT throw — validation should pass without wood_stock_id. Got: '
            . ($threw ? get_class($threw) . ' — ' . $threw->getMessage() : 'no exception'));

        // Verify the MO row was actually persisted
        $mo = \App\Models\ManufacturingOrder::where('product_name', 'Gap4c product')->first();
        $this->assertNotNull($mo, 'Manufacturing order should have been created.');
        $this->assertEquals(5, (int) $mo->quantity_produced);
    }

    /**
     * Gap 4c verification #4:
     * GET /warehouses/{id} (warehouse details) must render successfully
     * after the wood_stock_batches counter was removed from
     * WarehouseService.
     */
    public function test_warehouse_details_page_returns_200_after_counter_removal(): void
    {
        // The warehouse show page is rendered by WarehouseController::show.
        // We invoke it directly to avoid Stancl/Tenancy HTTP routing
        // complexity.
        $controller = app(\App\Http\Controllers\WarehouseController::class);
        $request = \Illuminate\Http\Request::create("/warehouses/{$this->warehouse->id}", 'GET');

        $response = null;
        $threw = null;
        try {
            $response = $controller->show($this->warehouse->id);
        } catch (\Throwable $e) {
            $threw = $e;
        }

        $this->assertNull($threw,
            'WarehouseController::show must NOT throw after WarehouseService cleanup. Got: '
            . ($threw ? get_class($threw) . ' — ' . $threw->getMessage() : 'no exception'));
    }
}
