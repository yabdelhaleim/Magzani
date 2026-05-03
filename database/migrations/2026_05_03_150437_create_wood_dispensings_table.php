<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wood_dispensings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wood_stock_id')->constrained('wood_stocks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('client_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('manufacturing_order_id')->nullable()->constrained('manufacturing_orders')->nullOnDelete();
            $table->decimal('volume_cm3_taken', 16, 4);
            $table->text('notes')->nullable();
            $table->date('dispensed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wood_dispensings');
    }
};
