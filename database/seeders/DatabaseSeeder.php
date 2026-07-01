<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (function_exists('tenant') && tenant()) {
            // إذا كنا داخل قاعدة بيانات المستأجر (العميل): تلقيم المستخدمين والبيانات التجريبية
            $this->call([
                UserSeeder::class,
                TestDataSeeder::class,
            ]);
        } else {
            // إذا كنا في قاعدة البيانات المركزية (السوبر أدمن): تلقيم الباقات فقط
            $this->call([
                PlanSeeder::class,
            ]);
        }
    }
}
