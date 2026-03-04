<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductWarehouseSeeder extends Seeder
{
    public function run()
    {
        DB::table('product_warehouse')->insert([
            [
                'product_id' => 1,
                'warehouse_id' => 1,
                'quantity' => 100,
                'reserved_quantity' => 0,
                'average_cost' => 50.00,
                'min_stock' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 3,
                'warehouse_id' => 1,
                'quantity' => 200,
                'reserved_quantity' => 0,
                'average_cost' => 75.00,
                'min_stock' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}