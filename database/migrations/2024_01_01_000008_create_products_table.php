<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // ===== المعلومات الأساسية =====
            $table->string('code', 100)->unique()->index()->comment('كود المنتج');
            $table->string('name', 255)->index()->comment('اسم المنتج');
            $table->string('sku', 100)->unique()->nullable()->index()->comment('رمز المنتج');
            $table->string('barcode', 100)->unique()->nullable()->index()->comment('الباركود');
            
            // ===== التصنيف والعلامة التجارية =====
            $table->string('category', 100)->nullable()->index()->comment('الفئة');
            $table->unsignedBigInteger('category_id')->nullable()->index()->comment('معرف الفئة');
            $table->unsignedBigInteger('brand_id')->nullable()->index()->comment('معرف العلامة التجارية');
            $table->text('description')->nullable()->comment('الوصف');
            
            // ===== الوحدات =====
            $table->string('unit', 50)->default('piece')->index()->comment('الوحدة القديمة');
            $table->string('base_unit', 50)->nullable()->comment('الوحدة الأساسية');
            $table->string('base_unit_label', 50)->nullable()->comment('تسمية الوحدة الأساسية');
            $table->unsignedBigInteger('unit_id')->nullable()->index()->comment('معرف الوحدة');
            
            // ===== الأسعار =====
            $table->decimal('purchase_price', 15, 2)->default(0)->comment('سعر الشراء');
            $table->decimal('selling_price', 15, 2)->default(0)->comment('سعر البيع');
            $table->decimal('min_selling_price', 15, 2)->nullable()->comment('الحد الأدنى لسعر البيع');
            $table->decimal('wholesale_price', 15, 2)->nullable()->comment('سعر الجملة');
            $table->decimal('profit_margin', 15, 2)->default(0)->comment('هامش الربح');
            
            // ===== الضرائب والخصومات =====
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('نسبة الضريبة');
            $table->decimal('default_discount', 5, 2)->default(0)->comment('الخصم الافتراضي');
            
            // ===== المخزون =====
            $table->decimal('stock_alert_quantity', 15, 3)->nullable()->comment('كمية التنبيه');
            $table->decimal('reorder_level', 15, 3)->nullable()->comment('مستوى إعادة الطلب');
            $table->decimal('reorder_quantity', 15, 3)->nullable()->comment('كمية إعادة الطلب');
            $table->decimal('min_stock', 15, 3)->nullable()->comment('الحد الأدنى للمخزون');
            $table->decimal('max_stock', 15, 3)->nullable()->comment('الحد الأقصى للمخزون');
            
            // ===== المواصفات الفيزيائية =====
            $table->decimal('weight', 10, 3)->nullable()->comment('الوزن');
            $table->string('dimensions', 100)->nullable()->comment('الأبعاد');
            
            // ===== الحالة والخصائص =====
            $table->enum('status', ['active', 'inactive'])->default('active')->index()->comment('الحالة القديمة');
            $table->boolean('is_active')->default(true)->index()->comment('نشط');
            $table->boolean('is_featured')->default(false)->comment('مميز');
            $table->boolean('has_expiry')->default(false)->comment('له تاريخ صلاحية');
            $table->boolean('track_serial')->default(false)->comment('تتبع الرقم التسلسلي');
            
            // ===== الصورة والملاحظات =====
            $table->string('image', 500)->nullable()->comment('الصورة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            
            // ===== SEO (اختياري) =====
            $table->string('meta_title', 255)->nullable()->comment('عنوان SEO');
            $table->string('meta_description', 500)->nullable()->comment('وصف SEO');
            $table->string('meta_keywords', 255)->nullable()->comment('كلمات مفتاحية');
            
            // ===== معلومات المستخدم =====
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // ===== التواريخ =====
            $table->timestamps();
            $table->softDeletes();
            
            // ===== Composite Indexes =====
            $table->index(['status', 'is_active']);
            $table->index(['category_id', 'is_active']);
            $table->index(['is_active', 'is_featured']);
            $table->index('created_at');
        });
        
        // تحسين أداء الجدول
        DB::statement('ALTER TABLE products ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};