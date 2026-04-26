<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ManufacturingOrder;
use App\Services\ManufacturingOrderService;
use Illuminate\Support\Facades\Auth;

Auth::guard()->setUser(\App\Models\User::find(1));

echo "=== TEST 3: Completing Manufacturing Order ===\n";

try {
    $orderId = trim(file_get_contents('test_order_id.txt'));
    $order = ManufacturingOrder::find($orderId);

    if (!$order) {
        echo "❌ ERROR: Order {$orderId} not found\n";
        exit(1);
    }

    echo "Current status: {$order->status}\n";
    echo "Product name: {$order->product_name}\n";
    echo "Quantity: {$order->quantity_produced}\n";
    echo "Has product_id: " . ($order->product_id ? 'Yes' : 'No') . "\n\n";

    $service = app(ManufacturingOrderService::class);
    $completedOrder = $service->completeOrder($order, 1); // Warehouse ID = 1

    echo "✓ Order completed!\n";
    echo "  - New status: {$completedOrder->status}\n";
    echo "  - Product ID: {$completedOrder->product_id}\n";
    echo "  - Produced at: {$completedOrder->produced_at}\n";

    // Get the product
    $product = $completedOrder->product;
    echo "\n✓ PRODUCT CREATED:\n";
    echo "  - ID: {$product->id}\n";
    echo "  - Name: {$product->name}\n";
    echo "  - Code: {$product->code}\n";
    echo "  - Purchase Price: \${$product->purchase_price}\n";
    echo "  - Selling Price: \${$product->selling_price}\n";
    echo "  - Is Active: " . ($product->is_active ? 'Yes' : 'No') . "\n";
    echo "  - Product Type: {$product->product_type}\n";

    // Check inventory movements
    $movements = $completedOrder->inventoryMovements;
    echo "\n✓ INVENTORY MOVEMENTS:\n";
    echo "  - Count: {$movements->count()}\n";
    foreach ($movements as $movement) {
        echo "  - Movement #{$movement->movement_number}\n";
        echo "    Type: {$movement->movement_type}\n";
        echo "    Quantity Change: +{$movement->quantity_change}\n";
        echo "    Unit Cost: \${$movement->unit_cost}\n";
        echo "    Notes: {$movement->notes}\n";
    }

} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
