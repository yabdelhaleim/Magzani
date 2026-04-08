<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['selling_unit_id']);
            $table->foreign('selling_unit_id')
                  ->references('id')->on('product_selling_units')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropForeign(['selling_unit_id']);
            $table->foreign('selling_unit_id')
                  ->references('id')->on('units')
                  ->onDelete('set null')->onUpdate('cascade');
        });
    }
};
