<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 2: Standard Costing & Cost Variance
 *
 * Adds the tenant-level toggle for standard costing. Default is FALSE so all
 * existing tenants keep their current Actual Costing behavior with zero
 * changes. When a tenant flips this on, MO completion starts posting the
 * Manufacturing Cost Variance (account 5160) where applicable.
 *
 * `variance_posting_account_id` lets an admin override the COA account used
 * for variance posting; defaults to 5160 when null.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('accounting_settings')) {
            Schema::table('accounting_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('accounting_settings', 'standard_costing_enabled')) {
                    $table->boolean('standard_costing_enabled')->default(false)->after('auto_post_manufacturing');
                }

                if (!Schema::hasColumn('accounting_settings', 'variance_posting_account_id')) {
                    $table->unsignedBigInteger('variance_posting_account_id')->nullable()->after('standard_costing_enabled');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accounting_settings')) {
            Schema::table('accounting_settings', function (Blueprint $table) {
                $table->dropColumnIfExists('variance_posting_account_id');
                $table->dropColumnIfExists('standard_costing_enabled');
            });
        }
    }
};
