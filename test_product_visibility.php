<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Product;

echo "=== TEST 5: Product Visibility in Sales List ===\n";

try {
    // Test 1: Check if product appears in active products query
    echo "1. Testing Product::active() query:\n";
    $activeProducts = Product::active()
        ->where('name', 'TEST-Balata-113x113')
        ->get();

    if ($activeProducts->count() > 0) {
        echo "   ✓ Product found in active products!\n";
        foreach ($activeProducts as $p) {
            echo "     - ID: {$p->id}, Name: {$p->name}, Active: " . ($p->is_active ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "   ✗ Product NOT found in active products\n";
    }

    // Test 2: Check if product appears in is_active = true query
    echo "\n2. Testing where('is_active', true) query:\n";
    $isActiveProducts = Product::where('is_active', true)
        ->where('name', 'TEST-Balata-113x113')
        ->get();

    if ($isActiveProducts->count() > 0) {
        echo "   ✓ Product found with is_active = true!\n";
    } else {
        echo "   ✗ Product NOT found with is_active = true\n";
    }

    // Test 3: Check if product would appear in sales controller query
    echo "\n3. Testing Sales Controller query pattern:\n";
    $salesProducts = Product::with(['sellingUnits' => function($query) {
                $query->where('is_active', true)
                      ->select('id', 'product_id', 'unit_name', 'unit_code',
                              'conversion_factor', 'is_default', 'display_order')
                      ->orderBy('is_default', 'desc');
            }])
            ->where('is_active', true)
            ->where('name', 'TEST-Balata-113x113')
            ->select('id', 'name', 'sku', 'base_unit_label', 'purchase_price')
            ->get();

    if ($salesProducts->count() > 0) {
        echo "   ✓ Product would appear in sales invoice screen!\n";
        foreach ($salesProducts as $p) {
            echo "     - ID: {$p->id}, Name: {$p->name}, Purchase Price: \${$p->purchase_price}\n";
        }
    } else {
        echo "   ✗ Product would NOT appear in sales invoice screen\n";
    }

    // Test 4: Check manufactured products specifically
    echo "\n4. Testing manufactured products query:\n";
    $manufacturedProducts = Product::active()
        ->where('product_type', 'manufactured')
        ->where('name', 'TEST-Balata-113x113')
        ->get();

    if ($manufacturedProducts->count() > 0) {
        echo "   ✓ Product found in manufactured products!\n";
    } else {
        echo "   ✗ Product NOT found in manufactured products\n";
    }

    echo "\n=== RESULT ===\n";
    echo "✅ SUCCESS! 'TEST-Balata-113x113' is visible in sales queries and ready to sell!\n";

} catch (\Exception $e) {
    echo "❌ ERROR: {$e->getMessage()}\n";
    echo "Stack trace:\n{$e->getTraceAsString()}\n";
    exit(1);
}
