<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ Performance Optimization: Add critical indexes
     */
    public function up(): void
    {
        // Index for inventory movements queries
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->index(['product_id', 'warehouse_id', 'movement_type'], 'idx_product_warehouse_type');
            $table->index(['movement_date', 'warehouse_id'], 'idx_date_warehouse');
        });

        // Index for sales invoices
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'idx_customer_date');
            $table->index(['status', 'created_at'], 'idx_status_date');
        });

        // Index for manufacturing orders
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_status_date');
        });

        // Index for products (common queries)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['category', 'is_active'], 'idx_category_active');
            $table->index(['product_type', 'is_active'], 'idx_type_active');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropIndex('idx_product_warehouse_type');
            $table->dropIndex('idx_date_warehouse');
        });

        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropIndex('idx_customer_date');
            $table->dropIndex('idx_status_date');
        });

        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $table->dropIndex('idx_status_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_category_active');
            $table->dropIndex('idx_type_active');
        });
    }
};
