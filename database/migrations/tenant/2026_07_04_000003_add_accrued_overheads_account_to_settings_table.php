<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('accrued_overheads_account_id')
                ->nullable()
                ->after('rounding_account_id')
                ->comment('حساب مصاريف التشغيل المستحقة للأجور والنقل');
        });
    }

    public function down(): void
    {
        Schema::table('accounting_settings', function (Blueprint $table) {
            $table->dropColumn('accrued_overheads_account_id');
        });
    }
};
