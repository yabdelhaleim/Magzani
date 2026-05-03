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
        Schema::table('wood_dispensings', function (Blueprint $table) {
            $table->foreignId('sales_invoice_id')
                  ->nullable()
                  ->after('client_id')
                  ->constrained('sales_invoices')
                  ->nullOnDelete();

            $table->index('sales_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wood_dispensings', function (Blueprint $table) {
            $table->dropForeign(['sales_invoice_id']);
            $table->dropIndex(['sales_invoice_id']);
            $table->dropColumn('sales_invoice_id');
        });
    }
};
