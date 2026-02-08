<?php
// database/migrations/2026_02_08_170001_create_product_base_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 🏭 جدول الوحدات الأساسية للمنتجات
     * 
     * هنا بنحفظ الوحدة اللي المنتج بيدخل بيها (طن، كيلو، قطعة، لتر...)
     * وسعر الوحدة دي (اللي منها هنحسب باقي الوحدات)
     */
    public function up(): void
    {
        Schema::create('product_base_units', function (Blueprint $table) {
            $table->id();
            
            // ===== معلومات المنتج =====
            $table->unsignedBigInteger('product_id')->unique()->comment('رقم المنتج');
            $table->string('product_code', 100)->index()->comment('كود المنتج (للبحث السريع)');
            
            // ===== الوحدة الأساسية =====
            $table->string('base_unit_type', 50)->index()->comment('نوع الوحدة: weight, volume, piece, length, area');
            $table->string('base_unit_code', 50)->index()->comment('كود الوحدة: ton, kg, g, piece, liter, meter');
            $table->string('base_unit_label', 100)->comment('اسم الوحدة بالعربي: طن، كيلو، قطعة، لتر، متر');
            
            // ===== معامل التحويل (للوزن فقط) =====
            $table->decimal('base_unit_weight_kg', 15, 6)->nullable()->comment('وزن الوحدة بالكيلو (طن=1000، كيلو=1، جرام=0.001)');
            
            // ===== الأسعار =====
            $table->decimal('base_purchase_price', 15, 2)->default(0)->comment('سعر شراء الوحدة الأساسية');
            $table->decimal('base_selling_price', 15, 2)->default(0)->comment('سعر بيع الوحدة الأساسية');
            $table->decimal('profit_margin', 15, 2)->default(0)->comment('هامش الربح %');
            
            // ===== الحالة والصلاحية =====
            $table->boolean('is_active')->default(true)->index()->comment('فعّال');
            $table->boolean('auto_update_selling_units')->default(true)->comment('تحديث تلقائي لوحدات البيع');
            $table->date('effective_from')->index()->comment('تاريخ بداية السريان');
            $table->date('effective_to')->nullable()->index()->comment('تاريخ نهاية السريان');
            
            // ===== معلومات إضافية =====
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            
            // ===== Audit Trail =====
            $table->unsignedBigInteger('created_by')->nullable()->comment('المستخدم اللي أنشأ السجل');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('المستخدم اللي عدّل السجل');
            $table->timestamps();
            $table->softDeletes();
            
            // ===== INDEXES للأداء العالي =====
            $table->index(['product_id', 'is_active'], 'idx_product_active');
            $table->index(['base_unit_type', 'is_active'], 'idx_unit_type_active');
            $table->index(['base_unit_code'], 'idx_unit_code');
            $table->index(['effective_from', 'effective_to'], 'idx_effective_dates');
            $table->index(['is_active', 'effective_from'], 'idx_active_from');
            $table->index('created_at', 'idx_created_at');
            
            // ===== FOREIGN KEYS =====
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
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
        DB::statement('ALTER TABLE product_base_units ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
        
        // FULLTEXT للبحث النصي السريع
        DB::statement('ALTER TABLE product_base_units ADD FULLTEXT INDEX ft_unit_label (base_unit_label)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_base_units');
    }
};