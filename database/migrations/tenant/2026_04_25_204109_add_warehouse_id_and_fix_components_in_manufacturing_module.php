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
        if (!Schema::hasColumn('manufacturing_orders', 'warehouse_id')) {
            Schema::table('manufacturing_orders', function (Blueprint $table) {
                $table->foreignId('warehouse_id')->nullable()->after('status')->constrained()->nullOnDelete();
            });
        }

        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            if (Schema::hasColumn('manufacturing_order_components', 'component_type')) {
                $table->dropColumn('component_type');
            }
        });

        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            $table->enum('component_type', ['فرش', 'روابط', 'شاسية', 'دكم'])->nullable()->after('order_id');
            
            if (Schema::hasColumn('manufacturing_order_components', 'cubic_cm')) {
                $table->renameColumn('cubic_cm', 'volume_cm3');
            }
            if (Schema::hasColumn('manufacturing_order_components', 'price_per_meter')) {
                $table->renameColumn('price_per_meter', 'price_per_cubic_meter');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manufacturing_orders', function (Blueprint $table) {
            if (Schema::hasColumn('manufacturing_orders', 'warehouse_id')) {
                $table->dropConstrainedForeignId('warehouse_id');
            }
        });

        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            $table->dropColumn('component_type');
        });

        Schema::table('manufacturing_order_components', function (Blueprint $table) {
            $table->string('component_type', 50)->nullable()->after('order_id');
            if (Schema::hasColumn('manufacturing_order_components', 'volume_cm3')) {
                $table->renameColumn('volume_cm3', 'cubic_cm');
            }
            if (Schema::hasColumn('manufacturing_order_components', 'price_per_cubic_meter')) {
                $table->renameColumn('price_per_cubic_meter', 'price_per_meter');
            }
        });
    }
};
