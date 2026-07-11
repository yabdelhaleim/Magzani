<?php

namespace App\Services;

use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use Exception;

class QuantityCalculationService
{
    /**
     * Convert quantity from one UoM to another
     */
    public function convert(float $quantity, int $fromUomId, int $toUomId): float
    {
        if ($fromUomId === $toUomId) {
            return $quantity;
        }

        // Try direct conversion
        $direct = UomConversion::where('from_uom_id', $fromUomId)
            ->where('to_uom_id', $toUomId)
            ->first();

        if ($direct) {
            return $quantity * (float) $direct->factor;
        }

        // Try reverse conversion
        $reverse = UomConversion::where('from_uom_id', $toUomId)
            ->where('to_uom_id', $fromUomId)
            ->first();

        if ($reverse && (float) $reverse->factor > 0) {
            return $quantity / (float) $reverse->factor;
        }

        // No conversion found
        throw new Exception("لا يوجد تعريف تحويل بين وحدات القياس المحددة.");
    }
}
