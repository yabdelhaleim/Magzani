<?php

namespace App\Services;

use App\Models\WoodStock;
use App\Models\WoodDispensing;
use App\Models\ManufacturingOrder;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WoodStockService
{
    public function __construct(
        private WoodCalculationService $calculationService,
        private InventoryMovementService $inventoryService,
        private ProductService $productService
    ) {}

    public function createStock(array $data): WoodStock
    {
        return DB::transaction(function () use ($data) {
            // Get or create default warehouse
            $warehouseId = $data['warehouse_id'] ?? null;
            if (!$warehouseId) {
                $warehouse = Warehouse::where('is_active', true)->first();
                $warehouseId = $warehouse?->id;
                $data['warehouse_id'] = $warehouseId;
            }

            // Create wood stock
            $woodStock = WoodStock::create($data);

            // Create or get wood product
            $woodProduct = $this->getOrCreateWoodProduct($woodStock, $warehouseId);
            $woodStock->update(['product_id' => $woodProduct->id]);

            // Record inventory movement
            $supplierName = $woodStock->supplier->name ?? 'غير محدد';
            $this->inventoryService->recordMovement([
                'warehouse_id' => $warehouseId,
                'product_id' => $woodProduct->id,
                'movement_type' => 'wood_stock_in',
                'quantity_change' => $woodStock->volume_m3_total, // Store in m³
                'unit_cost' => $woodStock->unit_cost,
                'unit_price' => $woodStock->unit_cost, // Wood is raw material, cost = price
                'reference_type' => WoodStock::class,
                'reference_id' => $woodStock->id,
                'notes' => "استلام خشب من {$supplierName} - {$woodStock->purchase_reference}",
                'created_by' => auth()->id(),
            ]);

            return $woodStock->fresh(['warehouse', 'product', 'inventoryMovements']);
        });
    }

    public function dispense(WoodStock $stock, array $data): WoodDispensing
    {
        return DB::transaction(function () use ($stock, $data) {
            $volumeTaken = (float) $data['volume_cm3_taken'];

            if ($stock->remaining_cm3 < $volumeTaken) {
                throw ValidationException::withMessages([
                    'volume_cm3_taken' => 'The requested volume exceeds the remaining stock in this batch.',
                ]);
            }

            $data['user_id'] = auth()->id() ?? 1;

            $dispensing = $stock->woodDispensings()->create($data);

            // Record inventory movement if linked to warehouse and product
            if ($stock->warehouse_id && $stock->product_id) {
                $this->inventoryService->recordMovement([
                    'warehouse_id' => $stock->warehouse_id,
                    'product_id' => $stock->product_id,
                    'movement_type' => 'wood_stock_out',
                    'quantity_change' => -($volumeTaken / 1000000), // Convert cm³ to m³
                    'unit_cost' => $stock->unit_cost,
                    'unit_price' => $stock->unit_cost,
                    'reference_type' => WoodDispensing::class,
                    'reference_id' => $dispensing->id,
                    'notes' => $data['notes'] ?? 'صرف خشب',
                    'created_by' => auth()->id(),
                ]);
            }

            return $dispensing;
        });
    }

    /**
     * Get or create a wood product for the given wood stock
     */
    private function getOrCreateWoodProduct(WoodStock $stock, int $warehouseId): Product
    {
        // Try to find existing wood product with same dimensions
        $productName = "خشب خام {$stock->length_cm}×{$stock->width_cm}×{$stock->thickness_cm} سم";

        $existingProduct = Product::where('name', $productName)
            ->where('product_type', 'raw_material')
            ->first();

        if ($existingProduct) {
            return $existingProduct;
        }

        // Create new wood product
        return $this->productService->createProduct([
            'name' => $productName,
            'code' => 'WOOD-' . $stock->id,
            'product_type' => 'raw_material',
            'category' => 'Raw Materials',
            'base_unit' => 'm3',
            'base_unit_label' => 'متر مكعب',
            'purchase_price' => $stock->unit_cost,
            'selling_price' => $stock->unit_cost * 1.2, // 20% markup
            'warehouses' => [
                [
                    'warehouse_id' => $warehouseId,
                    'quantity' => 0, // Will be updated by inventory movement
                    'min_stock' => 10,
                ]
            ],
            'is_active' => true,
        ]);
    }

    public function getStockSummary(): array
    {
        $stocks = WoodStock::all();

        $totalCm3 = $stocks->sum('volume_cm3');
        $remainingCm3 = $stocks->sum('remaining_cm3');

        $totalM2 = 0;
        $remainingM2 = 0;

        foreach ($stocks as $stock) {
            $totalM2 += $stock->volume_m2_total;
            $remainingM2 += $stock->remaining_m2;
        }

        return [
            'total_m3' => $this->calculationService->cm3ToM3($totalCm3),
            'total_m2' => $totalM2,
            'remaining_m3' => $this->calculationService->cm3ToM3($remainingCm3),
            'remaining_m2' => $remainingM2,
        ];
    }

    public function getStockForOrder(ManufacturingOrder $order): Collection
    {
        return WoodStock::withStock()->get();
    }
}
