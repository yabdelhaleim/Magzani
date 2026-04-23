<?php

namespace App\Services;

use App\Models\BomComponent;
use App\Models\ManufacturingCost;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManufacturingCostService
{
    public function calculateCosts(array $data): array
    {
        $components = $data['components'] ?? [];
        $pricePerM3 = (float) ($data['price_per_cubic_meter'] ?? 0);
        $laborCost = (float) ($data['labor_cost'] ?? 0);
        $nailsCost = (float) ($data['nails_hardware_cost'] ?? 0);
        $transportCost = (float) ($data['transportation_cost'] ?? 0);
        $tipsCost = (float) ($data['tips_misc_cost'] ?? 0);
        $fumigationCost = (float) ($data['fumigation_cost'] ?? 0);
        $profitPercentage = (float) ($data['profit_percentage'] ?? 0);

        $componentResults = [];
        $totalVolumeCm3 = 0;

        foreach ($components as $index => $comp) {
            $quantity = (float) ($comp['quantity'] ?? 1);
            $length = (float) ($comp['length_cm'] ?? 0);
            $width = (float) ($comp['width_cm'] ?? 0);
            $thickness = (float) ($comp['thickness_cm'] ?? 0);
            $volume = $length * $width * $thickness * $quantity;

            $componentResults[] = [
                'component_name' => $comp['component_name'] ?? '',
                'quantity' => $quantity,
                'length_cm' => $length,
                'width_cm' => $width,
                'thickness_cm' => $thickness,
                'volume_cm3' => round($volume, 4),
                'sort_order' => $index,
            ];

            $totalVolumeCm3 += $volume;
        }

        $totalVolumeM3 = $totalVolumeCm3 / 1_000_000;
        $materialCost = $totalVolumeM3 * $pricePerM3;
        $additionalCostsTotal = $laborCost + $nailsCost + $transportCost + $tipsCost + $fumigationCost;
        $totalCost = $materialCost + $additionalCostsTotal;
        $profitAmount = $totalCost * ($profitPercentage / 100);
        $finalPrice = $totalCost + $profitAmount;

        return [
            'components' => $componentResults,
            'total_volume_cm3' => round($totalVolumeCm3, 4),
            'total_volume_m3' => round($totalVolumeM3, 6),
            'material_cost' => round($materialCost, 2),
            'labor_cost' => round($laborCost, 2),
            'nails_hardware_cost' => round($nailsCost, 2),
            'transportation_cost' => round($transportCost, 2),
            'tips_misc_cost' => round($tipsCost, 2),
            'fumigation_cost' => round($fumigationCost, 2),
            'additional_costs_total' => round($additionalCostsTotal, 2),
            'total_cost' => round($totalCost, 2),
            'profit_percentage' => round($profitPercentage, 2),
            'profit_amount' => round($profitAmount, 2),
            'final_price' => round($finalPrice, 2),
        ];
    }

    public function createManufacturingCost(array $data): ManufacturingCost
    {
        return DB::transaction(function () use ($data) {
            $calculated = $this->calculateCosts($data);
            $userId = Auth::id();

            $cost = ManufacturingCost::create([
                'product_id' => $data['product_id'] ?? null,
                'product_name' => $data['product_name'],
                'price_per_cubic_meter' => $data['price_per_cubic_meter'],
                'total_volume_cm3' => $calculated['total_volume_cm3'],
                'total_volume_m3' => $calculated['total_volume_m3'],
                'material_cost' => $calculated['material_cost'],
                'labor_cost' => $calculated['labor_cost'],
                'nails_hardware_cost' => $calculated['nails_hardware_cost'],
                'transportation_cost' => $calculated['transportation_cost'],
                'tips_misc_cost' => $calculated['tips_misc_cost'],
                'fumigation_cost' => $calculated['fumigation_cost'],
                'additional_costs_total' => $calculated['additional_costs_total'],
                'total_cost' => $calculated['total_cost'],
                'profit_percentage' => $calculated['profit_percentage'],
                'profit_amount' => $calculated['profit_amount'],
                'final_price' => $calculated['final_price'],
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            foreach ($calculated['components'] as $compData) {
                BomComponent::create([
                    'manufacturing_cost_id' => $cost->id,
                    'component_name' => $compData['component_name'],
                    'quantity' => $compData['quantity'],
                    'length_cm' => $compData['length_cm'],
                    'width_cm' => $compData['width_cm'],
                    'thickness_cm' => $compData['thickness_cm'],
                    'volume_cm3' => $compData['volume_cm3'],
                    'sort_order' => $compData['sort_order'],
                ]);
            }

            $this->clearCache();

            Log::info('Manufacturing cost created', ['id' => $cost->id, 'product_name' => $cost->product_name]);

            return $cost->fresh('components');
        });
    }

    public function updateManufacturingCost(ManufacturingCost $cost, array $data): ManufacturingCost
    {
        return DB::transaction(function () use ($cost, $data) {
            $calculated = $this->calculateCosts($data);

            $cost->update([
                'product_id' => $data['product_id'] ?? $cost->product_id,
                'product_name' => $data['product_name'],
                'price_per_cubic_meter' => $data['price_per_cubic_meter'],
                'total_volume_cm3' => $calculated['total_volume_cm3'],
                'total_volume_m3' => $calculated['total_volume_m3'],
                'material_cost' => $calculated['material_cost'],
                'labor_cost' => $calculated['labor_cost'],
                'nails_hardware_cost' => $calculated['nails_hardware_cost'],
                'transportation_cost' => $calculated['transportation_cost'],
                'tips_misc_cost' => $calculated['tips_misc_cost'],
                'fumigation_cost' => $calculated['fumigation_cost'],
                'additional_costs_total' => $calculated['additional_costs_total'],
                'total_cost' => $calculated['total_cost'],
                'profit_percentage' => $calculated['profit_percentage'],
                'profit_amount' => $calculated['profit_amount'],
                'final_price' => $calculated['final_price'],
                'notes' => $data['notes'] ?? $cost->notes,
                'updated_by' => Auth::id(),
            ]);

            $cost->components()->delete();

            foreach ($calculated['components'] as $compData) {
                BomComponent::create([
                    'manufacturing_cost_id' => $cost->id,
                    'component_name' => $compData['component_name'],
                    'quantity' => $compData['quantity'],
                    'length_cm' => $compData['length_cm'],
                    'width_cm' => $compData['width_cm'],
                    'thickness_cm' => $compData['thickness_cm'],
                    'volume_cm3' => $compData['volume_cm3'],
                    'sort_order' => $compData['sort_order'],
                ]);
            }

            $this->clearCache();

            Log::info('Manufacturing cost updated', ['id' => $cost->id]);

            return $cost->fresh('components');
        });
    }

    public function deleteManufacturingCost(ManufacturingCost $cost): void
    {
        DB::transaction(function () use ($cost) {
            $cost->components()->delete();
            $cost->delete();
            $this->clearCache();

            Log::info('Manufacturing cost deleted', ['id' => $cost->id]);
        });
    }

    public function confirmCost(ManufacturingCost $cost): ManufacturingCost
    {
        return DB::transaction(function () use ($cost) {
            $cost->update([
                'status' => 'confirmed',
                'updated_by' => Auth::id(),
            ]);

            if ($cost->product_id) {
                $cost->product->update([
                    'selling_price' => $cost->final_price,
                    'purchase_price' => $cost->total_cost,
                ]);
            }

            $this->clearCache();

            Log::info('Manufacturing cost confirmed', ['id' => $cost->id]);

            return $cost->fresh();
        });
    }

    private function clearCache(): void
    {
        Cache::forget('manufacturing_costs_recent');
        Cache::forget('manufacturing_costs_stats');
    }
}
