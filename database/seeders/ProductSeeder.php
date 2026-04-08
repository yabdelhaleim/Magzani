<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductBaseUnit;
use App\Models\ProductWarehouse;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'آيفون 14 برو ماكس', 'code' => 'IP14PM', 'category' => 'هواتف ذكية', 'purchase_price' => 25000, 'selling_price' => 28000],
            ['name' => 'آيفون 13', 'code' => 'IP13', 'category' => 'هواتف ذكية', 'purchase_price' => 18000, 'selling_price' => 21000],
            ['name' => 'سامسونج جالكسي S23', 'code' => 'SG23', 'category' => 'هواتف ذكية', 'purchase_price' => 20000, 'selling_price' => 23000],
            ['name' => 'سامسونج جالكسي A54', 'code' => 'SGA54', 'category' => 'هواتف ذكية', 'purchase_price' => 12000, 'selling_price' => 14500],
            ['name' => 'آيباد برو 11', 'code' => 'IPAD11', 'category' => 'أجهزة لوحية', 'purchase_price' => 22000, 'selling_price' => 25000],
            ['name' => 'آيباد ميني', 'code' => 'IPADMINI', 'category' => 'أجهزة لوحية', 'purchase_price' => 15000, 'selling_price' => 17500],
            ['name' => 'شاحن آبل 20 واط', 'code' => 'CHAR20', 'category' => 'إكسسوارات', 'purchase_price' => 450, 'selling_price' => 600],
            ['name' => 'كفر آيفون 14', 'code' => 'CASE14', 'category' => 'إكسسوارات', 'purchase_price' => 150, 'selling_price' => 250],
            ['name' => 'سماعات إير بودز', 'code' => 'AIRPODS', 'category' => 'إكسسوارات', 'purchase_price' => 3500, 'selling_price' => 4200],
            ['name' => 'قميص رجالي قطن', 'code' => 'SHIRT01', 'category' => 'رجالي', 'purchase_price' => 150, 'selling_price' => 250],
            ['name' => 'بناطيل جينز', 'code' => 'JEANS01', 'category' => 'رجالي', 'purchase_price' => 300, 'selling_price' => 450],
            ['name' => 'فستان نسائي', 'code' => 'DRESS01', 'category' => 'نسائي', 'purchase_price' => 400, 'selling_price' => 600],
            ['name' => 'حجاب',
            'code' => 'HIJAB01', 'category' => 'نسائي', 'purchase_price' => 80, 'selling_price' => 120],
            ['name' => 'ملابس أطفال', 'code' => 'KIDS01', 'category' => 'أطفال', 'purchase_price' => 120, 'selling_price' => 180],
            ['name' => 'عصير برتقال 1ل', 'code' => 'JUICE01', 'category' => 'مشروبات', 'purchase_price' => 15, 'selling_price' => 25],
            ['name' => 'مياه معدنية 600مل', 'code' => 'WATER01', 'category' => 'مشروبات', 'purchase_price' => 5, 'selling_price' => 10],
            ['name' => 'نسكافيه', 'code' => 'NESCAFE', 'category' => 'مشروبات', 'purchase_price' => 25, 'selling_price' => 35],
            ['name' => 'شيبس', 'code' => 'CHIPS01', 'category' => 'مأكولات', 'purchase_price' => 10, 'selling_price' => 18],
            ['name' => 'بسكوت', 'code' => 'BISCUIT', 'category' => 'مأكولات', 'purchase_price' => 12, 'selling_price' => 20],
            ['name' => 'شوكلاتة', 'code' => 'CHOCO', 'category' => 'مأكولات', 'purchase_price' => 15, 'selling_price' => 25],
            ['name' => 'حليب مركز', 'code' => 'MILK01', 'category' => 'معلبات', 'purchase_price' => 20, 'selling_price' => 30],
            ['name' => 'صلصة طماطم', 'code' => 'TOMATO', 'category' => 'معلبات', 'purchase_price' => 8, 'selling_price' => 15],
            ['name' => 'ورق أ4', 'code' => 'PAPER01', 'category' => 'مستلزمات مكتبية', 'purchase_price' => 30, 'selling_price' => 50],
            ['name' => 'قلم جاف', 'code' => 'PEN01', 'category' => 'مستلزمات مكتبية', 'purchase_price' => 2, 'selling_price' => 5],
            ['name' => 'ملزمات', 'code' => 'STAPLE', 'category' => 'مستلزمات مكتبية', 'purchase_price' => 5, 'selling_price' => 10],
            ['name' => 'مصباح ليد', 'code' => 'LED01', 'category' => 'أدوات كهربائية', 'purchase_price' => 50, 'selling_price' => 80],
            ['name' => 'م ventilateur', 'code' => 'FAN01', 'category' => 'أدوات كهربائية', 'purchase_price' => 400, 'selling_price' => 550],
            ['name' => 'غسالة صحون', 'code' => 'DWASH', 'category' => 'أدوات كهربائية', 'purchase_price' => 5000, 'selling_price' => 6500],
            ['name' => 'شامبو', 'code' => 'SHAMPOO', 'category' => 'مستحضرات تجميل', 'purchase_price' => 50, 'selling_price' => 80],
            ['name' => 'كريم مرطب', 'code' => 'CREAM', 'category' => 'مستحضرات تجميل', 'purchase_price' => 60, 'selling_price' => 100],
            ['name' => 'لعبة فيديو', 'code' => 'GAME01', 'category' => 'ألعاب', 'purchase_price' => 1500, 'selling_price' => 2000],
            ['name' => 'لعبة أطفال', 'code' => 'TOY01', 'category' => 'أطفال', 'purchase_price' => 100, 'selling_price' => 180],
        ];

        $categories = \App\Models\Category::pluck('id', 'name');
        
        foreach ($products as $index => $prod) {
            $categoryName = $prod['category'];
            $categoryId = $categories[$categoryName] ?? $categories->first();
            
            $product = Product::create([
                'name' => $prod['name'],
                'code' => $prod['code'],
                'sku' => $prod['code'] . '-SKU',
                'barcode' => '62' . str_pad($index, 10, '0', STR_PAD_LEFT),
                'category_id' => $categoryId,
                'purchase_price' => $prod['purchase_price'],
                'selling_price' => $prod['selling_price'],
                'min_selling_price' => $prod['purchase_price'] * 1.1,
                'tax_rate' => 14,
                'stock_alert_quantity' => 10,
                'is_active' => true,
                'status' => 'active',
            ]);

            ProductBaseUnit::create([
                'product_id' => $product->id,
                'base_unit_type' => 'piece',
                'base_unit_code' => 'pc',
                'base_unit_label' => 'قطعة',
                'base_purchase_price' => $prod['purchase_price'],
                'base_selling_price' => $prod['selling_price'],
                'is_active' => true,
            ]);

            $warehouses = \App\Models\Warehouse::all();
            foreach ($warehouses as $warehouse) {
                $qty = rand(50, 500);
                ProductWarehouse::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $qty,
                    'reserved_quantity' => rand(0, 10),
                    'available_quantity' => $qty - rand(0, 10),
                    'average_cost' => $prod['purchase_price'],
                    'min_stock' => 10,
                ]);
            }
        }
    }
}