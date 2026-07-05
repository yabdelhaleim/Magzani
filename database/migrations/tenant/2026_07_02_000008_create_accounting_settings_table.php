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
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 200)->nullable();
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);
            $table->char('default_currency', 3)->default('EGP');
            $table->boolean('tax_enabled')->default(true);
            $table->decimal('default_tax_rate', 5, 2)->default(14.00);
            $table->unsignedBigInteger('tax_account_output_id')->nullable();
            $table->unsignedBigInteger('tax_account_input_id')->nullable();
            $table->unsignedBigInteger('cash_account_id')->nullable();
            $table->unsignedBigInteger('ar_account_id')->nullable();
            $table->unsignedBigInteger('ap_account_id')->nullable();
            $table->unsignedBigInteger('inventory_account_id')->nullable();
            $table->unsignedBigInteger('cogs_account_id')->nullable();
            $table->unsignedBigInteger('sales_revenue_account_id')->nullable();
            $table->unsignedBigInteger('retained_earnings_id')->nullable();
            $table->unsignedBigInteger('wip_account_id')->nullable();
            $table->unsignedBigInteger('income_summary_account_id')->nullable();
            $table->unsignedBigInteger('sales_discount_account_id')->nullable();
            $table->unsignedBigInteger('shipping_revenue_account_id')->nullable();
            $table->unsignedBigInteger('other_charges_account_id')->nullable();
            $table->unsignedBigInteger('rounding_account_id')->nullable();
            $table->unsignedBigInteger('advance_customer_account_id')->nullable();
            $table->unsignedBigInteger('advance_supplier_account_id')->nullable();
            $table->boolean('capitalize_freight')->default(true);
            $table->boolean('auto_post_invoices')->default(true);
            $table->boolean('auto_post_payments')->default(true);
            $table->boolean('auto_post_expenses')->default(true);
            $table->boolean('auto_post_manufacturing')->default(false);
            $table->string('numbering_prefix_je', 10)->default('JE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_settings');
    }
};
