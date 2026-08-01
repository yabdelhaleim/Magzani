<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('default_currency')->default('جنيه مصري (ج.م)');
            $table->string('date_format')->default('YYYY-MM-DD');
            $table->decimal('default_tax', 5, 2)->default(14);
            $table->integer('rows_per_page')->default(25);

            // Stock settings
            $table->boolean('low_stock_alert')->default(true);
            $table->boolean('allow_negative_stock')->default(true);
            $table->boolean('confirm_before_delete')->default(false);

            // Invoice settings
            $table->boolean('auto_invoice_number')->default(true);
            $table->boolean('auto_print_invoice')->default(true);
            $table->boolean('auto_email_invoice')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
