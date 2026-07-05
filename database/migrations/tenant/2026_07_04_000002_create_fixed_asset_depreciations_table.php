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
        Schema::create('fixed_asset_depreciations', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('fixed_asset_id')
                  ->constrained('fixed_assets')
                  ->onDelete('cascade');
                  
            $table->date('depreciation_date');
            $table->decimal('amount', 15, 2);
            
            $table->foreignId('journal_entry_id')
                  ->constrained('journal_entries')
                  ->onDelete('cascade');
                  
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['fixed_asset_id', 'depreciation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_asset_depreciations');
    }
};
