<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->nullable()->after('cost_price');
            $table->decimal('discount', 15, 2)->nullable()->after('discount_amount');
            $table->decimal('tax', 15, 2)->nullable()->after('tax_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['price', 'discount', 'tax']);
        });
    }
};
