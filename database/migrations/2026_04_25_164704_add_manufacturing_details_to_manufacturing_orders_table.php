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
             $table->decimal('waste_cost', 10, 4)->default(0)->after('total_cost');
             $table->decimal('labor_cost', 10, 4)->default(0)->after('waste_cost');
             $table->decimal('nails_cost', 10, 4)->default(0)->after('labor_cost');
             $table->decimal('tips_cost', 10, 4)->default(0)->after('nails_cost');
             $table->decimal('transport_cost', 10, 4)->default(0)->after('tips_cost');
             $table->decimal('fumigation_cost', 10, 4)->default(0)->after('transport_cost');
             $table->decimal('profit_margin', 5, 2)->default(0)->after('fumigation_cost');
             $table->decimal('profit_amount', 10, 4)->default(0)->after('profit_margin');
         });
     }

    /**
     * Reverse the migrations.
     */
     public function down(): void
     {
         Schema::table('manufacturing_orders', function (Blueprint $table) {
             $table->dropColumn([
                 'waste_cost', 'labor_cost', 'nails_cost', 'tips_cost',
                 'transport_cost', 'fumigation_cost', 'profit_margin', 'profit_amount'
             ]);
         });
     }
};
