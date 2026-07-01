<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_shifts', function (Blueprint $table) {
            $table->decimal('net_sales', 15, 2)->default(0)->after('total_returns')
                ->comment('صافي المبيعات (المبيعات - المرتجعات)');
            $table->decimal('expected_cash', 15, 2)->default(0)->after('net_sales')
                ->comment('رصيد النقدية المتوقع (افتتاح + المبيعات النقدية - مرتجعات النقدية)');
            $table->decimal('actual_cash', 15, 2)->default(0)->after('expected_cash')
                ->comment('رصيد النقدية الفعلي المُدخل من الكاشير');
            $table->decimal('cash_difference', 15, 2)->default(0)->after('actual_cash')
                ->comment('فرق النقدية (الفعلي - المتوقع)');
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('cash')->after('source')
                ->comment('طريقة الدفع: cash, card, credit, multiple');
        });
    }

    public function down(): void
    {
        Schema::table('pos_shifts', function (Blueprint $table) {
            $table->dropColumn(['net_sales', 'expected_cash', 'actual_cash', 'cash_difference']);
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
