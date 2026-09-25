<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_material_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('raw_material_templates', 'quantity')) {
                $table->decimal('quantity', 10, 4)->default(0)->after('name');
            }
            if (!Schema::hasColumn('raw_material_templates', 'sale_price')) {
                $table->decimal('sale_price', 12, 4)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('raw_material_templates', 'buy_price')) {
                $table->decimal('buy_price', 12, 4)->default(0)->after('sale_price');
            }
        });

        if (Schema::hasTable('raw_material_template_items')) {
            Schema::dropIfExists('raw_material_template_items');
        }
    }

    public function down(): void
    {
        Schema::table('raw_material_templates', function (Blueprint $table) {
            $table->dropColumn(['quantity', 'sale_price', 'buy_price']);
        });
    }
};
