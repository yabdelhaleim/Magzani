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
        Schema::create('account_types', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary(); // 1-5
            $table->string('code', 20)->unique();
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->string('normal_balance', 10); // debit, credit
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_types');
    }
};
