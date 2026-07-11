<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking — the heart of the feature.
 *
 * `batch_genealogy` is the M2M bridge between `material_batches` (source)
 * and `finished_good_batches` (destination). One row per consumption event.
 *
 *   - `source_material_batch_id`:  raw batch consumed
 *   - `finished_good_batch_id`:    finished batch produced
 *   - `quantity_consumed`:         how many raw units went into the FG batch
 *                                  (decimal 15,4 — fractional quantities allowed,
 *                                  e.g. 22.5 wood units in a chair)
 *   - `source_unit_cost_snapshot`: unit cost snapshotted at consumption time,
 *                                  used to compute impact splits when a price
 *                                  adjustment arrives later.
 *
 * Unique key on (source_material_batch_id, finished_good_batch_id) prevents
 * accidental double-insertion if the code path runs twice.
 *
 * Why a separate table (rather than columns on material_dispensings):
 *  - Symmetric traversal: source→finished AND finished→source all from one place.
 *  - Decoupled lifecycle: dispensings can be hard-deleted; genealogy persists
 *    for traceability of already-sold FG units.
 */
return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('batch_genealogy')) {
            Schema::create('batch_genealogy', function (Blueprint $table) {
                $table->id();

                $table->foreignId('source_material_batch_id')
                    ->constrained('material_batches')
                    ->onDelete('cascade');

                $table->foreignId('finished_good_batch_id')
                    ->constrained('finished_good_batches')
                    ->onDelete('cascade');

                $table->decimal('quantity_consumed', 15, 4)
                    ->comment('Raw units consumed into this FG batch (fractional OK)');

                $table->decimal('source_unit_cost_snapshot', 15, 4)
                    ->comment('Unit cost captured at consumption for late-reprice math');

                $table->timestamp('consumed_at')
                    ->useCurrent()
                    ->comment('When the consumption row was created');

                $table->timestamps();

                // Prevent accidental duplicates per (source, finished) pair
                $table->unique(['source_material_batch_id', 'finished_good_batch_id'], 'bg_source_finished_unique');

                // Support the two report traversal directions efficiently
                $table->index('source_material_batch_id');
                $table->index('finished_good_batch_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_genealogy');
    }
};
