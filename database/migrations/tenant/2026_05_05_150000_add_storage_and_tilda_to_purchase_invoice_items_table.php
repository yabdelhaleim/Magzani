<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->string('storage_type', 30)
                ->default('general')
                ->after('product_id')
                ->comment('general|manufactured|raw_material');
            $table->string('tilde_number', 120)->nullable()->after('storage_type');
            $table->json('tilde_details')->nullable()->after('tilde_number');
            $table->index('storage_type');
            $table->index('tilde_number');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropIndex(['storage_type']);
            $table->dropIndex(['tilde_number']);
            $table->dropColumn(['storage_type', 'tilde_number', 'tilde_details']);
        });
    }
};
