<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // منع تكرار أرقام فواتير المبيعات
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!$this->indexExists('sales_invoices', 'sales_invoices_invoice_number_unique')) {
                $table->unique('invoice_number', 'sales_invoices_invoice_number_unique');
            }
        });

        // منع تكرار أرقام فواتير المشتريات
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!$this->indexExists('purchase_invoices', 'purchase_invoices_invoice_number_unique')) {
                $table->unique('invoice_number', 'purchase_invoices_invoice_number_unique');
            }
        });

        // منع تكرار أرقام أوامر التصنيع
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            if (!$this->indexExists('manufacturing_orders', 'manufacturing_orders_order_number_unique')) {
                $table->unique('order_number', 'manufacturing_orders_order_number_unique');
            }
        });

        // منع تكرار أرقام التحويلات
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            if (!$this->indexExists('warehouse_transfers', 'warehouse_transfers_transfer_number_unique')) {
                $table->unique('transfer_number', 'warehouse_transfers_transfer_number_unique');
            }
        });

        // منع تكرار أرقام الجرد
        Schema::table('stock_counts', function (Blueprint $table) {
            if (!$this->indexExists('stock_counts', 'stock_counts_count_number_unique')) {
                $table->unique('count_number', 'stock_counts_count_number_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropUnique('sales_invoices_invoice_number_unique');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropUnique('purchase_invoices_invoice_number_unique');
        });
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->dropUnique('manufacturing_orders_order_number_unique');
        });
        Schema::table('warehouse_transfers', function (Blueprint $table) {
            $table->dropUnique('warehouse_transfers_transfer_number_unique');
        });
        Schema::table('stock_counts', function (Blueprint $table) {
            $table->dropUnique('stock_counts_count_number_unique');
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            return collect(\DB::select("SHOW INDEX FROM {$table}"))->pluck('Key_name')->contains($indexName);
        } catch (\Exception $e) {
            return false;
        }
    }
};
