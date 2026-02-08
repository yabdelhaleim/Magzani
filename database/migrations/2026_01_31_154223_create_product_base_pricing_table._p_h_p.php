<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_base_pricing', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            
            $table->string('base_unit', 50)->index()->comment('الوحدة الأساسية');
            $table->decimal('base_purchase_price', 15, 2)->default(0)->comment('سعر الشراء');
            $table->decimal('base_selling_price', 15, 2)->default(0)->comment('سعر البيع');
            
            $table->enum('profit_type', ['fixed', 'percentage'])->default('fixed')->comment('نوع هامش الربح');
            $table->decimal('profit_value', 15, 2)->default(0)->comment('قيمة هامش الربح');
            
            $table->boolean('is_active')->default(true)->index();
            $table->date('effective_from')->index()->comment('تاريخ سريان السعر');
            $table->date('effective_to')->nullable()->comment('تاريخ انتهاء السعر');
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Composite indexes
            $table->index(['product_id', 'is_active']);
            $table->index(['effective_from', 'effective_to']);
            $table->index(['product_id', 'effective_from']);
            
            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
        
        DB::statement('ALTER TABLE product_base_pricing ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_base_pricing');
    }
};