<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->decimal('balance', 15, 2)->default(0);

            // معلومات الاتصال
            $table->string('phone', 20)->nullable();
            $table->string('phone2', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('contact_person')->nullable();
            
            // العنوان
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('country', 100)->default('Egypt');
            
            // المالية
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            
            // الحالة
        $table->boolean('is_active')->default(true);
            
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('code');
            $table->index('name');
            $table->index('phone');
            $table->index('is_active');
            $table->index('current_balance');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};