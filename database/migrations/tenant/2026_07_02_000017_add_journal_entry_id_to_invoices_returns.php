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
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('payment_method');
            $table->unsignedBigInteger('cogs_entry_id')->nullable()->after('journal_entry_id');
            
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries');
            $table->foreign('cogs_entry_id')->references('id')->on('journal_entries');
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('payment_status');
            
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries');
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('total');
            
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries');
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('total');
            
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries');
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('journal_entry_id')->nullable()->after('notes');
            
            $table->foreign('journal_entry_id')->references('id')->on('journal_entries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropForeign(['cogs_entry_id']);
            $table->dropColumn(['journal_entry_id', 'cogs_entry_id']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::table('purchase_returns', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn('journal_entry_id');
        });
    }
};
