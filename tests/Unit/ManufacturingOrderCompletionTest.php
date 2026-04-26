<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\ManufacturingOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class ManufacturingOrderCompletionTest extends TestCase
{
    use RefreshDatabase;

    private ManufacturingOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ManufacturingOrderService::class);
        Auth::setId(1); // Simulate authenticated user
    }

    /**
     * Test completing a manufacturing order creates a new product automatically
     */
    public function test_complete_order_creates_product_automatically(): void
    {
        // Arrange: Create a warehouse and a confirmed manufacturing order
        $warehouse = Warehouse::factory()->create();
        $order = ManufacturingOrder::factory()->create([
            'order_number' => 'MO-2026-0001',
            'product_name' => 'Test Product 123',
            'product_id' => null, // No existing product
            'quantity_produced' => 100,
            'cost_per_unit' => 50.00,
            'selling_price_per_unit' => 75.00,
            'status' => 'confirmed',
        ]);

        // Act: Complete the order (should create product automatically)
        $completedOrder = $this->service->completeOrder($order, $warehouse->id);

        // Assert: Product was created automatically
        $product = Product::where('name', 'Test Product 123')->first();
        $this->assertNotNull($product, 'Product should be created automatically');
        $this->assertEquals('manufactured', $product->product_type);
        $this->assertEquals(50.00, $product->purchase_price);
        $this->assertEquals(75.00, $product->selling_price);
        $this->assertTrue($product->is_active);

        // Assert: Order is linked to product and completed
        $this->assertEquals('completed', $completedOrder->status);
        $this->assertEquals($product->id, $completedOrder->product_id);
        $this->assertNotNull($completedOrder->produced_at);

        // Assert: Inventory movement was created
        $this->assertCount(1, $completedOrder->inventoryMovements);
        $movement = $completedOrder->inventoryMovements->first();
        $this->assertEquals('production', $movement->movement_type);
        $this->assertEquals(100, $movement->quantity_change);
    }

    /**
     * Test completing order uses existing product if it already exists
     */
    public function test_complete_order_uses_existing_product(): void
    {
        // Arrange: Create existing product and manufacturing order
        $existingProduct = Product::factory()->create([
            'name' => 'Existing Product',
            'purchase_price' => 30.00,
            'selling_price' => 40.00,
        ]);

        $warehouse = Warehouse::factory()->create();
        $order = ManufacturingOrder::factory()->create([
            'order_number' => 'MO-2026-0002',
            'product_name' => 'Existing Product',
            'quantity_produced' => 50,
            'cost_per_unit' => 55.00,
            'selling_price_per_unit' => 80.00,
            'status' => 'confirmed',
        ]);

        // Act: Complete the order
        $completedOrder = $this->service->completeOrder($order, $warehouse->id);

        // Assert: Existing product was used and pricing was updated
        $product = Product::find($existingProduct->id);
        $this->assertEquals($existingProduct->id, $completedOrder->product_id);
        $this->assertEquals(55.00, $product->purchase_price); // Updated
        $this->assertEquals(80.00, $product->selling_price);  // Updated
        $this->assertEquals('manufactured', $product->product_type); // Updated
    }

    /**
     * Test product appears in sales catalog after completion
     */
    public function test_product_is_sellable_after_completion(): void
    {
        // Arrange
        $warehouse = Warehouse::factory()->create();
        $order = ManufacturingOrder::factory()->create([
            'product_name' => 'Sellable Product',
            'quantity_produced' => 25,
            'cost_per_unit' => 100.00,
            'selling_price_per_unit' => 150.00,
            'status' => 'confirmed',
        ]);

        // Act
        $this->service->completeOrder($order, $warehouse->id);

        // Assert: Product is active and can be sold
        $product = Product::where('name', 'Sellable Product')->first();
        $this->assertTrue($product->is_active, 'Product must be active to appear in sales');
        $this->assertEquals('manufactured', $product->product_type);
        $this->assertEquals(150.00, $product->selling_price);
    }

    /**
     * Test transaction rolls back on error
     */
    public function test_transaction_rolls_back_on_warehouse_error(): void
    {
        // Arrange
        $order = ManufacturingOrder::factory()->create([
            'product_name' => 'Test Product',
            'status' => 'confirmed',
        ]);

        // Act: Try to complete with non-existent warehouse
        // This should fail and rollback any changes
        try {
            $this->service->completeOrder($order, 99999); // Invalid warehouse ID
            $this->fail('Expected exception was not thrown');
        } catch (\Exception $e) {
            // Assert: Product was not created (transaction rolled back)
            $product = Product::where('name', 'Test Product')->first();
            $this->assertNull($product, 'Product should not exist after failed transaction');

            // Assert: Order status was not changed
            $order->refresh();
            $this->assertEquals('confirmed', $order->status);
        }
    }

    /**
     * Test stock quantity is updated correctly
     */
    public function test_stock_quantity_updated_after_completion(): void
    {
        // Arrange
        $warehouse = Warehouse::factory()->create();
        $order = ManufacturingOrder::factory()->create([
            'product_name' => 'Stocked Product',
            'quantity_produced' => 75,
            'status' => 'confirmed',
        ]);

        // Act
        $this->service->completeOrder($order, $warehouse->id);

        // Assert: Check stock in warehouse
        $product = Product::where('name', 'Stocked Product')->first();
        $stockInWarehouse = $product->getQuantityInWarehouse($warehouse->id);
        $this->assertEquals(75, $stockInWarehouse);
    }
}
