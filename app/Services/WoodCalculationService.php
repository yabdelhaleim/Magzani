<?php

namespace App\Services;

class WoodCalculationService
{
    public function calculateVolumeCm3(float $length, float $width, float $thickness, int $quantity): float
    {
        return $length * $width * $thickness * $quantity;
    }

    public function cm3ToM3(float $cm3): float
    {
        return $cm3 / 1000000;
    }

    public function cm3ToM2(float $cm3, float $thicknessCm): float
    {
        if ($thicknessCm <= 0) return 0;
        return $cm3 / $thicknessCm / 10000;
    }

    public function m3ToCm3(float $m3): float
    {
        return $m3 * 1000000;
    }

    public function pricePerCm3(float $pricePerM3): float
    {
        return $pricePerM3 / 1000000;
    }
}
