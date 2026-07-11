<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking
 *
 * The missing link the original schema lacks: a formal FK connection between
 * `material_batches` (which carry the unit cost) and `purchase_invoice_items`
 * (which carry the supplier-declared price).
 *
 * Without this, a supplier's late invoice price change has nowhere to attach.
 * Today the connection is a free-text `purchase_reference` on the batch,
 * which is unsearchable and unjoinable.
 *
 *   - `material_batch_id`        the raw batch this invoice line priced
 *   - `purchase_invoice_item_id` the priced invoice line
 *   - `quantity_originally_priced` decimal(15,4) — how many of the batch
 *                                 units were covered by THIS invoice line
 *                                 (one batch may be priced across multiple
 *                                 lines for partial deliveries).
 *
 * Unique key on (material_batch_id, purchase_invoice_item_id).
 *
 * NOTE: For batches created BEFORE this migration we keep their `null` links
 * and rely on the Fallback flow (Gap 4 Q4 — variance to 5160 of Gap 2).
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('material_batch_purchase_links')) {
            Schema::create('material_batch_purchase_links', function (Blueprint $table) {
                $table->id();

                $table->foreignId('material_batch_id')
                    ->constrained('material_batches')
                    ->onDelete('cascade');

                $table->foreignId('purchase_invoice_item_id')
                    ->constrained('purchase_invoice_items')
                    ->onDelete('cascade');

                $table->decimal('quantity_originally_priced', 15, 4)
                    ->default(0)
                    ->comment('Coverage: how many batch units this invoice line priced');

                $table->timestamp('linked_at')->useCurrent();

                $table->timestamps();

                $table->unique(['material_batch_id', 'purchase_invoice_item_id'], 'mbp_batch_item_unique');
                $table->index('purchase_invoice_item_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_batch_purchase_links');
    }
};
