<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_invoice_item_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            
            $table->decimal('quantity_returned', 10, 3);
            $table->decimal('unit_price', 15, 2);
            
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            $table->enum('item_condition', ['good', 'damaged', 'defective'])->default('good');
            $table->string('return_reason', 500)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('purchase_return_id');
            $table->index('purchase_invoice_item_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
    }
};