<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * تحديث جدول accounting_posting_failures لإضافة أعمدة مطلوبة لـ PostingService
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_posting_failures', function (Blueprint $table) {
            // مفتاح الحدث الموحد (يُستخدَم في PostingService)
            if (!Schema::hasColumn('accounting_posting_failures', 'source_event_key')) {
                $table->string('source_event_key', 200)->nullable()->after('event_key');
            }

            // وصف مختصر للعملية التي فشلت
            if (!Schema::hasColumn('accounting_posting_failures', 'description')) {
                $table->string('description', 300)->nullable()->after('source_event_key');
            }

            // Stack trace مختصر للخطأ
            if (!Schema::hasColumn('accounting_posting_failures', 'error_trace')) {
                $table->text('error_trace')->nullable()->after('error_message');
            }

            // وقت الفشل الفعلي
            if (!Schema::hasColumn('accounting_posting_failures', 'failed_at')) {
                $table->timestamp('failed_at')->nullable()->after('attempts');
            }

            // هل تم حل المشكلة؟
            if (!Schema::hasColumn('accounting_posting_failures', 'resolved')) {
                $table->boolean('resolved')->default(false)->after('failed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounting_posting_failures', function (Blueprint $table) {
            $table->dropColumnIfExists('source_event_key');
            $table->dropColumnIfExists('description');
            $table->dropColumnIfExists('error_trace');
            $table->dropColumnIfExists('failed_at');
            $table->dropColumnIfExists('resolved');
        });
    }
};
