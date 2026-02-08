<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique()->index();
            
            // المخازن
            $table->unsignedBigInteger('from_warehouse_id')->index();
            $table->unsignedBigInteger('to_warehouse_id')->index();
            
            // التواريخ
            $table->date('transfer_date')->index();
            $table->date('expected_date')->nullable();
            $table->date('received_date')->nullable();
            
            // الحالة - كل الحالات مدمجة
            $table->enum('status', [
                'draft', 
                'pending', 
                'in_transit', 
                'received', 
                'cancelled', 
                'reversed'
            ])->default('draft')->index();
            
            $table->timestamp('reversed_at')->nullable();
            $table->text('notes')->nullable();
            
            // المستخدمين
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Composite Indexes للـ queries المتكررة
            $table->index(['status', 'transfer_date']);
            $table->index(['from_warehouse_id', 'status']);
            $table->index(['to_warehouse_id', 'status']);
            $table->index('created_at');
            
            // Foreign Keys
            $table->foreign('from_warehouse_id')
                  ->references('id')->on('warehouses')
                  ->onDelete('restrict');
                  
            $table->foreign('to_warehouse_id')
                  ->references('id')->on('warehouses')
                  ->onDelete('restrict');
                  
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('updated_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('confirmed_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('received_by')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
        
        // Constraint: المخزن المصدر لازم يكون مختلف عن المخزن الهدف
        DB::statement('ALTER TABLE warehouse_transfers 
                      ADD CONSTRAINT check_different_warehouses 
                      CHECK (from_warehouse_id != to_warehouse_id)');
        
        // تحسين الـ table engine
        DB::statement('ALTER TABLE warehouse_transfers ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_transfers');
    }
};