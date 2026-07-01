<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('invoice_type', 20); // 'sales' or 'purchase'
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('cash'); // cash, card, bank_transfer, cheque
            $table->date('payment_date');
            $table->string('reference', 100)->nullable(); // رقم مرجعي للدفعة
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Indexes (names unique per DB — required for SQLite)
            $table->index(['invoice_id', 'invoice_type'], 'idx_inv_payments_invoice_type');
            $table->index('payment_date', 'idx_inv_payments_payment_date');
            $table->index('payment_method', 'idx_inv_payments_payment_method');
            $table->index('created_at', 'idx_inv_payments_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
