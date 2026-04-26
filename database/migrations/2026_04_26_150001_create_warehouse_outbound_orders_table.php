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
        Schema::create('warehouse_outbound_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->date('order_date');
            $table->string('reference_number')->nullable();
            $table->enum('purpose', ['sale', 'transfer', 'return', 'damage', 'sample', 'other'])->default('sale');
            $table->string('recipient_name')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'status']);
            $table->index('order_date');
            $table->index('purpose');
        });

        Schema::create('warehouse_outbound_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outbound_order_id')->constrained('warehouse_outbound_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('requested_quantity', 15, 3);
            $table->decimal('approved_quantity', 15, 3)->nullable();
            $table->string('unit');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['outbound_order_id', 'product_id'], 'wb_order_product_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_outbound_order_items');
        Schema::dropIfExists('warehouse_outbound_orders');
    }
};
