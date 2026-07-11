<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking
 *
 * Audit + posting record for one price-adjustment event caused by a
 * supplier's late invoice. ONE row per affected `purchase_invoice_item`,
 * even if the user adjusts several items at once in the UI.
 *
 *   - `purchase_invoice_item_id` the line that triggered the adjustment
 *   - `material_batch_id`        the batch whose cost is being corrected
 *   - `original_unit_cost`       snapshot before
 *   - `new_unit_cost`            snapshot after
 *   - `price_diff`               (new - original), the per-unit delta
 *   - `total_quantity_affected`  decimal(15,4) sum across inventory + cogs splits
 *   - `inventory_impact`         decimal(15,4) DR-or-CR portion that touches 1310
 *   - `cogs_impact`              decimal(15,4) DR-or-CR portion that touches 5100
 *   - `fallback_used`            boolean — true ⇒ entire diff posted to 5160
 *   - `fallback_reason`          nullable diagnostic text
 *   - `journal_entry_id`         nullable FK to the resulting JE
 *   - `applied_by`               FK users
 *
 * Decimal precision: 15,4 across the board (matches all other tables
 * in this gap to keep the * 20 / ÷ 100 split math reconciling to zero).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('batch_price_adjustments')) {
            Schema::create('batch_price_adjustments', function (Blueprint $table) {
                $table->id();

                $table->foreignId('purchase_invoice_item_id')
                    ->constrained('purchase_invoice_items')
                    ->onDelete('restrict');

                $table->foreignId('material_batch_id')
                    ->constrained('material_batches')
                    ->onDelete('restrict');

                $table->decimal('original_unit_cost', 15, 4);
                $table->decimal('new_unit_cost', 15, 4);
                $table->decimal('price_diff', 15, 4)
                    ->comment('Signed: positive = supplier raised, negative = supplier refunded');

                $table->decimal('total_quantity_affected', 15, 4);
                $table->decimal('inventory_impact', 15, 4)
                    ->comment('Signed — touches 1310 (raw + FG stock)');
                $table->decimal('cogs_impact', 15, 4)
                    ->comment('Signed — touches 5100 (sold-thru portion)');

                $table->boolean('fallback_used')->default(false);
                $table->string('fallback_reason')->nullable();

                $table->foreignId('journal_entry_id')
                    ->nullable()
                    ->constrained('journal_entries')
                    ->onDelete('set null');

                $table->foreignId('applied_by')
                    ->nullable()
                    ->constrained('users')
                    ->onDelete('set null');

                $table->timestamp('applied_at')->useCurrent();
                $table->timestamps();

                $table->index(['material_batch_id', 'applied_at']);
                $table->index('purchase_invoice_item_id');
                $table->index('fallback_used');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_price_adjustments');
    }
};
