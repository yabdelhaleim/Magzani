<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('material_dispensings');
        Schema::dropIfExists('material_batches');

        Schema::create('material_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            $table->foreignId('uom_id')->constrained('units_of_measure');
            $table->decimal('quantity', 15, 4);
            $table->decimal('remaining_qty', 15, 4);
            $table->decimal('unit_cost', 15, 2);
            $table->string('purchase_reference')->nullable();
            $table->date('received_at');
            $table->timestamps();
        });

        Schema::create('material_dispensings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_batch_id')->constrained('material_batches')->onDelete('cascade');
            $table->unsignedBigInteger('manufacturing_order_id')->nullable();
            $table->decimal('quantity_taken', 15, 4);
            $table->date('dispensed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_dispensings');
        Schema::dropIfExists('material_batches');
    }
};
