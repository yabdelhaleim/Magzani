<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // إضافة عمود is_current إلى الجداول الموجودة التي أُنشئت قبل إضافة العمود للـ Migration الأصلية
        if (Schema::hasTable('fiscal_years') && !Schema::hasColumn('fiscal_years', 'is_current')) {
            Schema::table('fiscal_years', function (Blueprint $table) {
                $table->boolean('is_current')->default(false)->after('is_closed');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fiscal_years') && Schema::hasColumn('fiscal_years', 'is_current')) {
            Schema::table('fiscal_years', function (Blueprint $table) {
                $table->dropColumn('is_current');
            });
        }
    }
};
