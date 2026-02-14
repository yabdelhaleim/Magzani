<?php
// app/Observers/ProductBaseUnitObserver.php

namespace App\Observers;

use App\Models\ProductBaseUnit;
use App\Models\Product;
use App\Models\ProductSellingUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductBaseUnitObserver
{
    /**
     * 🔥 يتم تنفيذه بعد تحديث product_base_units
     */
    public function updated(ProductBaseUnit $baseUnit)
    {
        try {
            Log::info('🔄 Observer: بدء تحديث الأسعار التلقائي', [
                'base_unit_id' => $baseUnit->id,
                'product_id' => $baseUnit->product_id,
                'new_purchase' => $baseUnit->base_purchase_price,
                'new_selling' => $baseUnit->base_selling_price,
            ]);

            // ✅ 1. تحديث جدول products
            $productUpdated = DB::table('products')
                ->where('id', $baseUnit->product_id)
                ->update([
                    'purchase_price' => $baseUnit->base_purchase_price,
                    'selling_price' => $baseUnit->base_selling_price,
                    'updated_at' => now(),
                ]);

            Log::info('📦 تحديث جدول products', [
                'product_id' => $baseUnit->product_id,
                'rows_updated' => $productUpdated,
            ]);

            // ✅ 2. تحديث product_selling_units (لو مفعل التحديث التلقائي)
            if ($baseUnit->auto_update_selling_units) {
                
                // الوحدة الأساسية (conversion_factor = 1)
                $baseUnitUpdated = DB::table('product_selling_units')
                    ->where('product_id', $baseUnit->product_id)
                    ->where('base_unit_id', $baseUnit->id)
                    ->where('is_base', true)
                    ->update([
                        'unit_purchase_price' => $baseUnit->base_purchase_price,
                        'unit_selling_price' => $baseUnit->base_selling_price,
                        'updated_at' => now(),
                    ]);

                Log::info('📊 تحديث الوحدة الأساسية في selling_units', [
                    'base_unit_id' => $baseUnit->id,
                    'rows_updated' => $baseUnitUpdated,
                ]);

                // الوحدات الفرعية (conversion_factor != 1)
                $sellingUnits = DB::table('product_selling_units')
                    ->where('product_id', $baseUnit->product_id)
                    ->where('base_unit_id', $baseUnit->id)
                    ->where('auto_calculate_price', true)
                    ->where('is_base', false)
                    ->get();

                foreach ($sellingUnits as $unit) {
                    $newPurchase = round($baseUnit->base_purchase_price * $unit->conversion_factor, 2);
                    $newSelling = round($baseUnit->base_selling_price * $unit->conversion_factor, 2);

                    DB::table('product_selling_units')
                        ->where('id', $unit->id)
                        ->update([
                            'unit_purchase_price' => $newPurchase,
                            'unit_selling_price' => $newSelling,
                            'updated_at' => now(),
                        ]);

                    Log::info('🔄 تحديث وحدة فرعية', [
                        'unit_id' => $unit->id,
                        'unit_code' => $unit->unit_code,
                        'conversion_factor' => $unit->conversion_factor,
                        'new_purchase' => $newPurchase,
                        'new_selling' => $newSelling,
                    ]);
                }

                Log::info('✅ تم تحديث جميع الوحدات الفرعية', [
                    'count' => $sellingUnits->count(),
                ]);
            }

            Log::info('✅ Observer: تم تحديث الأسعار بنجاح', [
                'product_id' => $baseUnit->product_id,
                'base_unit_id' => $baseUnit->id,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Observer: فشل تحديث الأسعار', [
                'base_unit_id' => $baseUnit->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * يتم تنفيذه بعد إنشاء product_base_units
     */
    public function created(ProductBaseUnit $baseUnit)
    {
        Log::info('✅ Observer: تم إنشاء base unit جديد', [
            'id' => $baseUnit->id,
            'product_id' => $baseUnit->product_id,
        ]);
    }
}