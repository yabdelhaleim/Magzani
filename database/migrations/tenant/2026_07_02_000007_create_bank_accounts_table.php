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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id'); // Link to GL account (e.g., 1121)
            $table->string('bank_name', 200);
            $table->string('account_number', 50);
            $table->string('iban', 50)->nullable();
            $table->char('currency_code', 3)->default('EGP');
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
