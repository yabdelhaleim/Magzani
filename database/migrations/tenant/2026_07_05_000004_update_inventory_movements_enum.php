<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM(
                'purchase', 'sale', 'return_in', 'return_out',
                'transfer_in', 'transfer_out', 'adjustment',
                'damage', 'expired', 'production', 'consumption',
                'material_in', 'material_out',
                'adjustment_in', 'adjustment_out'
            ) NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            DB::statement("ALTER TABLE inventory_movements MODIFY COLUMN movement_type ENUM(
                'purchase', 'sale', 'return_in', 'return_out',
                'transfer_in', 'transfer_out', 'adjustment',
                'damage', 'expired', 'production', 'consumption',
                'wood_stock_in', 'wood_stock_out',
                'adjustment_in', 'adjustment_out'
            ) NOT NULL");
        }
    }
};
