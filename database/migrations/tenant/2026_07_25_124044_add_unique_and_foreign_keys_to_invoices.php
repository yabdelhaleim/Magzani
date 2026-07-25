<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Migration: إضافة Unique + Foreign Key Constraints.
 *
 * - Unique على invoice_number في sales_invoices و purchase_invoices
 *   (لمنع تكرار أرقام الفواتير)
 * - Foreign Keys مع ON DELETE RESTRICT على العلاقات الحرجة
 *   (لمنع حذف عميل/مورد/منتج/مخزن له فواتير)
 * - Indexes مركبة لتحسين أداء الاستعلامات
 *
 * ملاحظة: الـ migration آمن - يستخدم try/catch حول كل تغيير لأن بعض
 * الـ constraints قد تكون موجودة مسبقاً في إصدارات معينة.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ──────────────────────────────────────────────
        // 1) sales_invoices
        // ──────────────────────────────────────────────
        $this->safeAddUnique('sales_invoices', 'invoice_number');

        $this->safeAddIndex('sales_invoices', ['customer_id', 'invoice_date'], 'idx_sales_customer_date');
        $this->safeAddIndex('sales_invoices', ['warehouse_id', 'status'], 'idx_sales_warehouse_status');
        $this->safeAddIndex('sales_invoices', 'invoice_date', 'idx_sales_invoice_date');

        // FKs مع RESTRICT
        $this->safeAddForeignKey('sales_invoices', 'customer_id', 'customers', 'id', 'restrict');
        $this->safeAddForeignKey('sales_invoices', 'warehouse_id', 'warehouses', 'id', 'restrict');

        // ──────────────────────────────────────────────
        // 2) sales_invoice_items
        // ──────────────────────────────────────────────
        $this->safeAddIndex('sales_invoice_items', 'product_id', 'idx_sales_items_product');
        $this->safeAddIndex('sales_invoice_items', 'sales_invoice_id', 'idx_sales_items_invoice');

        $this->safeAddForeignKey('sales_invoice_items', 'product_id', 'products', 'id', 'restrict');
        $this->safeAddForeignKey('sales_invoice_items', 'sales_invoice_id', 'sales_invoices', 'id', 'cascade');

        // ──────────────────────────────────────────────
        // 3) purchase_invoices
        // ──────────────────────────────────────────────
        $this->safeAddUnique('purchase_invoices', 'invoice_number');

        $this->safeAddIndex('purchase_invoices', ['supplier_id', 'invoice_date'], 'idx_purchase_supplier_date');
        $this->safeAddIndex('purchase_invoices', ['warehouse_id', 'status'], 'idx_purchase_warehouse_status');
        $this->safeAddIndex('purchase_invoices', 'invoice_date', 'idx_purchase_invoice_date');

        $this->safeAddForeignKey('purchase_invoices', 'supplier_id', 'suppliers', 'id', 'restrict');
        $this->safeAddForeignKey('purchase_invoices', 'warehouse_id', 'warehouses', 'id', 'restrict');

        // ──────────────────────────────────────────────
        // 4) purchase_invoice_items (إن وُجد)
        // ──────────────────────────────────────────────
        if (Schema::hasTable('purchase_invoice_items')) {
            $this->safeAddIndex('purchase_invoice_items', 'product_id', 'idx_purchase_items_product');

            $this->safeAddForeignKey('purchase_invoice_items', 'product_id', 'products', 'id', 'restrict');
            $this->safeAddForeignKey('purchase_invoice_items', 'purchase_invoice_id', 'purchase_invoices', 'id', 'cascade');
        }

        // ──────────────────────────────────────────────
        // 5) expenses — حماية من حذف category له مصروفات
        // ──────────────────────────────────────────────
        if (Schema::hasColumn('expenses', 'expense_category_id')) {
            $this->safeAddForeignKey('expenses', 'expense_category_id', 'expense_categories', 'id', 'restrict');
            $this->safeAddIndex('expenses', 'expense_category_id', 'idx_expenses_category');
        }
    }

    public function down(): void
    {
        // الـ rollback: حذف الـ constraints بترتيب عكسي
        // (للأمان، الـ dropForeign يكتشف الاسم تلقائياً من النمط)

        Schema::table('expenses', function (Blueprint $table) {
            try { $table->dropForeign(['expense_category_id']); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_expenses_category'); } catch (\Exception $e) {}
        });

        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            try { $table->dropForeign(['product_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['purchase_invoice_id']); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_purchase_items_product'); } catch (\Exception $e) {}
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            try { $table->dropForeign(['supplier_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['warehouse_id']); } catch (\Exception $e) {}
            try { $table->dropUnique('sales_invoices_invoice_number_unique'); } catch (\Exception $e) {}
            try { $table->dropUnique('purchase_invoices_invoice_number_unique'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_purchase_supplier_date'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_purchase_warehouse_status'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_purchase_invoice_date'); } catch (\Exception $e) {}
        });

        Schema::table('sales_invoice_items', function (Blueprint $table) {
            try { $table->dropForeign(['product_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['sales_invoice_id']); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_sales_items_product'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_sales_items_invoice'); } catch (\Exception $e) {}
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            try { $table->dropForeign(['customer_id']); } catch (\Exception $e) {}
            try { $table->dropForeign(['warehouse_id']); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_sales_customer_date'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_sales_warehouse_status'); } catch (\Exception $e) {}
            try { $table->dropIndex('idx_sales_invoice_date'); } catch (\Exception $e) {}
        });
    }

    /**
     * إضافة Unique constraint بأمان (يتجاهل إذا موجود)
     */
    private function safeAddUnique(string $table, string $column): void
    {
        try {
            $exists = collect(DB::select(
                "SHOW INDEXES FROM `{$table}` WHERE Non_unique = 0 AND Column_name = ?",
                [$column]
            ));

            if ($exists->isNotEmpty()) {
                return; // موجود مسبقاً
            }

            Schema::table($table, function (Blueprint $t) use ($column) {
                $t->unique($column, "{$table}_{$column}_unique");
            });
        } catch (\Exception $e) {
            // تجاهل - قد يكون موجود أو مشكلة أخرى
        }
    }

    /**
     * إضافة Index بأمان (يتجاهل إذا موجود)
     */
    private function safeAddIndex(string $table, array|string $columns, string $indexName): void
    {
        try {
            $columnList = is_array($columns) ? implode(',', $columns) : $columns;

            $exists = collect(DB::select(
                "SHOW INDEXES FROM `{$table}` WHERE Key_name = ?",
                [$indexName]
            ));

            if ($exists->isNotEmpty()) {
                return;
            }

            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Exception $e) {
            // تجاهل
        }
    }

    /**
     * إضافة Foreign Key بأمان
     */
    private function safeAddForeignKey(
        string $table,
        string $column,
        string $refTable,
        string $refColumn,
        string $onDelete = 'restrict'
    ): void {
        try {
            // فحص إن كان الـ FK موجود
            $dbName = DB::connection()->getDatabaseName();
            $fks = collect(DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$dbName, $table, $column]
            ));

            if ($fks->isNotEmpty()) {
                return;
            }

            Schema::table($table, function (Blueprint $t) use ($column, $refTable, $refColumn, $onDelete) {
                $t->foreign($column)
                  ->references($refColumn)
                  ->on($refTable)
                  ->onDelete($onDelete);
            });
        } catch (\Exception $e) {
            // تجاهل
        }
    }
};