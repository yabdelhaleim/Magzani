<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('manufacturing_order_extra_costs');
        Schema::dropIfExists('component_categories');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('uom_conversions');
        Schema::dropIfExists('units_of_measure');

        Schema::create('units_of_measure', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['count', 'weight', 'volume', 'length', 'custom']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_uom_id')->constrained('units_of_measure')->onDelete('cascade');
            $table->foreignId('to_uom_id')->constrained('units_of_measure')->onDelete('cascade');
            $table->decimal('factor', 15, 6);
            $table->timestamps();
        });

        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->morphs('attributable');
            $table->string('attribute_key');
            $table->string('attribute_value');
            $table->string('value_type')->default('string');
            $table->timestamps();
        });

        Schema::create('component_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('manufacturing_order_extra_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('manufacturing_order_id');
            $table->string('cost_type');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            // We will add the foreign key constraint once manufacturing_orders table columns are dropped/migrated
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_extra_costs');
        Schema::dropIfExists('component_categories');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('uom_conversions');
        Schema::dropIfExists('units_of_measure');
    }
};
