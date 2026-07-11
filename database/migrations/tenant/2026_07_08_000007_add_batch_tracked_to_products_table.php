<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gap 4 — Batch/Lot Tracking
 *
 * Adds `batch_tracked` flag to `products` so we know which products MUST
 * follow the genealogy workflow and which can stay on legacy behavior.
 *
 *   - true  → genealogy is required for every consumption & production
 *   - false → product skips batch tracking (default, preserves legacy
 *             behavior for everything produced before this gap)
 *
 * Distinct from `track_serial` (individual unit IDs) and `has_expiry`
 * (lot-level expiry date). Lives alongside them.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'batch_tracked')) {
                    $table->boolean('batch_tracked')
                        ->default(false)
                        ->after('is_manufactured')
                        ->comment('Gap 4: product must record batch genealogy on produce/consume');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumnIfExists('batch_tracked');
            });
        }
    }
};
