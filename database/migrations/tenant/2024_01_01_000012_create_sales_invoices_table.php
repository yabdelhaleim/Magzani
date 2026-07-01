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
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            
            // رقم الفاتورة
            $table->string('invoice_number', 50)
                  ->unique()
                  ->comment('رقم الفاتورة');
            
            // العلاقات الأساسية
            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->onDelete('restrict')
                  ->comment('العميل');
                  
            $table->foreignId('warehouse_id')
                  ->constrained('warehouses')
                  ->onDelete('restrict')
                  ->comment('المخزن');
            
            // التواريخ
            $table->date('invoice_date')
                  ->comment('تاريخ الفاتورة');
                  
            $table->date('due_date')
                  ->nullable()
                  ->comment('تاريخ الاستحقاق');
            
            // المبالغ المالية
            $table->decimal('subtotal', 15, 2)
                  ->default(0)
                  ->comment('المجموع الفرعي (قبل الخصم والضريبة)');
            
            // الخصم
            $table->enum('discount_type', ['fixed', 'percentage'])
                  ->default('fixed')
                  ->comment('نوع الخصم: ثابت أو نسبة');
                  
            $table->decimal('discount_value', 15, 2)
                  ->default(0)
                  ->comment('قيمة أو نسبة الخصم');
                  
            $table->decimal('discount_amount', 15, 2)
                  ->default(0)
                  ->comment('مبلغ الخصم المحسوب');
            
            // الضريبة
            $table->decimal('tax_rate', 5, 2)
                  ->default(0)
                  ->comment('نسبة الضريبة %');
                  
            $table->decimal('tax_amount', 15, 2)
                  ->default(0)
                  ->comment('مبلغ الضريبة');
            
            // مصاريف إضافية
            $table->decimal('shipping_cost', 15, 2)
                  ->default(0)
                  ->comment('تكلفة الشحن');
                  
            $table->decimal('other_charges', 15, 2)
                  ->default(0)
                  ->comment('مصاريف أخرى');
            
            // الإجمالي والمدفوع
            $table->decimal('total', 15, 2)
                  ->default(0)
                  ->comment('الإجمالي النهائي');
                  
            $table->decimal('paid', 15, 2)
                  ->default(0)
                  ->comment('المبلغ المدفوع');
            
            // الحالات
            $table->enum('status', [
                'draft',        // مسودة
                'confirmed',    // مؤكدة
                'cancelled'     // ملغاة
            ])->default('draft')->comment('حالة الفاتورة');
            
            $table->enum('payment_status', [
                'unpaid',       // غير مدفوعة
                'partial',      // مدفوعة جزئياً
                'paid'          // مدفوعة بالكامل
            ])->default('unpaid')->comment('حالة الدفع');
            
            // ملاحظات وشروط
            $table->text('notes')
                  ->nullable()
                  ->comment('ملاحظات');
                  
            $table->text('terms_conditions')
                  ->nullable()
                  ->comment('الشروط والأحكام');
            
            // معلومات التأكيد
            $table->foreignId('confirmed_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('المستخدم الذي أكد الفاتورة');
                  
            $table->timestamp('confirmed_at')
                  ->nullable()
                  ->comment('تاريخ التأكيد');
            
            // معلومات الإلغاء
            $table->foreignId('cancelled_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('المستخدم الذي ألغى الفاتورة');
                  
            $table->timestamp('cancelled_at')
                  ->nullable()
                  ->comment('تاريخ الإلغاء');
                  
            $table->text('cancellation_reason')
                  ->nullable()
                  ->comment('سبب الإلغاء');
            
            // معلومات الإنشاء والتعديل
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('المستخدم المنشئ');
                  
            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('آخر مستخدم عدّل');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes للأداء
            $table->index('invoice_number');
            $table->index(['customer_id', 'invoice_date']);
            $table->index(['warehouse_id', 'status']);
            $table->index(['status', 'payment_status']);
            $table->index('due_date');
            $table->index('confirmed_at');
            $table->index('total'); // للتقارير المالية
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
