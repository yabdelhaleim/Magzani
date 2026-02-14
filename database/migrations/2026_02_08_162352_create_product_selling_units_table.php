<?php
// database/migrations/2026_02_08_170002_create_product_selling_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_selling_units', function (Blueprint $table) {
            $table->id();
            
            // ===== العلاقات =====
            $table->unsignedBigInteger('product_id')->comment('رقم المنتج');
            $table->unsignedBigInteger('base_unit_id')->comment('رقم الوحدة الأساسية');
            
            // ===== معلومات وحدة البيع =====
            $table->string('unit_name', 100)->comment('اسم الوحدة');
            $table->string('unit_code', 50)->index()->comment('كود الوحدة');
            $table->string('unit_label', 100)->nullable()->comment('اسم الوحدة بالعربي');
            
            // ===== معامل التحويل =====
            $table->decimal('conversion_factor', 15, 6)->comment('معامل التحويل');
            $table->decimal('quantity_in_base_unit', 15, 6)->comment('الكمية بالوحدة الأساسية');
            
            // ===== الأسعار =====
            $table->decimal('unit_purchase_price', 15, 2)->default(0);
            $table->decimal('unit_selling_price', 15, 2)->default(0);
            $table->boolean('auto_calculate_price')->default(true)->index();
            
            // ===== 🔥 الحقول الجديدة =====
            $table->boolean('is_base')->default(false)->comment('هل هي الوحدة الأساسية؟');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->integer('display_order')->default(0)->index();
            
            $table->string('barcode', 100)->nullable()->unique()->index();
            $table->text('notes')->nullable();
            
            // ===== Audit Trail =====
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // ===== INDEXES =====
            $table->index(['product_id', 'is_active'], 'idx_product_active');
            $table->index(['product_id', 'is_default'], 'idx_product_default');
            $table->index(['product_id', 'is_base'], 'idx_product_base'); // 🔥 جديد
            $table->index(['base_unit_id', 'is_active'], 'idx_base_active');
            $table->index(['unit_code', 'product_id'], 'idx_unit_product');
            
            // Unique constraint
            $table->unique(['product_id', 'unit_code'], 'unique_product_unit_code');
            
            // ===== FOREIGN KEYS =====
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade')->onUpdate('cascade');
                  
            $table->foreign('base_unit_id')
                  ->references('id')->on('product_base_units')
                  ->onDelete('cascade')->onUpdate('cascade');
                  
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onDelete('set null')->onUpdate('cascade');
                  
            $table->foreign('updated_by')
                  ->references('id')->on('users')
                  ->onDelete('set null')->onUpdate('cascade');
        });
        
        DB::statement('ALTER TABLE product_selling_units ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
        DB::statement('ALTER TABLE product_selling_units ADD FULLTEXT INDEX ft_unit_name (unit_name, unit_label)');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_selling_units');
    }
};