<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'payment_terms')) {
                $table->string('payment_terms', 30)->default('due_on_receipt')->after('credit_limit');
            }
        });

        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'payment_terms')) {
                $table->string('payment_terms', 30)->default('net30')->after('current_balance');
            }
        });

        Schema::table('pos_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_shifts', 'journal_entry_id')) {
                $table->unsignedBigInteger('journal_entry_id')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumnIfExists('payment_terms');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumnIfExists('payment_terms');
        });
        Schema::table('pos_shifts', function (Blueprint $table) {
            $table->dropColumnIfExists('journal_entry_id');
        });
    }
};
