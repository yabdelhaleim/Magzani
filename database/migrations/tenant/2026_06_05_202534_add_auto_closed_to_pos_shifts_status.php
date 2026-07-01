<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds 'auto_closed' to the pos_shifts.status enum
     * and adds a cash_difference computed alias column for clarity.
     */
    public function up(): void
    {
        // MySQL: modify the enum to include 'auto_closed'
        DB::statement("
            ALTER TABLE pos_shifts
            MODIFY COLUMN status ENUM('open', 'closed', 'auto_closed')
            NOT NULL DEFAULT 'open'
            COMMENT 'حالة الوردية: open=مفتوحة, closed=مغلقة, auto_closed=أُغلقت تلقائياً'
        ");
    }

    public function down(): void
    {
        // Revert any auto_closed back to closed before modifying enum
        DB::statement("UPDATE pos_shifts SET status = 'closed' WHERE status = 'auto_closed'");

        DB::statement("
            ALTER TABLE pos_shifts
            MODIFY COLUMN status ENUM('open', 'closed')
            NOT NULL DEFAULT 'open'
        ");
    }
};
