<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 200);
            $table->text('description');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly']);
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_post')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'next_run_date']);
        });

        Schema::create('recurring_journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recurring_journal_entry_id');
            $table->unsignedSmallInteger('line_number');
            $table->unsignedBigInteger('account_id');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('description', 500)->nullable();

            $table->foreign('recurring_journal_entry_id')
                ->references('id')->on('recurring_journal_entries')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_journal_entry_lines');
        Schema::dropIfExists('recurring_journal_entries');
    }
};
