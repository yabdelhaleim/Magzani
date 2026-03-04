<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ✅ إضافة حقول الوزن ومعلومات الوحدة الأساسية لأصناف فاتورة المبيعات
     * 
     * - weight: الوزن (للمنتجات التي تُباع بالوزن)
     * - base_unit_type: نوع الوحدة الأساسية (weight, volume, count, length)
     * - base_unit_code: كود الوحدة الأساسية (kg, ton, piece, etc.)
     * - base_unit_label: اسم الوحدة الأساسية بالعربي
     */
    public function up(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            // ✅ الوزن (يُستخدم للمنتجات التي تُباع بالوزن)
            if (!Schema::hasColumn('sales_invoice_items', 'weight')) {
                $table->decimal('weight', 15, 3)->nullable()->after('base_quantity')
                      ->comment('الوزن بالوحدة الأساسية (كجم، طن، إلخ)');
            }

            // ✅ معلومات الوحدة الأساسية
            if (!Schema::hasColumn('sales_invoice_items', 'base_unit_type')) {
                $table->string('base_unit_type', 50)->nullable()->after('unit_code')
                      ->comment('نوع الوحدة: weight, volume, count, length');
            }

            if (!Schema::hasColumn('sales_invoice_items', 'base_unit_code')) {
                $table->string('base_unit_code', 50)->nullable()->after('base_unit_type')
                      ->comment('كود الوحدة الأساسية: kg, ton, piece, etc.');
            }

            if (!Schema::hasColumn('sales_invoice_items', 'base_unit_label')) {
                $table->string('base_unit_label', 100)->nullable()->after('base_unit_code')
                      ->comment('اسم الوحدة الأساسية بالعربي');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $columns = ['weight', 'base_unit_type', 'base_unit_code', 'base_unit_label'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('sales_invoice_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
