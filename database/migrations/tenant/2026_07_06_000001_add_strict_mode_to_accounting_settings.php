<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('accounting_settings')) {
            Schema::table('accounting_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('accounting_settings', 'strict_posting_mode')) {
                    $table->boolean('strict_posting_mode')->default(false)->after('auto_post_manufacturing');
                }
                if (!Schema::hasColumn('accounting_settings', 'max_posting_failures')) {
                    $table->integer('max_posting_failures')->default(5)->after('strict_posting_mode');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('accounting_settings')) {
            Schema::table('accounting_settings', function (Blueprint $table) {
                $table->dropColumnIfExists('strict_posting_mode');
                $table->dropColumnIfExists('max_posting_failures');
            });
        }
    }
};
