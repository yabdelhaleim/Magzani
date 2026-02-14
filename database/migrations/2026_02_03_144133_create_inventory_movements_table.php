<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            
            // معلومات الحركة الأساسية
            $table->string('movement_number', 50)->unique()->comment('رقم الحركة');
            $table->enum('movement_type', [
                'purchase',              // شراء
                'sale',                  // بيع
                'return_in',             // مرتجع وارد
                'return_out',            // مرتجع صادر
                'transfer_in',           // تحويل وارد
                'transfer_out',          // تحويل صادر
                'adjustment',            // تسوية
                'damage',                // تالف
                'expired',               // منتهي الصلاحية
                'return_from_transfer',  // ✅ عكس تحويل (وارد)
                'transfer_reversed',     // ✅ عكس تحويل (صادر)
                'production',            // ✅ إنتاج
                'consumption'            // ✅ استهلاك
            ])->comment('نوع الحركة');
            
            // العلاقات الأساسية
            $table->foreignId('product_id')->constrained()->comment('المنتج');
            $table->foreignId('warehouse_id')->constrained()->comment('المخزن');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->comment('من مخزن (للتحويلات)');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->comment('إلى مخزن (للتحويلات)');
            
            // ✅ Polymorphic Relations - لازم نضيفهم قبل الـ Index
            $table->string('reference_type')->nullable()->comment('نوع المرجع (Polymorphic)');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('معرف المرجع (Polymorphic)');
            
            // المراجع القديمة (للتوافق مع الكود القديم)
            $table->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices')->comment('فاتورة شراء');
            $table->foreignId('sales_invoice_id')->nullable()->constrained('sales_invoices')->comment('فاتورة بيع');
            $table->foreignId('transfer_id')->nullable()->constrained('warehouse_transfers')->nullOnDelete()->comment('أمر تحويل');
            
            // ✅ الكميات (الأربعة مع بعض)
            $table->decimal('quantity', 15, 3)->comment('الكمية المطلقة');
            $table->decimal('quantity_change', 15, 3)->comment('التغيير في الكمية (+ أو -)');
            $table->decimal('quantity_before', 15, 3)->default(0)->comment('الكمية قبل الحركة');
            $table->decimal('quantity_after', 15, 3)->default(0)->comment('الكمية بعد الحركة');

            // ✅ التكاليف والأسعار
            $table->decimal('unit_cost', 15, 2)->default(0)->comment('تكلفة الوحدة');
            $table->decimal('unit_price', 15, 2)->default(0)->comment('سعر الوحدة');
            $table->decimal('total_cost', 15, 2)->default(0)->comment('إجمالي التكلفة');
            $table->decimal('total_price', 15, 2)->default(0)->comment('إجمالي السعر');
            
            // معلومات إضافية
            $table->date('movement_date')->comment('تاريخ الحركة');
            $table->date('expiry_date')->nullable()->comment('تاريخ انتهاء الصلاحية');
            $table->string('batch_number', 100)->nullable()->comment('رقم الدفعة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->text('reason')->nullable()->comment('السبب (للتسويات والتالف)');
            
            // ✅ الأرشفة
            $table->boolean('archived')->default(false)->comment('هل الحركة مؤرشفة؟');
            
            // معلومات المستخدم
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            // ✅ الفهارس (Indexes) - بعد ما كل الأعمدة اتعملت
            $table->index('movement_number');
            $table->index('movement_type');
            $table->index('movement_date');
            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('quantity_change');
            $table->index('archived'); // ✅ Index للأرشفة
            $table->index(['product_id', 'warehouse_id']);
            $table->index(['movement_type', 'movement_date']);
            $table->index(['reference_type', 'reference_id'], 'idx_reference');
            $table->index(['warehouse_id', 'product_id', 'movement_date'], 'idx_movement_lookup');
            $table->index(['warehouse_id', 'archived', 'movement_date'], 'idx_warehouse_active'); // ✅ للاستعلامات السريعة
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};