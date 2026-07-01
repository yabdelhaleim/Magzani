<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🎯 Migration 5: جدول أسعار الموردين (اختياري)
 * 
 * التاريخ: 2026_01_31_000005
 * الاسم: create_supplier_product_prices_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplier_product_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('supplier_id')->comment('رقم المورد');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('base_unit', 50);
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('EGP');
            $table->decimal('min_order_quantity', 15, 2)->default(0);
            $table->integer('delivery_time_days')->default(0)->comment('مدة التوصيل بالأيام');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['supplier_id', 'product_id'], 'idx_supplier_product');
            $table->index(['valid_from', 'valid_to'], 'idx_valid_dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_product_prices');
    }
};