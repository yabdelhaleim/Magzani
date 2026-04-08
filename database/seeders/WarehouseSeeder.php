<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'المستودع الرئيسي',
                'code' => 'WH001',
                'status' => 'active',
                'location' => 'القاهرة',
                'city' => 'القاهرة',
                'area' => 'مدينة解放',
                'is_active' => true,
            ],
            [
                'name' => 'مستودع الجيزة',
                'code' => 'WH002',
                'status' => 'active',
                'location' => 'الجيزة',
                'city' => 'الجيزة',
                'area' => 'أكتوبر',
                'is_active' => true,
            ],
            [
                'name' => 'مستودع الإسكندرية',
                'code' => 'WH003',
                'status' => 'active',
                'location' => 'الإسكندرية',
                'city' => 'الإسكندرية',
                'area' => 'سيدي جابر',
                'is_active' => true,
            ],
            [
                'name' => 'مستودع التعبئة',
                'code' => 'WH004',
                'status' => 'active',
                'location' => 'القاهرة',
                'city' => 'القاهرة',
                'area' => 'المعادي',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
