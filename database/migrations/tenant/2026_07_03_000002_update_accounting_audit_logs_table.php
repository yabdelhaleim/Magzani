<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تحديث جدول accounting_audit_logs لإضافة أعمدة مطلوبة لـ JournalEntryService
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('accounting_audit_logs', 'notes')) {
                $table->string('notes', 500)->nullable()->after('new_values');
            }

            if (!Schema::hasColumn('accounting_audit_logs', 'performed_by')) {
                $table->unsignedBigInteger('performed_by')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('accounting_audit_logs', 'performed_at')) {
                $table->timestamp('performed_at')->nullable()->after('performed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_audit_logs', function (Blueprint $table) {
            $table->dropColumnIfExists('notes');
            $table->dropColumnIfExists('performed_by');
            $table->dropColumnIfExists('performed_at');
        });
    }
};
