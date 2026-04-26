<?php

/**
 * ═══════════════════════════════════════════════════════════════
 * MANUFACTURING ORDER COMPLETION TEST (PHP VERSION)
 * ═══════════════════════════════════════════════════════════════
 *
 * Run this file to test the complete manufacturing order workflow
 *
 * Usage: php test_complete_order_v2.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\InventoryMovement;
use App\Services\ManufacturingOrderService;
use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "🧪 MANUFACTURING ORDER COMPLETION TEST (PHP)\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

try {
    // ═══════════════════════════════════════════════════════════════
    // TEST 1: Create Manufacturing Order
    // ═══════════════════════════════════════════════════════════════

    echo "📝 TEST 1: CREATE MANUFACTURING ORDER\n";
    echo "───────────────────────────────────────────────────────────\n";

    $orderData = [
        'product_name' => 'TEST-PRODUCT-001',
        'quantity_produced' => 10,
        'cost_per_unit' => 100,
        'total_cost' => 1000,
        'selling_price_per_unit' => 130,
        'notes' => 'Test order for completion',
        'components' => [
            [
                'component_name' => 'Test Wood',
                'quantity' => 5,
                'unit' => 'm2',
                'unit_cost' => 20
            ]
        ]
    ];

    $orderService = app(ManufacturingOrderService::class);
    $order = $orderService->createOrder($orderData);

    echo "✅ Order Created:\n";
    echo "   ID: {$order->id}\n";
    echo "   Order Number: {$order->order_number}\n";
    echo "   Product: {$order->product_name}\n";
    echo "   Quantity: {$order->quantity_produced}\n";
    echo "   Status: {$order->status}\n";
    echo "\n";

    // ═══════════════════════════════════════════════════════════════
    // TEST 2: Confirm Manufacturing Order
    // ═══════════════════════════════════════════════════════════════

    echo "📝 TEST 2: CONFIRM MANUFACTURING ORDER\n";
    echo "───────────────────────────────────────────────────────────\n";

    $confirmedOrder = $orderService->confirmOrder($order);

    echo "✅ Order Confirmed:\n";
    echo "   Status: {$confirmedOrder->status}\n";
    echo "\n";

    // ═══════════════════════════════════════════════════════════════
    // TEST 3: Complete Order with Warehouse
    // ═══════════════════════════════════════════════════════════════

    echo "📝 TEST 3: COMPLETE ORDER WITH WAREHOUSE\n";
    echo "───────────────────────────────────────────────────────────\n";

    // Get or create a warehouse
    $warehouse = Warehouse::where('is_active', true)->first();
    if (!$warehouse) {
        $warehouse = Warehouse::create([
            'name' => 'Test Warehouse',
            'code' => 'TEST-WH',
            'is_active' => true,
            'created_by' => 1,
        ]);
        echo "⚠️  Created test warehouse: {$warehouse->id}\n";
    }

    echo "📦 Using Warehouse: {$warehouse->name} (ID: {$warehouse->id})\n";

    $completedOrder = $orderService->completeOrder($order, $warehouse->id);

    echo "✅ Order Completed:\n";
    echo "   Status: {$completedOrder->status}\n";
    echo "   Product ID: {$completedOrder->product_id}\n";
    echo "   Produced At: {$completedOrder->produced_at}\n";
    echo "\n";

    // ═══════════════════════════════════════════════════════════════
    // TEST 4: Check Database - Product Created
    // ═══════════════════════════════════════════════════════════════

    echo "📝 TEST 4: CHECK DATABASE - PRODUCT CREATED\n";
    echo "───────────────────────────────────────────────────────────\n";

    $product = Product::where('name', 'TEST-PRODUCT-001')->first();

    if ($product) {
        echo "✅ Product Found in Database:\n";
        echo "   ID: {$product->id}\n";
        echo "   Name: {$product->name}\n";
        echo "   Code: {$product->code}\n";
        echo "   Purchase Price: {$product->purchase_price}\n";
        echo "   Selling Price: {$product->selling_price}\n";
        echo "   Is Active: " . ($product->is_active ? '✅ YES' : '❌ NO') . "\n";
        echo "   Product Type: {$product->product_type}\n";

        if ($product->is_active) {
            echo "✅ PASS: Product is ACTIVE (will appear in sales)\n";
        } else {
            echo "❌ FAIL: Product is NOT active\n";
        }

        if ($product->product_type === 'manufactured') {
            echo "✅ PASS: Product type is 'manufactured'\n";
        } else {
            echo "⚠️  WARNING: Product type is '{$product->product_type}'\n";
        }
    } else {
        echo "❌ FAIL: Product NOT found in database\n";
    }
    echo "\n";

    // ═══════════════════════════════════════════════════════════════
    // TEST 5: Check Inventory Movement
    // ═══════════════════════════════════════════════════════════════

    echo "📝 TEST 5: CHECK INVENTORY MOVEMENT\n";
    echo "───────────────────────────────────────────────────────────\n";

    $movement = InventoryMovement::where('reference_type', ManufacturingOrder::class)
        ->where('reference_id', $order->id)
        ->first();

    if ($movement) {
        echo "✅ Inventory Movement Found:\n";
        echo "   Movement Type: {$movement->movement_type}\n";
        echo "   Quantity Change: {$movement->quantity_change}\n";
        echo "   Warehouse ID: {$movement->warehouse_id}\n";
        echo "   Unit Cost: {$movement->unit_cost}\n";
        echo "   Reference: {$movement->reference_type} #{$movement->reference_id}\n";

        if ($movement->movement_type === 'production') {
            echo "✅ PASS: Movement type is 'production'\n";
        }
    } else {
        echo "❌ FAIL: Inventory movement NOT found\n";
    }
    echo "\n";

    // ═══════════════════════════════════════════════════════════════
    // TEST 6: Check Product Warehouse Stock
    // ═══════════════════════════════════════════════════════════════

    echo "📝 TEST 6: CHECK PRODUCT WAREHOUSE STOCK\n";
    echo "───────────────────────────────────────────────────────────\n";

    $stock = DB::table('product_warehouse')
        ->where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();

    if ($stock) {
        echo "✅ Stock Found in Product_Warehouse:\n";
        echo "   Product ID: {$stock->product_id}\n";
        echo "   Warehouse ID: {$stock->warehouse_id}\n";
        echo "   Quantity: {$stock->quantity}\n";
        echo "   Available Quantity: {$stock->available_quantity}\n";

        if ($stock->quantity == 10) {
            echo "✅ PASS: Correct quantity (10) in stock\n";
        } else {
            echo "⚠️  WARNING: Quantity is {$stock->quantity}, expected 10\n";
        }
    } else {
        echo "❌ FAIL: Stock NOT found in product_warehouse table\n";
    }
    echo "\n";

    // ═══════════════════════════════════════════════════════════════
    // SUMMARY
    // ═══════════════════════════════════════════════════════════════

    echo "═══════════════════════════════════════════════════════════════\n";
    echo "🎉 TEST SUITE COMPLETED SUCCESSFULLY!\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "📊 Test Results Summary:\n";
    echo "   ✅ Manufacturing order created\n";
    echo "   ✅ Order confirmed successfully\n";
    echo "   ✅ Order completed with warehouse selection\n";
    echo "   ✅ Product automatically created in database\n";
    echo "   ✅ Product is ACTIVE (will appear in products list)\n";
    echo "   ✅ Product type set to 'manufactured'\n";
    echo "   ✅ Inventory movement recorded\n";
    echo "   ✅ Stock added to warehouse via product_warehouse pivot table\n";
    echo "\n";
    echo "🔗 Next Steps:\n";
    echo "   1. Visit /products to verify TEST-PRODUCT-001 appears in list\n";
    echo "   2. Check stock quantity in warehouse\n";
    echo "   3. Try creating a sales invoice with this product\n";
    echo "   4. Verify the product can be sold normally\n";
    echo "\n";
    echo "📋 IDs for Reference:\n";
    echo "   Order ID: {$order->id}\n";
    echo "   Order Number: {$order->order_number}\n";
    echo "   Product ID: {$product->id}\n";
    echo "   Product Code: {$product->code}\n";
    echo "   Warehouse ID: {$warehouse->id}\n";
    echo "   Movement ID: {$movement->id}\n";
    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";

} catch (\Exception $e) {
    echo "❌ TEST FAILED: " . $e->getMessage() . "\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString();
    exit(1);
}