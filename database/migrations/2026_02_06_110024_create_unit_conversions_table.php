<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // تحقق إذا كان الجدول موجوداً
        if (Schema::hasTable('unit_conversions')) {
            return; // إنهاء التنفيذ إذا كان الجدول موجوداً
        }

        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('from_unit', 50)->index()->comment('الوحدة المصدر');
            $table->string('to_unit', 50)->index()->comment('الوحدة الهدف');
            $table->decimal('conversion_factor', 15, 6)->comment('معامل التحويل');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['from_unit', 'to_unit'], 'unique_conversion');
            
            // Composite index للبحث السريع
            $table->index(['from_unit', 'is_active']);
            $table->index(['to_unit', 'is_active']);
        });

        // البيانات الأساسية للتحويلات
        DB::table('unit_conversions')->insert([
            // ==================
            // الوزن (Weight)
            // ==================
            ['from_unit' => 'ton', 'to_unit' => 'kg', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'ton', 'to_unit' => 'gram', 'conversion_factor' => 1000000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'ton', 'to_unit' => 'quintal', 'conversion_factor' => 10.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'kg', 'to_unit' => 'gram', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'quintal', 'to_unit' => 'kg', 'conversion_factor' => 100.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // التحويلات العكسية للوزن
            ['from_unit' => 'kg', 'to_unit' => 'ton', 'conversion_factor' => 0.001000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'gram', 'to_unit' => 'kg', 'conversion_factor' => 0.001000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'gram', 'to_unit' => 'ton', 'conversion_factor' => 0.000001, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // ==================
            // الحجم (Volume)
            // ==================
            ['from_unit' => 'liter', 'to_unit' => 'milliliter', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'milliliter', 'to_unit' => 'liter', 'conversion_factor' => 0.001000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'gallon', 'to_unit' => 'liter', 'conversion_factor' => 3.785000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'liter', 'to_unit' => 'gallon', 'conversion_factor' => 0.264172, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // ==================
            // الطول (Length)
            // ==================
            ['from_unit' => 'meter', 'to_unit' => 'cm', 'conversion_factor' => 100.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'meter', 'to_unit' => 'millimeter', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'cm', 'to_unit' => 'millimeter', 'conversion_factor' => 10.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'inch', 'to_unit' => 'cm', 'conversion_factor' => 2.540000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // التحويلات العكسية للطول
            ['from_unit' => 'cm', 'to_unit' => 'meter', 'conversion_factor' => 0.010000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'millimeter', 'to_unit' => 'meter', 'conversion_factor' => 0.001000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'millimeter', 'to_unit' => 'cm', 'conversion_factor' => 0.100000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'cm', 'to_unit' => 'inch', 'conversion_factor' => 0.393701, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // ==================
            // العدد (Quantity)
            // ==================
            ['from_unit' => 'dozen', 'to_unit' => 'piece', 'conversion_factor' => 12.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'piece', 'to_unit' => 'dozen', 'conversion_factor' => 0.083333, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'carton', 'to_unit' => 'piece', 'conversion_factor' => 24.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'piece', 'to_unit' => 'carton', 'conversion_factor' => 0.041667, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        
        // تحسين الـ table engine
        DB::statement('ALTER TABLE unit_conversions ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};