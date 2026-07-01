<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Initialize tenancy with a tenant
$tenant = \App\Models\Tenant::first();
if (!$tenant) {
    echo "No tenant found!\n";
    exit(1);
}

tenancy()->initialize($tenant);

echo "--- Columns in manufacturing_orders ---\n";
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('manufacturing_orders');
print_r($columns);

echo "\n--- Columns in manufacturing_order_components ---\n";
$columnsComponents = \Illuminate\Support\Facades\Schema::getColumnListing('manufacturing_order_components');
print_r($columnsComponents);
