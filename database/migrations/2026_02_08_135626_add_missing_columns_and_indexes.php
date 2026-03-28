<?php
// database/migrations/2026_02_08_135626_add_missing_columns_and_indexes.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ✅ فقط إذا كان الجدول موجود
        if (Schema::hasTable('product_base_pricing')) {
            if (!Schema::hasColumn('product_base_pricing', 'is_current')) {
                Schema::table('product_base_pricing', function (Blueprint $table) {
                    $table->boolean('is_current')->default(false)->after('is_active');
                    
                    // إضافة indexes لتحسين الأداء
                    $table->index(['product_id', 'is_current', 'is_active'], 'idx_product_current_active');
                    $table->index(['effective_from', 'effective_to'], 'idx_effective_dates');
                });
            }

            // ✅ تحديد السعر الحالي لكل منتج (البيانات الموجودة)
            DB::statement("
                UPDATE product_base_pricing pbp1
                INNER JOIN (
                    SELECT product_id, base_unit, MAX(id) as latest_id
                    FROM product_base_pricing
                    WHERE is_active = 1
                    AND effective_from <= NOW()
                    AND (effective_to IS NULL OR effective_to >= NOW())
                    GROUP BY product_id, base_unit
                ) pbp2 ON pbp1.id = pbp2.latest_id
                SET pbp1.is_current = 1
            ");
        }

        // ✅ فقط إذا كان الجدول موجود
        if (Schema::hasTable('product_selling_units')) {
            Schema::table('product_selling_units', function (Blueprint $table) {
                // تحقق من وجود الأعمدة المطلوبة أولاً
                if (Schema::hasColumn('product_selling_units', 'quantity_in_base_unit') && 
                    !Schema::hasColumn('product_selling_units', 'conversion_factor')) {
                    $table->decimal('conversion_factor', 10, 6)
                          ->storedAs('quantity_in_base_unit')
                          ->after('quantity_in_base_unit')
                          ->comment('Stored for faster queries');
                }
                
                // Indexes للبحث السريع (تحقق من عدم وجودها)
                $indexesFound = collect(DB::select("SHOW INDEX FROM product_selling_units"))->pluck('Key_name')->unique()->toArray();
                
                if (!isset($indexesFound['idx_product_active_default'])) {
                    if (Schema::hasColumn('product_selling_units', 'product_id') && 
                        Schema::hasColumn('product_selling_units', 'is_active') && 
                        Schema::hasColumn('product_selling_units', 'is_default')) {
                        $table->index(['product_id', 'is_active', 'is_default'], 'idx_product_active_default');
                    }
                }
                
                if (!isset($indexesFound['idx_barcode']) && Schema::hasColumn('product_selling_units', 'barcode')) {
                    $table->index(['barcode'], 'idx_barcode');
                }
                
                if (!isset($indexesFound['idx_unit_code']) && Schema::hasColumn('product_selling_units', 'unit_code')) {
                    $table->index(['unit_code'], 'idx_unit_code');
                }
                
                if (!isset($indexesFound['idx_display_order']) && Schema::hasColumn('product_selling_units', 'display_order')) {
                    $table->index(['display_order'], 'idx_display_order');
                }
            });
        }

        // ✅ فقط إذا كان الجدول موجود
        if (Schema::hasTable('product_price_history')) {
            Schema::table('product_price_history', function (Blueprint $table) {
                $indexesFound = collect(DB::select("SHOW INDEX FROM product_price_history"))->pluck('Key_name')->unique()->toArray();
                
                if (!isset($indexesFound['idx_product_changed']) && 
                    Schema::hasColumn('product_price_history', 'product_id') && 
                    Schema::hasColumn('product_price_history', 'changed_at')) {
                    $table->index(['product_id', 'changed_at'], 'idx_product_changed');
                }
                
                if (!isset($indexesFound['idx_user_changed']) && 
                    Schema::hasColumn('product_price_history', 'changed_by') && 
                    Schema::hasColumn('product_price_history', 'changed_at')) {
                    $table->index(['changed_by', 'changed_at'], 'idx_user_changed');
                }
                
                if (!isset($indexesFound['idx_changed_at']) && 
                    Schema::hasColumn('product_price_history', 'changed_at')) {
                    $table->index(['changed_at'], 'idx_changed_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_base_pricing')) {
            Schema::table('product_base_pricing', function (Blueprint $table) {
                if (Schema::hasColumn('product_base_pricing', 'is_current')) {
                    $table->dropIndex('idx_product_current_active');
                    $table->dropIndex('idx_effective_dates');
                    $table->dropColumn('is_current');
                }
            });
        }

        if (Schema::hasTable('product_selling_units')) {
            Schema::table('product_selling_units', function (Blueprint $table) {
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexesFound = $sm->listTableIndexes('product_selling_units');
                
                if (isset($indexesFound['idx_product_active_default'])) {
                    $table->dropIndex('idx_product_active_default');
                }
                if (isset($indexesFound['idx_barcode'])) {
                    $table->dropIndex('idx_barcode');
                }
                if (isset($indexesFound['idx_unit_code'])) {
                    $table->dropIndex('idx_unit_code');
                }
                if (isset($indexesFound['idx_display_order'])) {
                    $table->dropIndex('idx_display_order');
                }
                
                if (Schema::hasColumn('product_selling_units', 'conversion_factor')) {
                    $table->dropColumn('conversion_factor');
                }
            });
        }

        if (Schema::hasTable('product_price_history')) {
            Schema::table('product_price_history', function (Blueprint $table) {
                $indexesFound = collect(DB::select("SHOW INDEX FROM product_price_history"))->pluck('Key_name')->unique()->toArray();
                
                if (isset($indexesFound['idx_product_changed'])) {
                    $table->dropIndex('idx_product_changed');
                }
                if (isset($indexesFound['idx_user_changed'])) {
                    $table->dropIndex('idx_user_changed');
                }
                if (isset($indexesFound['idx_changed_at'])) {
                    $table->dropIndex('idx_changed_at');
                }
            });
        }
    }
};