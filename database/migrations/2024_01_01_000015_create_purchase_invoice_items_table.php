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
        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            
            // العلاقات
            $table->foreignId('purchase_invoice_id')
                  ->constrained('purchase_invoices')
                  ->onDelete('cascade')
                  ->comment('فاتورة الشراء');
                  
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('restrict')
                  ->comment('المنتج');
                  
            $table->foreignId('purchase_unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->onDelete('set null')
                  ->comment('وحدة الشراء');
            
            // معلومات الوحدة والتحويل
            $table->string('unit_code', 20)
                  ->nullable()
                  ->comment('كود الوحدة');
                  
            $table->decimal('conversion_factor', 10, 4)
                  ->default(1)
                  ->comment('معامل التحويل للوحدة الأساسية');
            
            // الكميات
            $table->decimal('quantity', 10, 3)
                  ->comment('الكمية بوحدة الشراء');
                  
            $table->decimal('base_quantity', 15, 3)
                  ->comment('الكمية بالوحدة الأساسية');
            
            // الأسعار
            $table->decimal('unit_price', 15, 2)
                  ->comment('سعر الوحدة');
                  
            $table->decimal('unit_cost', 15, 2)
                  ->default(0)
                  ->comment('تكلفة الوحدة (قد تختلف عن السعر)');
            
            // الخصم (نظام موحد)
            $table->enum('discount_type', ['fixed', 'percentage'])
                  ->default('fixed')
                  ->comment('نوع الخصم: ثابت أو نسبة مئوية');
                  
            $table->decimal('discount_value', 15, 2)
                  ->default(0)
                  ->comment('قيمة أو نسبة الخصم');
                  
            $table->decimal('discount_percent', 5, 2)
                  ->default(0)
                  ->comment('نسبة الخصم % (محسوبة)');
                  
            $table->decimal('discount_amount', 15, 2)
                  ->default(0)
                  ->comment('مبلغ الخصم النهائي');
            
            // الضريبة
            $table->decimal('tax_rate', 5, 2)
                  ->default(0)
                  ->comment('نسبة الضريبة %');
                  
            $table->decimal('tax_amount', 15, 2)
                  ->default(0)
                  ->comment('مبلغ الضريبة');
            
            // الإجماليات
            $table->decimal('subtotal', 15, 2)
                  ->default(0)
                  ->comment('المجموع قبل الضريبة');
                  
            $table->decimal('total', 15, 2)
                  ->default(0)
                  ->comment('الإجمالي النهائي');
            
            // معلومات إضافية للمخزون
            $table->date('expiry_date')
                  ->nullable()
                  ->comment('تاريخ انتهاء الصلاحية');
                  
            $table->string('batch_number', 100)
                  ->nullable()
                  ->comment('رقم الدفعة/اللوت');
            
            // ملاحظات
            $table->text('notes')
                  ->nullable()
                  ->comment('ملاحظات');
            
            $table->timestamps();
            
            // Indexes للأداء
            $table->index(['purchase_invoice_id', 'product_id']);
            $table->index('purchase_unit_id');
            $table->index('expiry_date');
            $table->index('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
    }
};