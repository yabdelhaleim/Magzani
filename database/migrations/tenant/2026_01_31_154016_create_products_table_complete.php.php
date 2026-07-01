<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🎯 Migration 1: جدول تحويلات الوحدات
 * 
 * التاريخ: 2026_01_31_000001
 * الاسم: create_unit_conversions_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('from_unit', 50)->comment('الوحدة المصدر');
            $table->string('to_unit', 50)->comment('الوحدة الهدف');
            $table->decimal('conversion_factor', 15, 6)->comment('معامل التحويل');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->unique(['from_unit', 'to_unit'], 'unique_conversion');
            $table->index('from_unit', 'idx_from_unit');
            $table->index('to_unit', 'idx_to_unit');
        });

        // ===================================
        // 📝 بيانات أساسية للتحويلات
        // ===================================
        
        DB::table('unit_conversions')->insert([
            // الوزن
            ['from_unit' => 'ton', 'to_unit' => 'kg', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'ton', 'to_unit' => 'gram', 'conversion_factor' => 1000000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'ton', 'to_unit' => 'quintal', 'conversion_factor' => 10.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'kg', 'to_unit' => 'gram', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'quintal', 'to_unit' => 'kg', 'conversion_factor' => 100.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // الحجم
            ['from_unit' => 'liter', 'to_unit' => 'milliliter', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'gallon', 'to_unit' => 'liter', 'conversion_factor' => 3.785000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // الطول
            ['from_unit' => 'meter', 'to_unit' => 'cm', 'conversion_factor' => 100.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'meter', 'to_unit' => 'millimeter', 'conversion_factor' => 1000.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'cm', 'to_unit' => 'millimeter', 'conversion_factor' => 10.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'inch', 'to_unit' => 'cm', 'conversion_factor' => 2.540000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            
            // العدد
            ['from_unit' => 'dozen', 'to_unit' => 'piece', 'conversion_factor' => 12.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['from_unit' => 'carton', 'to_unit' => 'piece', 'conversion_factor' => 24.000000, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};