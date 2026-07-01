<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter ENUM type of movement_type in inventory_movements table to add wood_stock_in and wood_stock_out
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM(
            'purchase',
            'sale',
            'return_in',
            'return_out',
            'transfer_in',
            'transfer_out',
            'adjustment',
            'damage',
            'expired',
            'return_from_transfer',
            'transfer_reversed',
            'production',
            'consumption',
            'wood_stock_in',
            'wood_stock_out'
        ) NOT NULL COMMENT 'نوع الحركة'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM (ignoring rows that may use wood_stock_in/out)
        DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM(
            'purchase',
            'sale',
            'return_in',
            'return_out',
            'transfer_in',
            'transfer_out',
            'adjustment',
            'damage',
            'expired',
            'return_from_transfer',
            'transfer_reversed',
            'production',
            'consumption'
        ) NOT NULL COMMENT 'نوع الحركة'");
    }
};
