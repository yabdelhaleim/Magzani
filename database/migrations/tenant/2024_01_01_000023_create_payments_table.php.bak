<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique();
            
            // Polymorphic
            $table->string('payable_type', 100);
            $table->unsignedBigInteger('payable_id');
            
            // المبلغ
            $table->decimal('amount', 15, 2);
            
            // التاريخ
            $table->date('payment_date');
            
            // الطريقة
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'credit_card', 'other'])->default('cash');
            
            // تفاصيل إضافية
            $table->string('reference_number', 100)->nullable();
            $table->string('bank_name')->nullable();
            $table->date('cheque_date')->nullable();
            
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('payment_number');
            $table->index(['payable_type', 'payable_id']);
            $table->index('payment_date');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};