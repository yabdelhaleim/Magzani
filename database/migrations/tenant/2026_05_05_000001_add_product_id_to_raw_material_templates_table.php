<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_material_templates', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->nullable()
                ->after('warehouse_id')
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('raw_material_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
        });
    }
};
