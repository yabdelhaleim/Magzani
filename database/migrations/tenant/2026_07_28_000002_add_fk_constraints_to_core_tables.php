<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // منع حذف عميل له فواتير مبيعات
        Schema::table('sales_invoices', function (Blueprint $table) {
            if (!$this->fkExists('sales_invoices', 'fk_si_customer_restrict')) {
                $table->foreign('customer_id', 'fk_si_customer_restrict')
                      ->references('id')->on('customers')
                      ->onDelete('restrict');
            }
        });

        // منع حذف مورد له فواتير مشتريات
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!$this->fkExists('purchase_invoices', 'fk_pi_supplier_restrict')) {
                $table->foreign('supplier_id', 'fk_pi_supplier_restrict')
                      ->references('id')->on('suppliers')
                      ->onDelete('restrict');
            }
        });

        // منع حذف منتج له حركات مخزون
        Schema::table('inventory_movements', function (Blueprint $table) {
            if (!$this->fkExists('inventory_movements', 'fk_im_product_restrict')) {
                $table->foreign('product_id', 'fk_im_product_restrict')
                      ->references('id')->on('products')
                      ->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign('fk_si_customer_restrict');
        });
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign('fk_pi_supplier_restrict');
        });
        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropForeign('fk_im_product_restrict');
        });
    }

    private function fkExists(string $table, string $fkName): bool
    {
        try {
            $constraints = \DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.REFERENTIAL_CONSTRAINTS 
                WHERE CONSTRAINT_SCHEMA = SCHEMA() 
                AND TABLE_NAME = '{$table}' 
                AND CONSTRAINT_NAME = '{$fkName}'
            ");
            return count($constraints) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
