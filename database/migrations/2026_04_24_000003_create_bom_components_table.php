<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('manufacturing_cost_id')->constrained()->cascadeOnDelete();
            $table->string('component_name');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('length_cm', 10, 2);
            $table->decimal('width_cm', 10, 2);
            $table->decimal('thickness_cm', 10, 2);
            $table->decimal('volume_cm3', 15, 4)->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index('manufacturing_cost_id');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_components');
    }
};
