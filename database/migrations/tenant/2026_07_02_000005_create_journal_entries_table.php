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
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 30)->unique();
            $table->date('entry_date');
            $table->unsignedBigInteger('fiscal_period_id');
            $table->text('description');
            $table->string('reference', 100)->nullable();
            $table->string('status', 20)->default('draft'); // draft, posted, reversed
            $table->string('source_type', 50); // manual, sales_invoice, etc.
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('source_event_key', 100)->nullable()->unique(); // For idempotency
            $table->decimal('total_debit', 15, 2)->default(0.00);
            $table->decimal('total_credit', 15, 2)->default(0.00);
            $table->char('currency_code', 3)->default('EGP');
            $table->unsignedBigInteger('reversed_entry_id')->nullable();
            $table->unsignedBigInteger('reversal_of_id')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('fiscal_period_id')->references('id')->on('fiscal_periods');
            $table->foreign('reversal_of_id')->references('id')->on('journal_entries');
            $table->index('entry_date');
            $table->index('status');
            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
