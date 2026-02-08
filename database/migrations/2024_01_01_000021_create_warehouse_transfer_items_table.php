<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_transfer_id')->index();
            $table->unsignedBigInteger('product_id')->index();
            
            // نظام مفصل للكميات
            $table->decimal('quantity_sent', 10, 3);
            $table->decimal('quantity_received', 10, 3)->default(0);
            
            // حساب الفرق تلقائياً
            $table->decimal('quantity_difference', 10, 3)
                  ->storedAs('quantity_sent - quantity_received');
            
            $table->text('notes')->nullable();
            $table->text('discrepancy_reason')->nullable(); // سبب الفرق
            $table->timestamps();
            
            // Composite Indexes للـ queries المتكررة
            $table->index(['warehouse_transfer_id', 'product_id']);
            $table->index('created_at');
            $table->index(['warehouse_transfer_id', 'product_id'], 'idx_transfer_product');

            // Foreign Keys
            $table->foreign('warehouse_transfer_id')
                  ->references('id')->on('warehouse_transfers')
                  ->onDelete('cascade');
                  
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('restrict');
        });
        
        // تحسين الـ table engine
        DB::statement('ALTER TABLE warehouse_transfer_items ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfer_items');
    }
};