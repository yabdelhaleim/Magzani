<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->id();
            
            // العلاقات
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            
            // الكميات
            $table->decimal('quantity', 10, 3)->default(0);
            $table->decimal('last_count_quantity', 15, 3)->nullable()->comment('الكمية في آخر جرد');
            $table->dateTime('last_count_date')->nullable()->index()->comment('تاريخ آخر جرد');
            $table->decimal('adjustment_total', 15, 3)->default(0)->comment('إجمالي التسويات من الجرد');
            
            $table->integer('min_stock')->default(10)->comment('الحد الأدنى للتنبيه');
            $table->decimal('average_cost', 15, 2)->default(0);
            $table->decimal('reserved_quantity', 10, 3)->default(0)->comment('الكمية المحجوزة');
            
            // الكمية المتاحة (محسوبة تلقائياً)
$table->decimal('available_quantity', 10, 3)
      ->storedAs('quantity - reserved_quantity')
      ->comment('الكمية المتاحة');
            
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['product_id', 'warehouse_id'], 'product_warehouse_unique');
            
            // Composite Indexes للـ queries المتكررة
            $table->index(['warehouse_id', 'product_id']);
            $table->index(['product_id', 'quantity']);
            $table->index(['warehouse_id', 'quantity']);
            $table->index('created_at');
             $table->index(['warehouse_id', 'product_id'], 'idx_warehouse_product');
            $table->index(['product_id', 'quantity'], 'idx_product_quantity');

            // Foreign Keys
            $table->foreign('product_id')
                  ->references('id')->on('products')
                  ->onDelete('cascade');
                  
            $table->foreign('warehouse_id')
                  ->references('id')->on('warehouses')
                  ->onDelete('cascade');
        });
        
        // تحسين الـ table engine
        DB::statement('ALTER TABLE product_warehouse ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_warehouse');
    }
};