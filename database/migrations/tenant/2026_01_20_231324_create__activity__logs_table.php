<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
    $table->id();
    $table->string('event_type');
    $table->json('event_data');
    $table->unsignedBigInteger('user_id')->nullable();
    $table->string('user_name')->nullable();
    $table->string('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamp('created_at');
    
    $table->index(['event_type', 'created_at']);
    $table->index('user_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_activity__logs');
    }
};
