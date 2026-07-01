<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            
            // المستخدم
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // النشاط
            $table->string('log_name')->nullable();
            $table->text('description');
            
            // الكائن المتأثر
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            
            // الكائن المسبب
            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            
            // البيانات
            $table->json('properties')->nullable();
            
            // معلومات إضافية
            $table->string('event')->nullable();
            $table->string('batch_uuid')->nullable();
            
            $table->timestamp('created_at')->nullable();
            
            $table->index('user_id');
            $table->index(['subject_type', 'subject_id']);
            $table->index(['causer_type', 'causer_id']);
            $table->index('log_name');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};