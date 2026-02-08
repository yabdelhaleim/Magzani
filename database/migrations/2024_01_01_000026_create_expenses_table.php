<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number', 50)->unique();
            
            // التصنيف
            $table->foreignId('expense_category_id')->constrained()->onDelete('restrict');
            
            // المبلغ
            $table->decimal('amount', 15, 2);
            
            // التاريخ
            $table->date('expense_date');
            
            // الوصف
            $table->text('description')->nullable();
            $table->string('reference')->nullable();
            
            // المرفقات
            $table->string('attachment', 500)->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('expense_number');
            $table->index('expense_category_id');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};