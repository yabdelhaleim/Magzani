<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * فهارس أداء إضافية لتسريع التقارير المالية والاستعلامات الثقيلة
 */
return new class extends Migration
{
    public function up(): void
    {
        // journal_entries: تسريع البحث بالحالة+التاريخ (أكثر استعلام تكراراً)
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['status', 'entry_date'], 'je_status_date_idx');
            $table->index(['source_type', 'source_id', 'status'], 'je_source_status_idx');
        });

        // journal_entry_lines: تسريع تجميع الأرصدة حسب الحساب
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->index(['account_id', 'journal_entry_id'], 'jel_account_entry_idx');
        });

        // account_balances: تسريع البحث بالرصيد
        Schema::table('account_balances', function (Blueprint $table) {
            $table->index('balance', 'ab_balance_idx');
        });

        // accounts: تسريع البحث بالنوع والحالة
        Schema::table('accounts', function (Blueprint $table) {
            $table->index(['account_type_id', 'is_leaf', 'is_active'], 'acc_type_leaf_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex('je_status_date_idx');
            $table->dropIndex('je_source_status_idx');
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropIndex('jel_account_entry_idx');
        });

        Schema::table('account_balances', function (Blueprint $table) {
            $table->dropIndex('ab_balance_idx');
        });

        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex('acc_type_leaf_active_idx');
        });
    }
};
