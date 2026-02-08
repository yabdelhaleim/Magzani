<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            
            $table->string('base_unit', 50)->index();
            $table->decimal('old_purchase_price', 15, 2)->default(0);
            $table->decimal('new_purchase_price', 15, 2)->default(0);
            $table->decimal('old_selling_price', 15, 2)->default(0);
            $table->decimal('new_selling_price', 15, 2)->default(0);
            
            $table->text('change_reason')->nullable()->comment('سبب التغيير');
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->timestamp('changed_at')->useCurrent()->index();
            
            // Composite indexes
            $table->index(['product_id', 'changed_at']);
            $table->index(['changed_by', 'changed_at']);
            
            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
        });
        
        DB::statement('ALTER TABLE product_price_history ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_history');
    }
};