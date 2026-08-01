<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // لتسريع تقارير حركات المخزون
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!$this->indexExists('inventory_movements', 'idx_im_product_warehouse')) {
                $table->index(['product_id', 'warehouse_id'], 'idx_im_product_warehouse');
            }
            if (!$this->indexExists('inventory_movements', 'idx_im_warehouse_type_date')) {
                $table->index(['warehouse_id', 'movement_type', 'created_at'], 'idx_im_warehouse_type_date');
            }
        });

        // لتسريع تقارير المبيعات
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            if (!$this->indexExists('sales_invoice_items', 'idx_sii_product')) {
                $table->index('product_id', 'idx_sii_product');
            }
            if (!$this->indexExists('sales_invoice_items', 'idx_sii_unit')) {
                $table->index('selling_unit_id', 'idx_sii_unit');
            }
        });

        // لتسريع تقارير المشتريات
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            if (!$this->indexExists('purchase_invoice_items', 'idx_pii_product')) {
                $table->index('product_id', 'idx_pii_product');
            }
        });

        // لتسريع استعلامات المحاسبة
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            if (!$this->indexExists('journal_entry_lines', 'idx_jel_entry')) {
                $table->index('journal_entry_id', 'idx_jel_entry');
            }
        });

        // لتسريع فلتر التاريخ في الفواتير
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!$this->indexExists('sales_invoices', 'idx_si_customer_date')) {
                $table->index(['customer_id', 'invoice_date'], 'idx_si_customer_date');
            }
            if (!$this->indexExists('sales_invoices', 'idx_si_warehouse_status_date')) {
                $table->index(['warehouse_id', 'status', 'invoice_date'], 'idx_si_warehouse_status_date');
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!$this->indexExists('purchase_invoices', 'idx_pi_supplier_date')) {
                $table->index(['supplier_id', 'invoice_date'], 'idx_pi_supplier_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('idx_im_product_warehouse');
            $table->dropIndex('idx_im_warehouse_type_date');
        });
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropIndex('idx_sii_product');
            $table->dropIndex('idx_sii_unit');
        });
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropIndex('idx_pii_product');
        });
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex('idx_jel_entry');
        });
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_si_customer_date');
            $table->dropIndex('idx_si_warehouse_status_date');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_pi_supplier_date');
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
