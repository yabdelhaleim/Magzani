<?php
// app/Observers/ProductBaseUnitObserver.php

namespace App\Observers;

use App\Models\ProductBaseUnit;
use App\Models\ProductSellingUnit;
use Illuminate\Support\Facades\Log;

/**
 * 🎯 Observer للتحديث التلقائي لوحدات البيع
 * 
 * عند تحديث سعر الوحدة الأساسية، 
 * يتم تحديث كل وحدات البيع المرتبطة تلقائياً
 */
class ProductBaseUnitObserver
{
    /**
     * 🔄 عند تحديث الوحدة الأساسية
     */
    public function updated(ProductBaseUnit $baseUnit)
    {
        // تحقق إذا كان السعر تغيّر
        if ($this->priceChanged($baseUnit)) {
            $this->updateSellingUnits($baseUnit);
        }
    }

    /**
     * ✅ تحقق من تغيير السعر
     */
    private function priceChanged(ProductBaseUnit $baseUnit): bool
    {
        return $baseUnit->isDirty('base_purchase_price') 
            || $baseUnit->isDirty('base_selling_price');
    }

    /**
     * 🔄 تحديث وحدات البيع
     */
    private function updateSellingUnits(ProductBaseUnit $baseUnit)
    {
        if (!$baseUnit->auto_update_selling_units) {
            Log::info('⚠️ التحديث التلقائي معطّل', [
                'product_id' => $baseUnit->product_id
            ]);
            return;
        }

        $updatedCount = 0;
        
        // جلب كل وحدات البيع المرتبطة
        $sellingUnits = ProductSellingUnit::where('base_unit_id', $baseUnit->id)
            ->where('auto_calculate_price', true)
            ->where('is_active', true)
            ->get();

        foreach ($sellingUnits as $sellingUnit) {
            // حساب السعر الجديد
            $sellingUnit->unit_purchase_price = $baseUnit->base_purchase_price * $sellingUnit->conversion_factor;
            $sellingUnit->unit_selling_price = $baseUnit->base_selling_price * $sellingUnit->conversion_factor;
            
            $sellingUnit->save();
            $updatedCount++;
        }

        Log::info('✅ تم تحديث وحدات البيع تلقائياً', [
            'product_id' => $baseUnit->product_id,
            'base_unit_id' => $baseUnit->id,
            'updated_count' => $updatedCount,
            'old_price' => $baseUnit->getOriginal('base_selling_price'),
            'new_price' => $baseUnit->base_selling_price,
        ]);
    }
}