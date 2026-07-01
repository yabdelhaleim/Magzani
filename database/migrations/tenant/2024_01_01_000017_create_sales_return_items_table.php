<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained()->onDelete('cascade');
            $table->foreignId('sales_invoice_item_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            
            // الكميات
            $table->decimal('quantity_returned', 10, 3);
            $table->decimal('unit_price', 15, 2);
            
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            
            // حالة المنتج
            $table->enum('item_condition', ['good', 'damaged', 'defective'])->default('good');
            $table->string('return_reason', 500)->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('sales_return_id');
            $table->index('sales_invoice_item_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_return_items');
    }
};