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
         Schema::table('manufacturing_order_components', function (Blueprint $table) {
             $table->string('component_type', 50)->nullable()->after('order_id');
             $table->decimal('thickness_cm', 10, 4)->nullable()->after('component_type');
             $table->decimal('width_cm', 10, 4)->nullable()->after('thickness_cm');
             $table->decimal('length_cm', 10, 4)->nullable()->after('width_cm');
             $table->decimal('cubic_cm', 15, 4)->nullable()->after('length_cm');
             $table->decimal('price_per_meter', 10, 4)->nullable()->after('cubic_cm');
         });
     }

    /**
     * Reverse the migrations.
     */
     public function down(): void
     {
         Schema::table('manufacturing_order_components', function (Blueprint $table) {
             $table->dropColumn([
                 'component_type', 'thickness_cm', 'width_cm', 'length_cm', 'cubic_cm', 'price_per_meter'
             ]);
         });
     }
};
