<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_outbound_order_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)->nullable()->after('unit');
            $table->decimal('total_cost', 15, 2)->nullable()->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_outbound_order_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'total_cost']);
        });
    }
};
