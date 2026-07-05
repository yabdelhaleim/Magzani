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
        Schema::create('accounting_posting_failures', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 50);
            $table->unsignedBigInteger('source_id');
            $table->string('event_key', 100);
            $table->text('error_message');
            $table->string('error_class', 200);
            $table->unsignedTinyInteger('attempts')->default(1);
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamps();

            $table->index(['resolved_at', 'created_at']);
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_posting_failures');
    }
};
