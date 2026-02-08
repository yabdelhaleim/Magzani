<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index(); // محدد الطول + index
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active')->index();
            $table->string('city', 50)->nullable()->index();
            $table->string('area', 50)->nullable();
            $table->string('code', 50)->unique();
            $table->string('location', 255)->nullable(); // قللت الحجم
            $table->string('address', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('manager_name', 100)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable()->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Composite indexes للـ queries المتكررة
            $table->index(['is_active', 'status']);
            $table->index(['city', 'is_active']);
            $table->index('created_at');
            
            // Foreign keys في النهاية
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
        
        // تحسين الـ table engine
        DB::statement('ALTER TABLE warehouses ENGINE = InnoDB ROW_FORMAT=DYNAMIC');
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
