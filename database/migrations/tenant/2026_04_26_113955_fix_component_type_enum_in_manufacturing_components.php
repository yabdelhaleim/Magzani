<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE manufacturing_order_components MODIFY COLUMN component_type ENUM('فرش', 'روباط', 'شاسية', 'دكم')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE manufacturing_order_components MODIFY COLUMN component_type ENUM('فرش', 'روابط', 'شاسية', 'دكم')");
    }
};
