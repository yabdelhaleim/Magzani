<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking
 *
 * Snapshots the source batch's unit_cost at the moment of dispensing so we
 * can later reconstruct historical consumption costs when a retroactive
 * price adjustment arrives. Without this, late-invoice handling has no
 * baseline to compare "what we charged the FG batch at" vs "what we should
 * have charged".
 *
 *   - `source_unit_cost`:  copy of material_batch.unit_cost at dispense time.
 *                          Precision decimal(15,4) — must match BatchImpactSplitService
 *                          math so fractional quantities (e.g. 22.5, 7.5) reconcile.
 *   - `dispensing_method`: string method FIFO/MANUAL/PROPORTIONAL — audit only.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('material_dispensings')) {
            Schema::table('material_dispensings', function (Blueprint $table) {
                if (!Schema::hasColumn('material_dispensings', 'source_unit_cost')) {
                    $table->decimal('source_unit_cost', 15, 4)
                        ->nullable()
                        ->after('quantity_taken')
                        ->comment('Snapshot of source batch unit_cost at dispense time');
                }

                if (!Schema::hasColumn('material_dispensings', 'dispensing_method')) {
                    $table->string('dispensing_method', 20)
                        ->default('proportional')
                        ->after('source_unit_cost')
                        ->comment('FIFO | MANUAL | PROPORTIONAL — audit only');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('material_dispensings')) {
            Schema::table('material_dispensings', function (Blueprint $table) {
                $table->dropColumnIfExists('dispensing_method');
                $table->dropColumnIfExists('source_unit_cost');
            });
        }
    }
};
