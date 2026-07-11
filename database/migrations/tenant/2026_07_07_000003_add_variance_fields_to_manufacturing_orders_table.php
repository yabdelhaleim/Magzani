<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 2: Standard Costing & Cost Variance
 *
 * Adds snapshot + classification fields on each completed manufacturing order:
 *
 *  - `standard_cost_at_completion`: snapshot of BOM standard × qty, captured
 *     at the moment of completion (so historical MOs keep the standard cost
 *     that was valid at the time, even if later revisions change the BOM).
 *  - `actual_cost_at_completion`:  the actual full cost already on the row
 *     (cost_per_unit * quantity_produced) snapshotted for audit.
 *  - `total_variance` (actual - standard), `variance_type`
 *     ('favorable' | 'unfavorable' | 'none'), and the source split into
 *     `material_variance` and `labor_overhead_variance` (record-level
 *     classification only; not separate GL accounts).
 *  - `cost_locked_at`: prevents any retroactive tampering with the actual
 *     cost once variance has been calculated and posted.
 *  - `variance_journal_entry_id`: link back to the 5160 journal entry, used
 *     by Gap 5 reversal flows.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('manufacturing_orders')) {
            Schema::table('manufacturing_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('manufacturing_orders', 'standard_cost_at_completion')) {
                    $table->decimal('standard_cost_at_completion', 12, 4)->nullable()->after('total_cost');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'actual_cost_at_completion')) {
                    $table->decimal('actual_cost_at_completion', 12, 4)->nullable()->after('standard_cost_at_completion');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'total_variance')) {
                    $table->decimal('total_variance', 12, 4)->nullable()->after('actual_cost_at_completion');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'variance_type')) {
                    $table->string('variance_type', 16)->nullable()->after('total_variance');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'material_variance')) {
                    $table->decimal('material_variance', 12, 4)->nullable()->after('variance_type');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'labor_overhead_variance')) {
                    $table->decimal('labor_overhead_variance', 12, 4)->nullable()->after('material_variance');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'variance_posted_at')) {
                    $table->timestamp('variance_posted_at')->nullable()->after('labor_overhead_variance');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'variance_journal_entry_id')) {
                    $table->unsignedBigInteger('variance_journal_entry_id')->nullable()->after('variance_posted_at');
                }
                if (!Schema::hasColumn('manufacturing_orders', 'cost_locked_at')) {
                    $table->timestamp('cost_locked_at')->nullable()->after('variance_journal_entry_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('manufacturing_orders')) {
            Schema::table('manufacturing_orders', function (Blueprint $table) {
                $table->dropColumnIfExists('cost_locked_at');
                $table->dropColumnIfExists('variance_journal_entry_id');
                $table->dropColumnIfExists('variance_posted_at');
                $table->dropColumnIfExists('labor_overhead_variance');
                $table->dropColumnIfExists('material_variance');
                $table->dropColumnIfExists('variance_type');
                $table->dropColumnIfExists('total_variance');
                $table->dropColumnIfExists('actual_cost_at_completion');
                $table->dropColumnIfExists('standard_cost_at_completion');
            });
        }
    }
};
