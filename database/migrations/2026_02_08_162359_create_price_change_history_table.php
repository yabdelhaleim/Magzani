<?php
// database/migrations/2026_02_08_170003_create_price_change_history_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 📜 جدول سجل تغييرات الأسعار
     * 
     * بيحفظ كل تغيير في سعر الوحدة الأساسية
     * وعدد وحدات البيع اللي اتأثرت بالتغيير ده
     */
    public function up(): void
    {
        Schema::create('price_change_history', function (Blueprint $table) {
            $table->id();
            
            // ===== العلاقات =====
            $table->unsignedBigInteger('product_id')->index()->comment('رقم المنتج');
            $table->unsignedBigInteger('base_unit_id')->index()->comment('رقم الوحدة الأساسية');
            
            // ===== التغيير في الوحدة الأساسية =====
            $table->decimal('old_base_purchase_price', 15, 2)->default(0)->comment('سعر الشراء القديم');
            $table->decimal('new_base_purchase_price', 15, 2)->default(0)->comment('سعر الشراء الجديد');
            $table->decimal('old_base_selling_price', 15, 2)->default(0)->comment('سعر البيع القديم');
            $table->decimal('new_base_selling_price', 15, 2)->default(0)->comment('سعر البيع الجديد');
            
            // ===== الفرق (محسوب تلقائياً) =====
            $table->decimal('purchase_price_diff', 15, 2)
                  ->storedAs('new_base_purchase_price - old_base_purchase_price')
                  ->comment('الفرق في سعر الشراء');
                  
            $table->decimal('selling_price_diff', 15, 2)
                  ->storedAs('new_base_selling_price - old_base_selling_price')
                  ->comment('الفرق في سعر البيع');
            
            $table->decimal('diff_percentage', 10, 2)->nullable()->comment('نسبة التغيير %');
            
            // ===== معلومات التغيير =====
            $table->text('change_reason')->nullable()->comment('سبب التغيير');
            $table->integer('affected_selling_units')->default(0)->comment('عدد وحدات البيع اللي اتأثرت');
            $table->boolean('selling_units_updated')->default(false)->index()->comment('تم تحديث وحدات البيع');
            $table->json('affected_units_details')->nullable()->comment('تفاصيل الوحدات المتأثرة (JSON)');
            
            // ===== Audit Trail =====
            $table->unsignedBigInteger('changed_by')->nullable()->comment('المستخدم اللي غيّر السعر');
            $table->timestamp('changed_at')->useCurrent()->index()->comment('وقت التغيير');
            
            // ===== INDEXES للأداء العالي =====
            $table->index(['product_id', 'changed_at'], 'idx_product_changed');
            $table->index(['base_unit_id', 'changed_at'], 'idx_base_changed');
            $table->index(['changed_by', 'changed_at'], 'idx_user_changed');
            $table->index(['changed_at'], 'idx_changed_at');
            $table->index(['selling_units_updated'], 'idx_units_updated');
            
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
                  
            $table->foreign('changed_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });
        
        // ===== تحسين الأداء =====
        DB::statement('ALTER TABLE price_change_history ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_change_history');
    }
};