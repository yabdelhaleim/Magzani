<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            
            // العلاقات
            $table->foreignId('sales_invoice_id')
                  ->constrained('sales_invoices')
                  ->onDelete('cascade')
                  ->comment('الفاتورة');
                  
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('restrict')
                  ->comment('المنتج');
                  
            $table->foreignId('selling_unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->onDelete('set null')
                  ->comment('وحدة البيع');
            
            // معلومات الوحدة والتحويل
            $table->string('unit_code', 20)->nullable()->comment('كود الوحدة');
            $table->decimal('conversion_factor', 10, 4)->default(1)->comment('معامل التحويل');
            
            // الكميات
            $table->decimal('quantity', 10, 3)->comment('الكمية بوحدة البيع');
            $table->decimal('base_quantity', 15, 3)->comment('الكمية بالوحدة الأساسية');
            
            // الأسعار
            $table->decimal('unit_price', 15, 2)->comment('سعر الوحدة');
            $table->decimal('cost_price', 15, 2)->default(0)->comment('سعر التكلفة');
            
            // الخصم (نظام موحد)
            $table->enum('discount_type', ['fixed', 'percentage'])
                  ->default('fixed')
                  ->comment('نوع الخصم: ثابت أو نسبة');
            $table->decimal('discount_value', 15, 2)->default(0)->comment('قيمة/نسبة الخصم');
            $table->decimal('discount_amount', 15, 2)->default(0)->comment('مبلغ الخصم المحسوب');
            
            // الضريبة
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('نسبة الضريبة %');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('مبلغ الضريبة');
            
            // الإجماليات
            $table->decimal('subtotal', 15, 2)->default(0)->comment('المجموع قبل الضريبة');
            $table->decimal('total', 15, 2)->default(0)->comment('الإجمالي النهائي');
            
            // ملاحظات
            $table->text('notes')->nullable()->comment('ملاحظات');
            
            $table->timestamps();
            
            // Indexes للأداء
            $table->index(['sales_invoice_id', 'product_id']);
            $table->index('selling_unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_invoice_items');
    }
};
