<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('customer_id', 'idx_customer_id');
            $table->index('invoice_id', 'idx_invoice_id');
            $table->index('status', 'idx_status');
            $table->index('created_at', 'idx_created_at');
            
            // Foreign Keys
            $table->foreign('invoice_id')->references('id')->on('sales_invoices')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_refunds');
    }
};