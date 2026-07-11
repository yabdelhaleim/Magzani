<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add uom_id to products
        if (!Schema::hasColumn('products', 'uom_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('uom_id')->nullable()->constrained('units_of_measure')->onDelete('set null');
            });
        }

        // 2. Add foreign keys to manufacturing_order_extra_costs
        try {
            Schema::table('manufacturing_order_extra_costs', function (Blueprint $table) {
                $table->foreign('manufacturing_order_id', 'fk_mo_extra_costs_order_id')
                      ->references('id')->on('manufacturing_orders')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Ignore if already added
        }

        // 3. Drop legacy columns from manufacturing_orders
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['nails_cost', 'tips_cost', 'fumigation_cost', 'waste_cost', 'transport_cost'] as $col) {
                if (Schema::hasColumn('manufacturing_orders', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 4. Drop legacy columns from manufacturing_costs (BOM)
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach ([
                'price_per_cubic_meter', 'total_volume_cm3', 'total_volume_m3',
                'labor_cost', 'nails_hardware_cost', 'transportation_cost',
                'tips_misc_cost', 'fumigation_cost', 'additional_costs_total'
            ] as $col) {
                if (Schema::hasColumn('manufacturing_costs', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 5. Update bom_components
        Schema::table('bom_components', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['length_cm', 'width_cm', 'thickness_cm', 'volume_cm3'] as $col) {
                if (Schema::hasColumn('bom_components', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (!Schema::hasColumn('bom_components', 'uom_id')) {
                $table->foreignId('uom_id')->nullable()->constrained('units_of_measure')->onDelete('set null');
            }
            if (!Schema::hasColumn('bom_components', 'cost_per_uom')) {
                $table->decimal('cost_per_uom', 15, 2)->default(0.00);
            }
            if (!Schema::hasColumn('bom_components', 'component_category_id')) {
                $table->foreignId('component_category_id')->nullable()->constrained('component_categories')->onDelete('set null');
            }
            if (!Schema::hasColumn('bom_components', 'component_product_id')) {
                $table->unsignedBigInteger('component_product_id')->nullable()->after('manufacturing_cost_id');
                $table->foreign('component_product_id')->references('id')->on('products')->onDelete('cascade');
            }
        });

        // 6. Update manufacturing_order_components
        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            // Drop foreign key if exists
            try {
                $table->dropForeign('manufacturing_order_components_wood_stock_id_foreign');
            } catch (\Exception $e) {
                // Ignore if already dropped
            }

            $columnsToDrop = [];
            foreach (['wood_stock_id', 'thickness_cm', 'width_cm', 'length_cm', 'volume_cm3', 'price_per_cubic_meter'] as $col) {
                if (Schema::hasColumn('manufacturing_order_components', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }

            if (!Schema::hasColumn('manufacturing_order_components', 'material_batch_id')) {
                $table->foreignId('material_batch_id')->nullable()->constrained('material_batches')->onDelete('set null');
            }
            if (!Schema::hasColumn('manufacturing_order_components', 'uom_id')) {
                $table->foreignId('uom_id')->nullable()->constrained('units_of_measure')->onDelete('set null');
            }
        });

        // 7. Update purchase_invoice_items (drop tilde columns)
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['tilde_number', 'tilde_details'] as $col) {
                if (Schema::hasColumn('purchase_invoice_items', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 8. Add foreign key to material_dispensings for manufacturing_orders
        try {
            Schema::table('material_dispensings', function (Blueprint $table) {
                $table->foreign('manufacturing_order_id')
                      ->references('id')->on('manufacturing_orders')->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Ignore if already added
        }

        // 9. Drop legacy tables
        Schema::dropIfExists('wood_dispensings');
        Schema::dropIfExists('wood_stocks');
    }

    public function down(): void
    {
    }
};
