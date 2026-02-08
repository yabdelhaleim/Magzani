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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique()->comment('رقم المعاملة الفريد');
            
            // النوع
            $table->enum('transaction_type', ['deposit', 'withdrawal'])->comment('نوع المعاملة: إيداع أو سحب');
            
            // المبلغ
            $table->decimal('amount', 15, 2)->unsigned()->comment('المبلغ');
            
            // التاريخ
            $table->date('transaction_date')->index()->comment('تاريخ المعاملة');
            
            // التصنيف
            $table->string('category', 100)->nullable()->index()->comment('تصنيف المعاملة');
            
            // الوصف والمرجع
            $table->text('description')->nullable()->comment('وصف المعاملة');
            $table->string('reference', 100)->nullable()->comment('المرجع');
            
            // معلومات الإنشاء
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null')->comment('المستخدم الذي أنشأ المعاملة');
            
            // التواريخ
            $table->timestamps();
            $table->softDeletes();
            
            // الفهارس
            $table->index(['transaction_type', 'transaction_date']);
            $table->index(['created_by', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};