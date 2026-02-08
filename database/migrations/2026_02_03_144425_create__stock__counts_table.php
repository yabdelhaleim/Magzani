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
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            
            // معلومات الجرد الأساسية
            $table->string('count_number')->unique()->comment('رقم الجرد');
            $table->foreignId('warehouse_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('المخزن');
            $table->dateTime('count_date')->comment('تاريخ الجرد');
            
            // نوع الجرد
            $table->enum('count_type', ['full', 'partial', 'random'])
                  ->default('full')
                  ->comment('نوع الجرد: شامل، جزئي، عشوائي');
            
            // تواريخ هامة
            $table->dateTime('started_at')
                  ->nullable()
                  ->comment('تاريخ بدء الجرد الفعلي');
            
            // الحالة
            $table->enum('status', [
                'draft',        // مسودة
                'in_progress',  // جاري التنفيذ
                'completed',    // مكتمل
                'cancelled'     // ملغي
            ])->default('draft')->comment('حالة الجرد');
            
            // الإحصائيات
            $table->integer('total_items')->default(0)->comment('عدد الأصناف');
            $table->integer('items_counted')->default(0)->comment('الأصناف المجردة');
            $table->integer('discrepancies')->default(0)->comment('الفروقات');
            $table->integer('adjustments_applied')->default(0)->comment('عدد التعديلات المطبقة');
            $table->integer('adjustments_skipped')->default(0)->comment('عدد التعديلات المتجاهلة');
            
            // ملاحظات
            $table->text('notes')->nullable()->comment('ملاحظات');
            
            // المسؤولون
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('المستخدم المنشئ');
                  
            $table->foreignId('completed_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('المستخدم الذي أكمل الجرد');
                  
            $table->foreignId('cancelled_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('المستخدم الذي ألغى الجرد');
            
            $table->dateTime('completed_at')->nullable()->comment('تاريخ اكتمال الجرد');
            $table->dateTime('cancelled_at')->nullable()->comment('تاريخ إلغاء الجرد');
            
            $table->timestamps();
            
            // Indexes للأداء
            $table->index(['warehouse_id', 'status', 'count_date']);
            $table->index('count_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};