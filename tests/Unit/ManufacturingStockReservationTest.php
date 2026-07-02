<?php

namespace Tests\Unit;

use App\Models\ManufacturingOrder;
use App\Models\RawMaterialTemplate;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Tenant;
use App\Services\ManufacturingOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManufacturingStockReservationTest extends TestCase
{
    use RefreshDatabase;

    private ManufacturingOrderService $service;
    protected $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant and initialize environment for testing
        $tenantId = 'unit-' . uniqid();
        $this->tenant = Tenant::create([
            'id' => $tenantId,
            'plan_id' => 'basic',
        ]);
        tenancy()->initialize($this->tenant);

        $this->service = app(ManufacturingOrderService::class);
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
     * Test raw material is reserved when order is confirmed
     */
    public function test_raw_material_reserved_on_confirmation(): void
    {
        // Arrange: Create warehouse and raw material template with stock
        $warehouse = Warehouse::factory()->create();
        $rawMaterial = RawMaterialTemplate::create([
            'name' => 'Pine Board',
            'quantity' => 100.00,
            'buy_price' => 10.00,
            'sale_price' => 15.00,
        ]);

        $order = ManufacturingOrder::factory()->create([
            'order_number' => 'MO-2026-R001',
            'product_name' => 'Test Pallet',
            'quantity_produced' => 10,
            'cost_per_unit' => 50.00,
            'total_cost' => 500.00,
            'status' => 'draft',
            'warehouse_id' => $warehouse->id,
        ]);

        // Add component linked to raw material
        $order->components()->create([
            'component_name' => 'Pine Board component',
            'component_type' => 'Pine Board',
            'quantity' => 2, // 2 pieces per unit * 10 units = 20 pieces needed
            'unit' => 'piece',
            'thickness_cm' => 2,
            'width_cm' => 10,
            'length_cm' => 100,
            'volume_cm3' => 2000,
            'price_per_cubic_meter' => 0,
            'unit_cost' => 10,
            'total_cost' => 20,
        ]);

        // Act: Confirm the order (reserves stock)
        $this->service->confirmOrder($order);

        // Assert: Raw material quantity is decremented by 20 (100 - 20 = 80)
        $rawMaterial->refresh();
        $this->assertEquals(80.00, $rawMaterial->quantity);
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    /**
     * Test raw material is released when order is cancelled
     */
    public function test_raw_material_released_on_cancellation(): void
    {
        // Arrange: Create warehouse and raw material template with stock
        $warehouse = Warehouse::factory()->create();
        $rawMaterial = RawMaterialTemplate::create([
            'name' => 'Pine Board',
            'quantity' => 100.00,
            'buy_price' => 10.00,
            'sale_price' => 15.00,
        ]);

        $order = ManufacturingOrder::factory()->create([
            'order_number' => 'MO-2026-R002',
            'product_name' => 'Test Pallet',
            'quantity_produced' => 10,
            'cost_per_unit' => 50.00,
            'total_cost' => 500.00,
            'status' => 'draft',
            'warehouse_id' => $warehouse->id,
        ]);

        $order->components()->create([
            'component_name' => 'Pine Board component',
            'component_type' => 'Pine Board',
            'quantity' => 2,
            'unit' => 'piece',
            'thickness_cm' => 2,
            'width_cm' => 10,
            'length_cm' => 100,
            'volume_cm3' => 2000,
            'price_per_cubic_meter' => 0,
            'unit_cost' => 10,
            'total_cost' => 20,
        ]);

        // Act: Confirm order (reserves stock)
        $this->service->confirmOrder($order);
        $rawMaterial->refresh();
        $this->assertEquals(80.00, $rawMaterial->quantity);

        // Act: Cancel order (releases stock)
        $this->service->cancelOrder($order, 'Testing cancel');

        // Assert: Raw material quantity returns to 100
        $rawMaterial->refresh();
        $this->assertEquals(100.00, $rawMaterial->quantity);
        $this->assertEquals('cancelled', $order->fresh()->status);
    }
}
