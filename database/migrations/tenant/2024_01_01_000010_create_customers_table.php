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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // المعلومات الأساسية
            $table->string('code', 50)
                  ->unique()
                  ->comment('كود العميل الفريد');
                  
            $table->string('name', 200)
                  ->comment('اسم العميل');
            
            // معلومات الاتصال
            $table->string('phone', 20)
                  ->nullable()
                  ->comment('رقم الهاتف الأساسي');
                  
            $table->string('phone2', 20)
                  ->nullable()
                  ->comment('رقم هاتف إضافي');
            $table->decimal('balance', 10, 2)->default(0)  ;
            $table->string('email', 150)
                  ->nullable()
                  ->comment('البريد الإلكتروني');
            
            // العنوان
            $table->text('address')
                  ->nullable()
                  ->comment('العنوان التفصيلي');
                  
            $table->string('city', 100)
                  ->nullable()
                  ->comment('المدينة');
                  
            $table->string('country', 100)
                  ->default('Egypt')
                  ->comment('الدولة');
            
            // الحسابات المالية
            $table->decimal('opening_balance', 15, 2)
                  ->default(0)
                  ->comment('الرصيد الافتتاحي');
                  
            $table->decimal('current_balance', 15, 2)
                  ->default(0)
                  ->comment('الرصيد الحالي (محسوب)');
            
            $table->decimal('credit_limit', 15, 2)
                  ->default(0)
                  ->comment('حد الائتمان المسموح');
            
            // التصنيف
            $table->enum('customer_type', [
                'retail',      // تجزئة
                'wholesale',   // جملة
                'vip'          // VIP
            ])->default('retail')->comment('نوع العميل');
            
            // الحالة
            $table->boolean('is_active')
                  ->default(true)
                  ->comment('نشط؟');
            
            // ملاحظات
            $table->text('notes')
                  ->nullable()
                  ->comment('ملاحظات');
            
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
            $table->index('code');
            $table->index('name');
            $table->index('phone');
            $table->index(['is_active', 'customer_type']);
            $table->index('current_balance');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};