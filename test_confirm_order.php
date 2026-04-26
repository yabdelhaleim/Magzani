<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ManufacturingOrder;
use App\Services\ManufacturingOrderService;
use Illuminate\Support\Facades\Auth;

Auth::guard()->setUser(\App\Models\User::find(1));

echo "=== TEST 2: Confirming Manufacturing Order ===\n";

try {
    $orderId = trim(file_get_contents('test_order_id.txt'));
    $order = ManufacturingOrder::find($orderId);

    if (!$order) {
        echo "❌ ERROR: Order {$orderId} not found\n";
        exit(1);
    }

    echo "Current status: {$order->status}\n";

    $service = app(ManufacturingOrderService::class);
    $confirmedOrder = $service->confirmOrder($order);

    echo "✓ Order confirmed!\n";
    echo "  - New status: {$confirmedOrder->status}\n";
    echo "  - Can complete: " . ($confirmedOrder->can_complete ? 'Yes' : 'No') . "\n";

} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
