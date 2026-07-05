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
        Schema::create('account_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->primary();
            $table->decimal('period_debit', 15, 2)->default(0.00);
            $table->decimal('period_credit', 15, 2)->default(0.00);
            $table->decimal('ytd_debit', 15, 2)->default(0.00);
            $table->decimal('ytd_credit', 15, 2)->default(0.00);
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->unsignedBigInteger('last_entry_id')->nullable();
            $table->date('last_entry_date')->nullable();
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_balances');
    }
};
