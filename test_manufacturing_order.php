<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderComponent;
use Illuminate\Support\Facades\Auth;

// Simulate authenticated user using guard
Auth::guard()->setUser(\App\Models\User::find(1));

echo "=== TEST 1: Creating Manufacturing Order ===\n";

try {
    $order = ManufacturingOrder::create([
        'order_number' => 'MO-2026-TEST-001',
        'product_name' => 'TEST-Balata-113x113',
        'quantity_produced' => 10,
        'cost_per_unit' => 100.00,
        'total_cost' => 1000.00,
        'selling_price_per_unit' => 130.00,
        'status' => 'draft',
        'created_by' => 1,
        'updated_by' => 1,
    ]);

    echo "✓ Order created: ID {$order->id}, Number: {$order->order_number}\n";
    echo "  - Product: {$order->product_name}\n";
    echo "  - Quantity: {$order->quantity_produced}\n";
    echo "  - Status: {$order->status}\n";

    // Add component
    ManufacturingOrderComponent::create([
        'order_id' => $order->id,
        'component_name' => 'Wood',
        'quantity' => 5,
        'unit' => 'm2',
        'unit_cost' => 20,
        'total_cost' => 100,
        'created_by' => 1,
    ]);

    echo "✓ Component added: Wood (5 m2 @ $20)\n";

    file_put_contents('test_order_id.txt', $order->id);
    echo "\nOrder ID saved to test_order_id.txt: {$order->id}\n";

} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
