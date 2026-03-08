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
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            // إضافة عمود الوزن للمنتجات التي تُشترى بالوزن
            $table->decimal('weight', 15, 3)->nullable()->after('base_quantity')->comment('الوزن بالوحدة الأساسية (كجم، طن، إلخ)');
            
            // إضافة حقول نوع الوحدة الأساسية
            $table->string('base_unit_type', 50)->nullable()->after('weight')->comment('نوع الوحدة: weight, volume, count, length');
            $table->string('base_unit_code', 50)->nullable()->after('base_unit_type')->comment('كود الوحدة الأساسية: kg, ton, piece, etc.');
            $table->string('base_unit_label', 100)->nullable()->after('base_unit_code')->comment('اسم الوحدة الأساسية بالعربي');
            
            // إضافة حقل سعر التكلفة
            $table->decimal('cost_price', 15, 2)->nullable()->after('unit_price')->comment('سعر التكلفة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_invoice_items', function (Blueprint $table) {
            $table->dropColumn([
                'weight',
                'base_unit_type',
                'base_unit_code',
                'base_unit_label',
                'cost_price',
            ]);
        });
    }
};
