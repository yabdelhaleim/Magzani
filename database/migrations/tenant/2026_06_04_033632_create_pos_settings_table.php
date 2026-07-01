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
        Schema::create('pos_settings', function (Blueprint $table) {
            $table->id();
            $table->string('pos_name', 100)->default('الكاشير الرئيسي');
            $table->unsignedBigInteger('default_warehouse_id')->nullable();
            $table->string('default_payment_method', 20)->default('cash');
            $table->boolean('require_shift')->default(true);
            $table->boolean('auto_print_receipt')->default(false);
            $table->boolean('allow_negative_stock')->default(false);
            $table->string('receipt_header_text', 255)->nullable();
            $table->string('receipt_footer_text', 255)->nullable();
            $table->timestamps();

            $table->foreign('default_warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_settings');
    }
};
