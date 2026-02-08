<?php
// database/migrations/2026_02_08_170002_create_product_selling_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 📦 جدول وحدات البيع (المحسوبة من الوحدة الأساسية)
     * 
     * هنا بنحفظ كل وحدات البيع المختلفة (شيكارة، كرتونة، علبة...)
     * وسعرها المحسوب تلقائياً من سعر الوحدة الأساسية
     */
    public function up(): void
    {
        Schema::create('product_selling_units', function (Blueprint $table) {
            $table->id();
            
            // ===== العلاقات =====
            $table->unsignedBigInteger('product_id')->comment('رقم المنتج');
            $table->unsignedBigInteger('base_unit_id')->comment('رقم الوحدة الأساسية');
            
            // ===== معلومات وحدة البيع =====
            $table->string('unit_code', 50)->index()->comment('كود وحدة البيع: bag_50kg, box_12pcs, carton_24L');
            $table->string('unit_label', 100)->comment('اسم وحدة البيع بالعربي: شيكارة 50 كجم، علبة 12 قطعة');
            
            // ===== معامل التحويل =====
            $table->decimal('conversion_factor', 15, 6)->comment('معامل التحويل من الوحدة الأساسية');
            /*
             * أمثلة على conversion_factor:
             * - شيكارة 50 كجم من طن (1000 كجم) = 50 ÷ 1000 = 0.05
             * - علبة 12 قطعة من قطعة = 12
             * - كرتونة 24 لتر من لتر = 24
             * - جوال 25 كجم من كيلو = 25
             */
            
            $table->decimal('quantity_in_base_unit', 15, 6)->comment('الكمية بالوحدة الأساسية');
            /*
             * أمثلة على quantity_in_base_unit:
             * - شيكارة تحتوي على 50 كجم
             * - علبة تحتوي على 12 قطعة
             * - كرتونة تحتوي على 24 لتر
             */
            
            // ===== الأسعار (محسوبة تلقائياً) =====
            $table->decimal('unit_purchase_price', 15, 2)->default(0)->comment('سعر شراء الوحدة');
            $table->decimal('unit_selling_price', 15, 2)->default(0)->comment('سعر بيع الوحدة');
            $table->boolean('auto_calculate_price')->default(true)->index()->comment('حساب السعر تلقائياً من الوحدة الأساسية');
            $table->string('unit_name');
            // ===== إعدادات الوحدة =====
            $table->string('barcode', 100)->nullable()->unique()->index()->comment('باركود وحدة البيع');
            $table->boolean('is_default')->default(false)->comment('وحدة البيع الافتراضية');
            $table->boolean('is_active')->default(true)->index()->comment('فعّال');
            $table->integer('display_order')->default(0)->index()->comment('ترتيب العرض');
            $table->text('notes')->nullable()->comment('ملاحظات');
            
            // ===== Audit Trail =====
            $table->unsignedBigInteger('created_by')->nullable()->comment('المستخدم اللي أنشأ السجل');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('المستخدم اللي عدّل السجل');
            $table->timestamps();
            $table->softDeletes();
            
            // ===== INDEXES المحسّنة للأداء العالي =====
            $table->index(['product_id', 'is_active'], 'idx_product_active');
            $table->index(['product_id', 'is_default'], 'idx_product_default');
            $table->index(['base_unit_id', 'is_active'], 'idx_base_active');
            $table->index(['unit_code', 'product_id'], 'idx_unit_product');
            $table->index(['auto_calculate_price', 'is_active'], 'idx_auto_calc_active');
            $table->index(['is_active', 'display_order'], 'idx_active_display');
            $table->index('created_at', 'idx_created_at');
            
            // Unique constraint - كل منتج ميكونش ليه نفس الوحدة مرتين
            $table->unique(['product_id', 'unit_code'], 'unique_product_unit_code');
            
            // ===== FOREIGN KEYS =====
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('base_unit_id')
                  ->references('id')
                  ->on('product_base_units')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
                  
            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
                  
            $table->foreign('updated_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
        
        // ===== تحسين الأداء =====
        DB::statement('ALTER TABLE product_selling_units ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
        
        // FULLTEXT للبحث النصي السريع
        DB::statement('ALTER TABLE product_selling_units ADD FULLTEXT INDEX ft_unit_label (unit_label)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_selling_units');
    }
};