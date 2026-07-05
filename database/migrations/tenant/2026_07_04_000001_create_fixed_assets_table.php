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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 15, 2);
            $table->decimal('scrap_value', 15, 2)->default(0.00);
            $table->integer('useful_life'); // in years
            $table->string('depreciation_method', 50)->default('straight_line');
            
            $table->foreignId('asset_account_id')
                  ->constrained('accounts')
                  ->onDelete('restrict');
                  
            $table->foreignId('accumulated_depreciation_account_id')
                  ->constrained('accounts')
                  ->onDelete('restrict');
                  
            $table->foreignId('depreciation_expense_account_id')
                  ->constrained('accounts')
                  ->onDelete('restrict');
                  
            $table->enum('status', ['active', 'disposed', 'fully_depreciated'])
                  ->default('active');
                  
            $table->date('disposed_at')->nullable();
            $table->decimal('disposal_value', 15, 2)->nullable();
            $table->decimal('disposal_gain_loss', 15, 2)->nullable();
            
            $table->foreignId('disposal_entry_id')
                  ->nullable()
                  ->constrained('journal_entries')
                  ->onDelete('set null');

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->foreignId('updated_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');
                  
            $table->timestamps();
            
            $table->index('status');
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
