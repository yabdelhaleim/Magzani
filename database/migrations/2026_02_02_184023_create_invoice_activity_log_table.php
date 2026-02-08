<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('invoice_type', 20); // 'sales' or 'purchase'
            $table->string('action', 50); // 'created', 'updated', 'cancelled', 'payment_added', etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // بيانات إضافية
            $table->timestamp('created_at');
            
            // Indexes
            $table->index(['invoice_id', 'invoice_type'], 'idx_invoice');
            $table->index('action', 'idx_action');
            $table->index('created_at', 'idx_created_at');
            $table->index('user_id', 'idx_user_id');
            
            // Foreign Key (optional - حسب تصميم قاعدة البيانات)
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_activity_log');
    }
};