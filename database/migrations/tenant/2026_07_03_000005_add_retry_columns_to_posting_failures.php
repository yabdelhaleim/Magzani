<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_posting_failures', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_posting_failures', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('resolved');
            }
            if (!Schema::hasColumn('accounting_posting_failures', 'resolved_by')) {
                $table->unsignedBigInteger('resolved_by')->nullable()->after('resolved_at');
            }
            if (!Schema::hasColumn('accounting_posting_failures', 'error_class')) {
                $table->string('error_class', 200)->nullable()->after('error_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_posting_failures', function (Blueprint $table) {
            $table->dropColumnIfExists('resolved_at');
            $table->dropColumnIfExists('resolved_by');
            $table->dropColumnIfExists('error_class');
        });
    }
};
