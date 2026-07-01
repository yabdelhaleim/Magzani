<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Tenant;
use App\Models\User;
use App\Models\ProductWarehouse;
use App\Models\InventoryMovement;
use App\Services\StockService;
use App\Exceptions\StockAdjustmentException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize tenant context for test
        $tenantId = 'unit-' . uniqid();
        $this->tenant = Tenant::create([
            'id' => $tenantId,
            'plan_id' => 'pro',
        ]);
        tenancy()->initialize($this->tenant);

        $this->service = app(StockService::class);
        
        $this->actingAs(User::factory()->create([
            'is_active' => true,
            'role' => 'admin',
        ]));
    }

    protected function tearDown(): void
    {
        if ($this->tenant) {
            try {
                $this->tenant->delete();
            } catch (\Exception $e) {
                // Ignore
            }
        }
        parent::tearDown();
    }

    /**
     * Test stock adjustment increases quantity on PURCHASE
     */
    public function test_adjust_increases_stock_on_purchase(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $this->service->adjust(
            $warehouse->id,
            $product->id,
            10.0,
            StockService::PURCHASE,
            123, // reference_id
            15.5 // unitCost
        );

        $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($productWarehouse);
        $this->assertEquals(10.0, (float) $productWarehouse->quantity);
        $this->assertEquals(10.0, (float) $productWarehouse->available_quantity);
        $this->assertEquals(15.5, (float) $productWarehouse->average_cost);

        // Assert movement is recorded
        $movement = InventoryMovement::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals('purchase', $movement->movement_type);
        $this->assertEquals(10.0, (float) $movement->quantity_change);
        $this->assertEquals(10.0, (float) $movement->quantity_after);
        $this->assertEquals(0.0, (float) $movement->quantity_before);
        $this->assertEquals(15.5, (float) $movement->unit_cost);
    }

    /**
     * Test stock adjustment decreases quantity on SALE
     */
    public function test_adjust_decreases_stock_on_sale(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        // Seed initial stock
        ProductWarehouse::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 15.0,
            'reserved_quantity' => 3.0,
            'average_cost' => 10.0,
            'min_stock' => 0.0,
        ]);

        $this->service->adjust(
            $warehouse->id,
            $product->id,
            -5.0,
            StockService::SALE,
            456, // referenceId
            null // no unitCost
        );

        $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertEquals(10.0, (float) $productWarehouse->quantity);
        $this->assertEquals(7.0, (float) $productWarehouse->available_quantity); // 10.0 - 3.0
        $this->assertEquals(10.0, (float) $productWarehouse->average_cost); // average cost remains the same

        // Assert movement is recorded
        $movement = InventoryMovement::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->where('movement_type', 'sale')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(-5.0, (float) $movement->quantity_change);
        $this->assertEquals(5.0, (float) $movement->quantity);
        $this->assertEquals(15.0, (float) $movement->quantity_before);
        $this->assertEquals(10.0, (float) $movement->quantity_after);
    }

    /**
     * Test moving average calculation on stock increase
     */
    public function test_adjust_recalculates_moving_average_cost(): void
    {
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        // Initial state: 10 units at average cost $50
        ProductWarehouse::create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10.0,
            'reserved_quantity' => 0.0,
            'average_cost' => 50.0,
            'min_stock' => 0.0,
        ]);

        // Adjust: add 5 units at cost $80
        // New Average Cost = ((10 * 50) + (5 * 80)) / 15 = (500 + 400) / 15 = 900 / 15 = 60
        $this->service->adjust(
            $warehouse->id,
            $product->id,
            5.0,
            StockService::PURCHASE,
            789,
            80.0
        );

        $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouse->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertEquals(15.0, (float) $productWarehouse->quantity);
        $this->assertEquals(60.0, (float) $productWarehouse->average_cost);
    }

    /**
     * Test adjustment throws exception for invalid type
     */
    public function test_adjust_throws_exception_for_invalid_type(): void
    {
        $this->expectException(StockAdjustmentException::class);
        $this->service->adjust(1, 1, 10.0, 'INVALID_TYPE', 999);
    }

    /**
     * Test transaction rollback on database error
     */
    public function test_adjust_rolls_back_on_error(): void
    {
        $warehouse = Warehouse::factory()->create();
        
        // Product ID 999999 does not exist in products table, so foreign key constraint should fail
        try {
            $this->service->adjust(
                $warehouse->id,
                999999, // Non-existent product
                10.0,
                StockService::PURCHASE,
                111,
                10.0
            );
            $this->fail('Expected StockAdjustmentException was not thrown');
        } catch (StockAdjustmentException $e) {
            // Assert that no record was created in product_warehouse
            $count = ProductWarehouse::where('warehouse_id', $warehouse->id)
                ->where('product_id', 999999)
                ->count();
            $this->assertEquals(0, $count);
        }
    }
}
