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
        Schema::table('wood_stocks', function (Blueprint $table) {
            $table->foreignId('warehouse_id')
                  ->nullable()
                  ->after('supplier_id')
                  ->constrained('warehouses')
                  ->nullOnDelete();

            $table->foreignId('product_id')
                  ->nullable()
                  ->after('warehouse_id')
                  ->constrained('products')
                  ->nullOnDelete();

            $table->index('warehouse_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wood_stocks', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['product_id']);
            $table->dropIndex(['warehouse_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn(['warehouse_id', 'product_id']);
        });
    }
};
