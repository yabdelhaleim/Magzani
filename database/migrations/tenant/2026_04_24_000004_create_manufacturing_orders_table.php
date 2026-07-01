<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manufacturing_orders', function (Blueprint $table) {
            $table->id();

            // Basic order information
            $table->string('order_number', 50)->unique()->comment('Unique order number like MO-2026-0001');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete()->comment('Link to product catalog');
            $table->string('product_name')->comment('Product name denormalized for history');

            // Production quantities and costs
            $table->decimal('quantity_produced', 10, 2)->comment('Quantity produced in this order');
            $table->decimal('cost_per_unit', 10, 4)->comment('Manufacturing cost per unit');
            $table->decimal('total_cost', 12, 4)->comment('Total manufacturing cost');
            $table->decimal('selling_price_per_unit', 10, 4)->comment('Suggested selling price per unit');

            // Order status and workflow
            $table->enum('status', ['draft', 'confirmed', 'completed', 'cancelled'])
                  ->default('draft')
                  ->comment('Order status workflow');
            $table->text('notes')->nullable()->comment('Additional notes');

            // Production tracking
            $table->dateTime('produced_at')->nullable()->comment('When production was completed');

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('status');
            $table->index('produced_at');
            $table->index(['product_id', 'status']);
            $table->index('created_at');
        });

        // Create the components table
        Schema::create('manufacturing_order_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('manufacturing_orders')->cascadeOnDelete()->comment('Parent manufacturing order');

            // Component details
            $table->string('component_name')->comment('Name of the component/material');
            $table->decimal('quantity', 10, 4)->comment('Quantity used');
            $table->string('unit', 50)->nullable()->comment('Unit of measurement (kg, m2, piece, etc.)');
            $table->decimal('unit_cost', 10, 4)->comment('Cost per unit of this component');
            $table->decimal('total_cost', 12, 4)->comment('Total cost for this component (quantity * unit_cost)');

            // Audit trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            // Indexes
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manufacturing_order_components');
        Schema::dropIfExists('manufacturing_orders');
    }
};