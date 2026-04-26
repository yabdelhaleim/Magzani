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
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->decimal('cost_per_unit', 15, 4)->change();
            $table->decimal('total_cost', 20, 4)->change();
            $table->decimal('selling_price_per_unit', 15, 4)->change();
            $table->decimal('waste_cost', 15, 4)->change();
            $table->decimal('labor_cost', 15, 4)->change();
            $table->decimal('nails_cost', 15, 4)->change();
            $table->decimal('tips_cost', 15, 4)->change();
            $table->decimal('transport_cost', 15, 4)->change();
            $table->decimal('fumigation_cost', 15, 4)->change();
            $table->decimal('profit_amount', 15, 4)->change();
        });
    }

    public function down(): void
    {
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->decimal('cost_per_unit', 10, 4)->change();
            $table->decimal('total_cost', 12, 4)->change();
            $table->decimal('selling_price_per_unit', 10, 4)->change();
            $table->decimal('waste_cost', 10, 4)->change();
            $table->decimal('labor_cost', 10, 4)->change();
            $table->decimal('nails_cost', 10, 4)->change();
            $table->decimal('tips_cost', 10, 4)->change();
            $table->decimal('transport_cost', 10, 4)->change();
            $table->decimal('fumigation_cost', 10, 4)->change();
            $table->decimal('profit_amount', 10, 4)->change();
        });
    }
};
