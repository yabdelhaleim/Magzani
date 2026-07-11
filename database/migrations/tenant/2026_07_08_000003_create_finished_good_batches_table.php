<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking
 *
 * A "batch" for a manufactured (finished) product. Created the moment an MO
 * is completed. Symmetric to `material_batches`:
 *
 *   - `batch_code`             formal unique code, FGB-YYYY-NNNNN
 *   - `manufacturing_order_id` nullable — drops to nullable in case the MO
 *                              is later hard-deleted but the finished batch
 *                              must persist for traceability of already-sold units.
 *   - `standard_unit_cost`     nullable cross-ref to Gap 2 — set if the
 *                              BOM had a standard at completion time.
 *   - `remaining_qty`          mirrors the raw-material convention so
 *                              inventory queries can reuse one helper.
 *
 * Precision: decimal(15,4) for all quantities and costs to avoid rounding
 * drift across compound calculations (e.g. 22.5 × 20 must stay 450, not 449.99…).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('finished_good_batches')) {
            Schema::create('finished_good_batches', function (Blueprint $table) {
                $table->id();
                $table->string('batch_code', 40)->unique()->comment('Formal code FGB-YYYY-NNNNN');

                $table->foreignId('product_id')
                    ->constrained('products')
                    ->onDelete('restrict')
                    ->comment('The finished/semi-finished product produced');

                $table->foreignId('warehouse_id')
                    ->constrained('warehouses')
                    ->onDelete('restrict');

                $table->foreignId('manufacturing_order_id')
                    ->nullable()
                    ->constrained('manufacturing_orders')
                    ->onDelete('set null')
                    ->comment('Origin MO; nullable for orphan FG batches (rare)');

                $table->decimal('quantity', 15, 4)
                    ->comment('Total quantity produced in this batch');
                $table->decimal('remaining_qty', 15, 4)
                    ->comment('Quantity still in stock; decrement on sale/dispense');

                $table->decimal('unit_cost', 15, 4)
                    ->comment('Actual per-unit cost at production time');
                $table->decimal('standard_unit_cost', 15, 4)
                    ->nullable()
                    ->comment('Cross-ref to Gap 2: BOM standard at completion, if any');

                $table->date('produced_at');
                $table->timestamps();

                $table->index(['product_id', 'produced_at']);
                $table->index(['manufacturing_order_id']);
                $table->index(['product_id', 'remaining_qty']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_good_batches');
    }
};
