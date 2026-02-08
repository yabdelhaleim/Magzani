<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            
            $table->foreignId('warehouse_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            
            $table->date('adjustment_date');
            
            // الكميات
            $table->decimal('old_quantity', 10, 3);
            $table->decimal('adjustment_quantity', 10, 3); // موجب أو سالب
            $table->decimal('new_quantity', 10, 3);
            
            // السبب
            $table->enum('adjustment_type', ['increase', 'decrease']);
            $table->enum('reason', ['damaged', 'expired', 'lost', 'found', 'correction', 'other']);
            $table->text('description')->nullable();
            
            // الحالة
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('adjustment_number');
            $table->index('warehouse_id');
            $table->index('product_id');
            $table->index('adjustment_date');
            $table->index('adjustment_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};