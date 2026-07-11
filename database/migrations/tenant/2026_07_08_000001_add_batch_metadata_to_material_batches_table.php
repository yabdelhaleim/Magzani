<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking
 *
 * Adds formal batch identifiers to `material_batches` and snapshots the
 * cost at receipt so a retroactive price adjustment can always compare
 * "old vs new" without losing the original.
 *
 *   - `batch_code`:          human-friendly identifier, e.g. "B-2026-00001"
 *                             distinct from the legacy free-form `purchase_reference`.
 *   - `original_unit_cost`:  snapshot of unit_cost at the moment the batch
 *                             was received. NEVER overwritten — `unit_cost`
 *                             may move forward (e.g. late-invoice adjustments),
 *                             `original_unit_cost` is the audit baseline.
 *   - `original_unit_cost_locked_at`: when the snapshot was taken.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('material_batches')) {
            Schema::table('material_batches', function (Blueprint $table) {
                if (!Schema::hasColumn('material_batches', 'batch_code')) {
                    $table->string('batch_code', 40)
                        ->nullable()
                        ->unique()
                        ->after('id')
                        ->comment('Formal batch code (unique) — pattern B-YYYY-NNNNN');
                }

                if (!Schema::hasColumn('material_batches', 'original_unit_cost')) {
                    $table->decimal('original_unit_cost', 15, 4)
                        ->nullable()
                        ->after('unit_cost')
                        ->comment('Snapshot of unit_cost at receipt; never overwritten');
                }

                if (!Schema::hasColumn('material_batches', 'original_unit_cost_locked_at')) {
                    $table->timestamp('original_unit_cost_locked_at')
                        ->nullable()
                        ->after('original_unit_cost');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('material_batches')) {
            Schema::table('material_batches', function (Blueprint $table) {
                $table->dropUniqueIfExists(['batch_code']);
                $table->dropColumnIfExists('original_unit_cost_locked_at');
                $table->dropColumnIfExists('original_unit_cost');
                $table->dropColumnIfExists('batch_code');
            });
        }
    }
};
