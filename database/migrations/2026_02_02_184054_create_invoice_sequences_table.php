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
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20); // 'sales' or 'purchase'
            $table->year('year');
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->unique(['type', 'year'], 'unique_type_year');
            $table->index(['type', 'year'], 'idx_type_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};