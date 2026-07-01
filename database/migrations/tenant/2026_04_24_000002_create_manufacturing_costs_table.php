<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_costs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');

            $table->decimal('price_per_cubic_meter', 15, 2);
            $table->decimal('total_volume_cm3', 15, 4)->default(0);
            $table->decimal('total_volume_m3', 15, 6)->default(0);
            $table->decimal('material_cost', 15, 2)->default(0);

            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->decimal('nails_hardware_cost', 15, 2)->default(0);
            $table->decimal('transportation_cost', 15, 2)->default(0);
            $table->decimal('tips_misc_cost', 15, 2)->default(0);
            $table->decimal('fumigation_cost', 15, 2)->default(0);
            $table->decimal('additional_costs_total', 15, 2)->default(0);

            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('profit_percentage', 5, 2)->default(0);
            $table->decimal('profit_amount', 15, 2)->default(0);
            $table->decimal('final_price', 15, 2)->default(0);

            $table->enum('status', ['draft', 'confirmed'])->default('draft');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_costs');
    }
};
