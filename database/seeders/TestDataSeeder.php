<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting to seed test data...');

        $this->seedRolesAndPermissions();
        $this->seedUsers();
        $this->seedWarehouses();
        $this->seedCategories();
        $this->seedProducts();
        $this->seedCustomers();
        $this->seedSuppliers();
        $this->seedSalesInvoices();
        $this->seedPurchaseInvoices();

        $this->command->info('Test data seeding completed!');
    }

    private function seedRolesAndPermissions(): void
    {
        $existingRoles = DB::table('roles')->count();
        if ($existingRoles === 0) {
            DB::table('roles')->insert([
                ['name' => 'مدير نظام', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'محاسب', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'موظف مبيعات', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'موظف مشتريات', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مستودع', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $existingPerms = DB::table('permissions')->count();
        if ($existingPerms === 0) {
            $permissions = [
                ['name' => 'إدارة المستخدمين'],
                ['name' => 'عرض المستخدمين'],
                ['name' => 'إدارة المخزون'],
                ['name' => 'عرض المخزون'],
                ['name' => 'إدارة المبيعات'],
                ['name' => 'عرض المبيعات'],
                ['name' => 'إدارة المشتريات'],
                ['name' => 'عرض المشتريات'],
                ['name' => 'التقارير'],
                ['name' => 'الإعدادات'],
            ];

            foreach ($permissions as $perm) {
                DB::table('permissions')->insert(array_merge($perm, ['created_at' => now(), 'updated_at' => now()]));
            }
        }
    }

    private function seedUsers(): void
    {
        if (DB::table('users')->where('email', 'ahmed@magzany.com')->count() === 0) {
            DB::table('users')->insert([
                'name' => 'أحمد محمد',
                'email' => 'ahmed@magzany.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedWarehouses(): void
    {
        if (DB::table('warehouses')->count() === 0) {
            DB::table('warehouses')->insert([
                ['name' => 'المستودع الرئيسي', 'code' => 'WH001', 'status' => 'active', 'city' => 'القاهرة', 'area' => 'مدينة السلام', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مستودع الفرع', 'code' => 'WH002', 'status' => 'active', 'city' => 'الجيزة', 'area' => 'الشيخ زايد', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مستودع الصيانة', 'code' => 'WH003', 'status' => 'maintenance', 'city' => 'الإسكندرية', 'area' => 'العجمى', 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    private function seedCategories(): void
    {
        if (DB::table('categories')->count() === 0) {
            $categories = [
                ['name' => 'الإلكترونيات', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ملابس', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'أحذية', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مواد غذائية', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مشروبات', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($categories as $cat) {
                DB::table('categories')->insert($cat);
            }
        }
    }

    private function seedProducts(): void
    {
        $electronicsCat = DB::table('categories')->where('name', 'الإلكترونيات')->value('id');
        $clothingCat = DB::table('categories')->where('name', 'ملابس')->value('id');
        $shoesCat = DB::table('categories')->where('name', 'أحذية')->value('id');
        $foodCat = DB::table('categories')->where('name', 'مواد غذائية')->value('id');

        $mainWarehouse = DB::table('warehouses')->where('name', 'المستودع الرئيسي')->value('id');

        $products = [
            ['name' => 'لابتوب_LENOVO', 'code' => 'PROD001', 'sku' => 'LENOVO-LAPTOP', 'category_id' => $electronicsCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 15000, 'selling_price' => 18000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'موبايل_SAMSUNG', 'code' => 'PROD002', 'sku' => 'Samsung-Galaxy', 'category_id' => $electronicsCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 8000, 'selling_price' => 9500, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'تلفزيون_LED', 'code' => 'PROD003', 'sku' => 'LED-TV-55', 'category_id' => $electronicsCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 5000, 'selling_price' => 6200, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'قميص رجالي', 'code' => 'PROD004', 'sku' => 'SHIRT-M', 'category_id' => $clothingCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 150, 'selling_price' => 250, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'بناطيل جينز', 'code' => 'PROD005', 'sku' => 'JEANS-32', 'category_id' => $clothingCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 200, 'selling_price' => 350, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'حذاء رياضي', 'code' => 'PROD006', 'sku' => 'SPORT-SHOE', 'category_id' => $shoesCat, 'base_unit' => 'pair', 'base_unit_label' => 'زوج', 'purchase_price' => 300, 'selling_price' => 450, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'شوكولاتة', 'code' => 'PROD007', 'sku' => 'CHOC-BAR', 'category_id' => $foodCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 15, 'selling_price' => 25, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'عصير برتقال', 'code' => 'PROD008', 'sku' => 'JUICE-ORANGE', 'category_id' => $foodCat, 'base_unit' => 'piece', 'base_unit_label' => 'قطعة', 'purchase_price' => 8, 'selling_price' => 15, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        if (DB::table('products')->count() === 0) {
            $productIds = [];
            foreach ($products as $product) {
                $productIds[] = DB::table('products')->insertGetId($product);
            }

            foreach ($productIds as $index => $productId) {
                $quantity = rand(50, 500);
                DB::table('product_warehouse')->insert([
                    'product_id' => $productId,
                    'warehouse_id' => $mainWarehouse,
                    'quantity' => $quantity,
                    'reserved_quantity' => 0,
                    'average_cost' => rand(50, 15000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedCustomers(): void
    {
        if (DB::table('customers')->count() === 0) {
            $customers = [
                ['name' => 'أحمد علي', 'code' => 'CUST001', 'phone' => '01012345671', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'محمد خالد', 'code' => 'CUST002', 'phone' => '01012345672', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'صلاح الدين', 'code' => 'CUST003', 'phone' => '01012345673', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => ' شركة التقنية', 'code' => 'CUST004', 'phone' => '01012345674', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مؤسسة الأمل', 'code' => 'CUST005', 'phone' => '01012345675', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'ياسر محمد', 'code' => 'CUST006', 'phone' => '01012345676', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'علياء تريدينج', 'code' => 'CUST007', 'phone' => '01012345677', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'سمير للتوكيلات', 'code' => 'CUST008', 'phone' => '01012345678', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'حسين وأولاده', 'code' => 'CUST009', 'phone' => '01012345679', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'شركة النور', 'code' => 'CUST010', 'phone' => '01012345680', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($customers as $customer) {
                DB::table('customers')->insert($customer);
            }
        }
    }

    private function seedSuppliers(): void
    {
        if (DB::table('suppliers')->count() === 0) {
            $suppliers = [
                ['name' => 'شركة التقنية العالمية', 'code' => 'SUP001', 'phone' => '01112345671', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'موردين الخليج', 'code' => 'SUP002', 'phone' => '01112345672', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'شركة الغيث', 'code' => 'SUP003', 'phone' => '01112345673', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'مؤسسة النور', 'code' => 'SUP004', 'phone' => '01112345674', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'شركة الوفاء', 'code' => 'SUP005', 'phone' => '01112345675', 'balance' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ];

            foreach ($suppliers as $supplier) {
                DB::table('suppliers')->insert($supplier);
            }
        }
    }

    private function seedSalesInvoices(): void
    {
        if (DB::table('sales_invoices')->count() > 0) {
            $this->command->info('Sales invoices already exist, skipping...');
            return;
        }

        $users = DB::table('users')->pluck('id');
        $customers = DB::table('customers')->pluck('id');
        $warehouses = DB::table('warehouses')->where('is_active', true)->pluck('id');
        $products = DB::table('products')->where('is_active', true)->get();

        for ($i = 1; $i <= 15; $i++) {
            $invoiceNumber = 'INV-' . date('Y') . str_pad($i, 5, '0', STR_PAD_LEFT);
            $customerId = $customers->random();
            $warehouseId = $warehouses->random();
            $userId = $users->random();

            $itemsCount = rand(1, 4);
            $items = [];
            $subtotal = 0;

            $selectedProducts = $products->random($itemsCount);
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 10);
                $unitPrice = $product->selling_price;
                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'base_quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                    'total' => $lineTotal,
                ];
            }

            $discountAmount = $subtotal * (rand(0, 10) / 100);
            $taxAmount = ($subtotal - $discountAmount) * 0.14;
            $total = $subtotal - $discountAmount + $taxAmount;
            $paid = rand(0, 1) ? $total : ($total * rand(20, 80) / 100);

            $invoiceId = DB::table('sales_invoices')->insertGetId([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customerId,
                'warehouse_id' => $warehouseId,
                'invoice_date' => now()->subDays(rand(0, 30)),
                'subtotal' => $subtotal,
                'discount_type' => 'percentage',
                'discount_value' => $discountAmount > 0 ? rand(5, 10) : 0,
                'discount_amount' => $discountAmount,
                'tax_rate' => 14,
                'tax_amount' => $taxAmount,
                'shipping_cost' => rand(0, 1) ? rand(20, 100) : 0,
                'other_charges' => 0,
                'total' => $total,
                'paid' => $paid,
                'status' => 'confirmed',
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'created_by' => $userId,
                'confirmed_by' => $userId,
                'confirmed_at' => now()->subDays(rand(0, 25)),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('sales_invoice_items')->insert([
                    'sales_invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $item['base_quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'total' => $item['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($paid > 0 && $paid < $total) {
                try {
                    if (Schema::hasTable('payments')) {
                        DB::table('payments')->insert([
                            'payment_number' => 'PAY-' . $invoiceNumber,
                            'payable_type' => 'App\\Models\\SalesInvoice',
                            'payable_id' => $invoiceId,
                            'amount' => $paid,
                            'payment_method' => 'cash',
                            'reference_number' => 'PAY-' . $invoiceNumber,
                            'payment_date' => now()->subDays(rand(0, 20)),
                            'notes' => 'دفع نقدي',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Skip payments if table doesn't exist
                }
            }
        }
    }

    private function seedPurchaseInvoices(): void
    {
        if (DB::table('purchase_invoices')->count() > 0) {
            $this->command->info('Purchase invoices already exist, skipping...');
            return;
        }

        $users = DB::table('users')->pluck('id');
        $suppliers = DB::table('suppliers')->pluck('id');
        $warehouses = DB::table('warehouses')->where('is_active', true)->pluck('id');
        $products = DB::table('products')->where('is_active', true)->get();

        for ($i = 1; $i <= 10; $i++) {
            $invoiceNumber = 'PINV-' . date('Y') . str_pad($i, 5, '0', STR_PAD_LEFT);
            $supplierId = $suppliers->random();
            $warehouseId = $warehouses->random();
            $userId = $users->random();

            $itemsCount = rand(1, 3);
            $items = [];
            $subtotal = 0;

            $selectedProducts = $products->random($itemsCount);
            foreach ($selectedProducts as $product) {
                $quantity = rand(10, 50);
                $unitPrice = $product->purchase_price;
                $lineTotal = $quantity * $unitPrice;
                $subtotal += $lineTotal;

                $items[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'base_quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $lineTotal,
                    'total' => $lineTotal,
                ];
            }

            $discountAmount = $subtotal * (rand(0, 8) / 100);
            $taxAmount = ($subtotal - $discountAmount) * 0.14;
            $total = $subtotal - $discountAmount + $taxAmount;
            $paid = rand(0, 1) ? $total : ($total * rand(30, 70) / 100);

            $invoiceId = DB::table('purchase_invoices')->insertGetId([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $supplierId,
                'warehouse_id' => $warehouseId,
                'invoice_date' => now()->subDays(rand(0, 30)),
                'subtotal' => $subtotal,
                'discount_type' => 'percentage',
                'discount_value' => $discountAmount > 0 ? rand(3, 8) : 0,
                'discount_amount' => $discountAmount,
                'tax_rate' => 14,
                'tax_amount' => $taxAmount,
                'shipping_cost' => rand(50, 200),
                'other_charges' => 0,
                'total' => $total,
                'paid' => $paid,
                'status' => 'confirmed',
                'payment_status' => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
                'created_by' => $userId,
                'confirmed_by' => $userId,
                'confirmed_at' => now()->subDays(rand(0, 25)),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);

            foreach ($items as $item) {
                DB::table('purchase_invoice_items')->insert([
                    'purchase_invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $item['base_quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'total' => $item['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($paid > 0 && $paid < $total) {
                try {
                    if (Schema::hasTable('supplier_payments')) {
                        DB::table('supplier_payments')->insert([
                            'supplier_id' => $supplierId,
                            'method' => 'bank_transfer',
                            'amount' => $paid,
                            'payment_date' => now()->subDays(rand(0, 20)),
                            'notes' => 'تحويل بنكي',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Skip if table doesn't have all columns
                }
            }
        }
    }
}