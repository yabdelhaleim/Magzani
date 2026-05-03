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
        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            $table->foreignId('wood_stock_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('wood_stocks')
                  ->nullOnDelete();

            $table->index('wood_stock_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            $table->dropForeign(['wood_stock_id']);
            $table->dropIndex(['wood_stock_id']);
            $table->dropColumn('wood_stock_id');
        });
    }
};
