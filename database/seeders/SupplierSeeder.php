<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'شركة الإلكترونيات العالمية', 'code' => 'S001', 'phone' => '01112345670', 'city' => 'القاهرة', 'opening_balance' => 50000],
            ['name' => 'شركة الملابس الشرقية', 'code' => 'S002', 'phone' => '01112345671', 'city' => 'الجيزة', 'opening_balance' => 30000],
            ['name' => 'شركة الأغذية الطازجة', 'code' => 'S003', 'phone' => '01112345672', 'city' => 'الإسكندرية', 'opening_balance' => 20000],
            ['name' => 'مورد التجزئة', 'code' => 'S004', 'phone' => '01112345673', 'city' => 'القاهرة', 'opening_balance' => 10000],
            ['name' => 'شركة المستلزمات المكتبية', 'code' => 'S005', 'phone' => '01112345674', 'city' => 'الجيزة', 'opening_balance' => 5000],
            ['name' => 'شركة الأدوات الكهربائية', 'code' => 'S006', 'phone' => '01112345675', 'city' => 'المنصورة', 'opening_balance' => 15000],
            ['name' => 'شركة مستحضرات التجميل', 'code' => 'S007', 'phone' => '01112345676', 'city' => 'الإسكندرية', 'opening_balance' => 8000],
            ['name' => 'شركة الألعاب الدولية', 'code' => 'S008', 'phone' => '01112345677', 'city' => 'القاهرة', 'opening_balance' => 12000],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create([
                'name' => $supplier['name'],
                'code' => $supplier['code'],
                'phone' => $supplier['phone'],
                'email' => strtolower(str_replace(' ', '.', $supplier['name'])) . '@supplier.com',
                'city' => $supplier['city'],
                'address' => $supplier['city'] . ' - المنطقة الصناعية',
                'opening_balance' => $supplier['opening_balance'],
                'current_balance' => $supplier['opening_balance'],
                'is_active' => true,
            ]);
        }
    }
}
