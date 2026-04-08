<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            ['name' => 'أحمد محمد', 'code' => 'C001', 'phone' => '01012345670', 'address' => 'القاهرة', 'balance' => 5000, 'credit_limit' => 20000],
            ['name' => 'محمد علي', 'code' => 'C002', 'phone' => '01012345671', 'address' => 'الجيزة', 'balance' => 3000, 'credit_limit' => 15000],
            ['name' => 'علي حسن', 'code' => 'C003', 'phone' => '01012345672', 'address' => 'الإسكندرية', 'balance' => 0, 'credit_limit' => 10000],
            ['name' => 'شركة الأمل للتجارة', 'code' => 'C004', 'phone' => '01012345673', 'address' => 'القاهرة', 'balance' => 25000, 'credit_limit' => 100000],
            ['name' => 'شركة النجاح', 'code' => 'C005', 'phone' => '01012345674', 'address' => 'الجيزة', 'balance' => 15000, 'credit_limit' => 50000],
            ['name' => 'خالد عمر', 'code' => 'C006', 'phone' => '01012345675', 'address' => 'المنصورة', 'balance' => 2000, 'credit_limit' => 8000],
            ['name' => 'ياسر إبراهيم', 'code' => 'C007', 'phone' => '01012345676', 'address' => 'طنطا', 'balance' => 0, 'credit_limit' => 5000],
            ['name' => 'شركة النور', 'code' => 'C008', 'phone' => '01012345677', 'address' => 'الإسكندرية', 'balance' => 8000, 'credit_limit' => 30000],
            ['name' => 'مصطفي صلاح', 'code' => 'C009', 'phone' => '01012345678', 'address' => 'المنوفية', 'balance' => 1500, 'credit_limit' => 6000],
            ['name' => 'عمرو دياب', 'code' => 'C010', 'phone' => '01012345679', 'address' => 'القاهرة', 'balance' => 10000, 'credit_limit' => 25000],
        ];

        foreach ($customers as $customer) {
            Customer::create([
                'name' => $customer['name'],
                'code' => $customer['code'],
                'phone' => $customer['phone'],
                'email' => strtolower(str_replace(' ', '.', $customer['name'])) . '@example.com',
                'address' => $customer['address'],
                'balance' => $customer['balance'],
                'credit_limit' => $customer['credit_limit'],
                'is_active' => true,
            ]);
        }
    }
}
