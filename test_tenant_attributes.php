<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$row = DB::table('tenants')->where('id', 'mahmo')->first();
if ($row) {
    echo "Raw database row:\n";
    print_r($row);
} else {
    echo "Tenant row not found\n";
}
