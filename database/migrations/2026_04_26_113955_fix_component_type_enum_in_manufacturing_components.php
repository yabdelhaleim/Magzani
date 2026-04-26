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
        // MySQL Enum modification is best done with DB::statement to avoid issues with doctrine/dbal
        DB::statement("ALTER TABLE manufacturing_order_components MODIFY COLUMN component_type ENUM('فرش', 'روباط', 'شاسية', 'دكم')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE manufacturing_order_components MODIFY COLUMN component_type ENUM('فرش', 'روابط', 'شاسية', 'دكم')");
    }
};
