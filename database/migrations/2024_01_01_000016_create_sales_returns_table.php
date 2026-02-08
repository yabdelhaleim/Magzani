<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number', 50)->unique();
            
            // العلاقات
            $table->foreignId('sales_invoice_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            
            // التواريخ
            $table->date('return_date');
            
            // المبالغ
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // الحالة
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            
            // سبب الإرجاع
            $table->text('return_reason')->nullable();
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('return_number');
            $table->index('sales_invoice_id');
            $table->index('customer_id');
            $table->index('return_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_returns');
    }
};