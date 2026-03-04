<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ تصحيح الـ Foreign Key لـ selling_unit_id
     * تغيير من units إلى product_selling_units
     */
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            // حذف الـ Foreign Key القديم إن وجد
            try {
                $table->dropForeign(['selling_unit_id']);
            } catch (\Exception $e) {
                // الـ Foreign Key قد لا يكون موجود
            }
            
            // إنشاء الـ Foreign Key الصحيح
            $table->foreign('selling_unit_id')
                  ->nullable()
                  ->references('id')
                  ->on('product_selling_units')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            try {
                $table->dropForeign(['selling_unit_id']);
            } catch (\Exception $e) {
                //
            }
        });
    }
};
