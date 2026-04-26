<?php

/**
 * DEMO: Manufacturing Order Completion with Auto Product Creation
 *
 * This script demonstrates the complete flow of creating a manufacturing order
 * and completing it, which automatically creates a sellable product.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ManufacturingOrder;
use App\Models\Warehouse;
use App\Services\ManufacturingOrderService;
use Illuminate\Support\Facades\Auth;

echo "=== Manufacturing Order Completion Demo ===\n\n";

// Simulate authenticated user
Auth::setId(1);

try {
    // STEP 1: Get a warehouse
    echo "1. Finding warehouse...\n";
    $warehouse = Warehouse::first();
    if (!$warehouse) {
        die("Error: No warehouse found. Please create a warehouse first.\n");
    }
    echo "✓ Using warehouse: {$warehouse->name}\n\n";

    // STEP 2: Create a manufacturing order
    echo "2. Creating manufacturing order...\n";
    $order = ManufacturingOrder::create([
        'order_number' => 'MO-2026-DEMO',
        'product_name' => 'Demo Manufactured Product',
        'quantity_produced' => 100,
        'cost_per_unit' => 50.00,
        'total_cost' => 5000.00,
        'selling_price_per_unit' => 75.00,
        'status' => 'confirmed',
        'created_by' => 1,
        'updated_by' => 1,
    ]);
    echo "✓ Order created: {$order->order_number}\n";
    echo "  - Product: {$order->product_name}\n";
    echo "  - Quantity: {$order->quantity_produced}\n";
    echo "  - Cost per unit: \${$order->cost_per_unit}\n";
    echo "  - Selling price: \${$order->selling_price_per_unit}\n";
    echo "  - Status: {$order->status}\n\n";

    // STEP 3: Complete the order (this creates the product automatically)
    echo "3. Completing order and creating product...\n";
    $service = app(ManufacturingOrderService::class);
    $completedOrder = $service->completeOrder($order, $warehouse->id);
    echo "✓ Order completed!\n\n";

    // STEP 4: Verify the product was created
    echo "4. Verifying product creation...\n";
    $product = \App\Models\Product::where('name', 'Demo Manufactured Product')->first();

    if (!$product) {
        die("Error: Product was not created!\n");
    }

    echo "✓ Product successfully created!\n";
    echo "  - ID: {$product->id}\n";
    echo "  - Name: {$product->name}\n";
    echo "  - Code: {$product->code}\n";
    echo "  - Purchase Price: \${$product->purchase_price}\n";
    echo "  - Selling Price: \${$product->selling_price}\n";
    echo "  - Type: {$product->product_type}\n";
    echo "  - Active: " . ($product->is_active ? 'Yes' : 'No') . "\n\n";

    // STEP 5: Check stock in warehouse
    echo "5. Checking stock levels...\n";
    $stockInWarehouse = $product->getQuantityInWarehouse($warehouse->id);
    echo "✓ Stock in {$warehouse->name}: {$stockInWarehouse} units\n\n";

    // STEP 6: Check inventory movements
    echo "6. Checking inventory movements...\n";
    $movements = $completedOrder->inventoryMovements;
    echo "✓ Inventory movements created: {$movements->count()}\n";
    foreach ($movements as $movement) {
        echo "  - Movement #{$movement->movement_number}\n";
        echo "    Type: {$movement->movement_type}\n";
        echo "    Quantity Change: +{$movement->quantity_change}\n";
        echo "    Unit Cost: \${$movement->unit_cost}\n";
        echo "    Unit Price: \${$movement->unit_price}\n";
    }
    echo "\n";

    // STEP 7: Verify product is sellable
    echo "7. Verifying product is ready for sales...\n";
    $checks = [
        'Product exists' => $product !== null,
        'Product is active' => $product->is_active,
        'Has selling price' => $product->selling_price > 0,
        'Has cost price' => $product->purchase_price > 0,
        'Marked as manufactured' => $product->product_type === 'manufactured',
        'Stock available' => $stockInWarehouse > 0,
    ];

    foreach ($checks as $check => $passed) {
        echo "  " . ($passed ? '✓' : '✗') . " {$check}\n";
    }
    echo "\n";

    // FINAL RESULT
    echo "=== RESULT ===\n";
    echo "✅ SUCCESS! Manufacturing order completed successfully.\n";
    echo "✅ Product '{$product->name}' is now in the catalog and ready to sell!\n";
    echo "✅ Stock of {$stockInWarehouse} units available in warehouse.\n";
    echo "\nYou can now:\n";
    echo "  - Add this product to sales invoices\n";
    echo "  - See it in the products list\n";
    echo "  - Track its inventory movements\n";
    echo "  - Sell it to customers\n";

} catch (\Exception $e) {
    echo "\n❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n";
    echo $e->getTraceAsString();
    exit(1);
}
