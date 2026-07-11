<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 2: Standard Costing & Cost Variance
 *
 * Adds standard cost fields to the `manufacturing_costs` table — which acts
 * as the BOM header in this system. Standard costs are stored per-unit and
 * split into material / labor / overhead to allow reporting-level
 * classification without inflating the GL chart of accounts.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            // Snapshot of the standard cost at the time the standard was last revised.
            // This is the baseline used when comparing actual costs at MO completion.
            $table->decimal('standard_material_cost', 12, 4)->default(0)->after('material_cost');
            $table->decimal('standard_labor_cost', 12, 4)->default(0)->after('standard_material_cost');
            $table->decimal('standard_overhead_cost', 12, 4)->default(0)->after('standard_labor_cost');
            $table->decimal('standard_cost', 12, 4)->default(0)->after('standard_overhead_cost');

            // Versioning — when this standard cost became effective.
            // MO completion snapshots the standard_cost_at_completion (see migration
            // on manufacturing_orders) using the value valid at that date.
            $table->date('standard_cost_effective_from')->nullable()->after('standard_cost');
            $table->date('standard_cost_effective_to')->nullable()->after('standard_cost_effective_from');

            // Audit
            $table->unsignedBigInteger('standard_cost_updated_by')->nullable()->after('standard_cost_effective_to');
            $table->timestamp('standard_cost_updated_at')->nullable()->after('standard_cost_updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('manufacturing_costs', function (Blueprint $table) {
            $table->dropColumn([
                'standard_material_cost',
                'standard_labor_cost',
                'standard_overhead_cost',
                'standard_cost',
                'standard_cost_effective_from',
                'standard_cost_effective_to',
                'standard_cost_updated_by',
                'standard_cost_updated_at',
            ]);
        });
    }
};
