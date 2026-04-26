<?php

namespace App\Services;

use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderComponent;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Models\ProductWarehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ManufacturingOrderService
{
    public function __construct(
        private InventoryMovementService $inventoryService,
        private ProductService $productService
    ) {}

    /**
     * Create a new manufacturing order
     * Calculates all costs automatically from components + additional costs
     */
    public function createOrder(array $data): ManufacturingOrder
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            // Calculate components total
            $componentsTotal = 0;
            if (!empty($data['components'])) {
                foreach ($data['components'] as $componentData) {
                    $quantity = (float) ($componentData['quantity'] ?? 0);
                    $thickness = (float) ($componentData['thickness_cm'] ?? 0);
                    $width = (float) ($componentData['width_cm'] ?? 0);
                    $length = (float) ($componentData['length_cm'] ?? 0);
                    $pricePerCubicMeter = (float) ($componentData['price_per_cubic_meter'] ?? 0);
                    $volumeCm3 = $quantity * $thickness * $width * $length;
                    $componentCost = ($volumeCm3 / 1000000) * $pricePerCubicMeter;
                    $componentsTotal += $componentCost;
                }
            }

            // Calculate additional costs total
            $additionalTotal = (float) ($data['waste_cost'] ?? 0)
                + (float) ($data['labor_cost'] ?? 0)
                + (float) ($data['nails_cost'] ?? 0)
                + (float) ($data['tips_cost'] ?? 0)
                + (float) ($data['transport_cost'] ?? 0)
                + (float) ($data['fumigation_cost'] ?? 0);

            // grand_total_cost = components_total + additional_total (per pallet)
            $costPerUnit = $componentsTotal + $additionalTotal;

            // profit_amount (per pallet) = cost_per_unit × (profit_margin / 100)
            $profitMargin = (float) ($data['profit_margin'] ?? 0);
            $profitAmountPerUnit = $costPerUnit * ($profitMargin / 100);
            $sellingPricePerUnit = $costPerUnit + $profitAmountPerUnit;

            // total_cost = cost_per_unit × quantity_produced
            $quantityProduced = (float) ($data['quantity_produced'] ?? 0);
            $totalCost = $costPerUnit * $quantityProduced;

            // total profit amount = profit_amount_per_unit × quantity_produced
            $totalProfitAmount = $profitAmountPerUnit * $quantityProduced;

            $order = ManufacturingOrder::create([
                'order_number' => ManufacturingOrder::generateOrderNumber(),
                'product_id' => $data['product_id'] ?? null,
                'product_name' => $data['product_name'],
                'quantity_produced' => $quantityProduced,
                'cost_per_unit' => $costPerUnit,
                'total_cost' => $totalCost,
                'selling_price_per_unit' => $sellingPricePerUnit,
                // Additional costs
                'waste_cost' => $data['waste_cost'] ?? 0,
                'labor_cost' => $data['labor_cost'] ?? 0,
                'nails_cost' => $data['nails_cost'] ?? 0,
                'tips_cost' => $data['tips_cost'] ?? 0,
                'transport_cost' => $data['transport_cost'] ?? 0,
                'fumigation_cost' => $data['fumigation_cost'] ?? 0,
                'profit_margin' => $profitMargin,
                'profit_amount' => $totalProfitAmount,
                'status' => 'draft',
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Create components
            if (!empty($data['components'])) {
                foreach ($data['components'] as $componentData) {
                    $this->addComponent($order, $componentData);
                }
            }

            Log::info('Manufacturing order created', [
                'order_number' => $order->order_number,
                'product_name' => $order->product_name
            ]);

            return $order->fresh(['components', 'product']);
        });
    }

    /**
     * Update an existing manufacturing order
     */
    public function updateOrder(ManufacturingOrder $order, array $data): ManufacturingOrder
    {
        if (!$order->can_edit) {
            throw new Exception('Cannot update an order that is ' . $order->status);
        }

        return DB::transaction(function () use ($order, $data) {
            // If components or costs are being updated, recalculate
            if (isset($data['components']) || isset($data['waste_cost']) || isset($data['profit_margin'])) {
                // Calculate components total
                $componentsTotal = 0;
                $components = $data['components'] ?? $order->components()->get()->toArray();

                foreach ($components as $componentData) {
                    // If it's an existing component model, use its values; else array
                    if (is_object($componentData)) {
                        $quantity = (float) $componentData->quantity;
                        $thickness = (float) $componentData->thickness_cm;
                        $width = (float) $componentData->width_cm;
                        $length = (float) $componentData->length_cm;
                        $pricePerCubicMeter = (float) $componentData->price_per_cubic_meter;
                    } else {
                        $quantity = (float) ($componentData['quantity'] ?? 0);
                        $thickness = (float) ($componentData['thickness_cm'] ?? 0);
                        $width = (float) ($componentData['width_cm'] ?? 0);
                        $length = (float) ($componentData['length_cm'] ?? 0);
                        $pricePerCubicMeter = (float) ($componentData['price_per_cubic_meter'] ?? 0);
                    }
                    $volumeCm3 = $quantity * $thickness * $width * $length;
                    $componentsTotal += ($volumeCm3 / 1000000) * $pricePerCubicMeter;
                }

                // Calculate additional total
                $additionalTotal = (float) ($data['waste_cost'] ?? $order->waste_cost)
                    + (float) ($data['labor_cost'] ?? $order->labor_cost)
                    + (float) ($data['nails_cost'] ?? $order->nails_cost)
                    + (float) ($data['tips_cost'] ?? $order->tips_cost)
                    + (float) ($data['transport_cost'] ?? $order->transport_cost)
                    + (float) ($data['fumigation_cost'] ?? $order->fumigation_cost);

                $costPerUnit = $componentsTotal + $additionalTotal;
                $quantityProduced = (float) ($data['quantity_produced'] ?? $order->quantity_produced);
                $profitMargin = (float) ($data['profit_margin'] ?? $order->profit_margin);
                $profitAmountPerUnit = $costPerUnit * ($profitMargin / 100);
                $sellingPricePerUnit = $costPerUnit + $profitAmountPerUnit;
                $totalCost = $costPerUnit * $quantityProduced;
                $totalProfitAmount = $profitAmountPerUnit * $quantityProduced;

                $updateFields = [
                    'cost_per_unit' => $costPerUnit,
                    'total_cost' => $totalCost,
                    'selling_price_per_unit' => $sellingPricePerUnit,
                    'profit_margin' => $profitMargin,
                    'profit_amount' => $totalProfitAmount,
                ];

                // Merge with passed data for update
                $order->update(array_merge($data, $updateFields));
            } else {
                $order->update($data);
            }

            // Update components if provided
            if (isset($data['components'])) {
                $order->components()->delete();
                foreach ($data['components'] as $componentData) {
                    $this->addComponent($order, $componentData);
                }
            }

            Log::info('Manufacturing order updated', ['order_number' => $order->order_number]);

            return $order->fresh(['components', 'product']);
        });
    }

    /**
     * Add a component to a manufacturing order
     * Each component represents a wood piece with cubic volume calculation
     * Also fills legacy fields (component_name, unit_cost) for backward compatibility
     */
    public function addComponent(ManufacturingOrder $order, array $data): ManufacturingOrderComponent
    {
        // Calculate volume_cm3 = quantity × thickness × width × length
        $quantity = (float) ($data['quantity'] ?? 0);
        $thickness = (float) ($data['thickness_cm'] ?? 0);
        $width = (float) ($data['width_cm'] ?? 0);
        $length = (float) ($data['length_cm'] ?? 0);
        $pricePerCubicMeter = (float) ($data['price_per_cubic_meter'] ?? 0);

        $volumeCm3 = $quantity * $thickness * $width * $length;

        // component_total = (volume_cm3 / 1,000,000) × price_per_cubic_meter
        $totalCost = ($volumeCm3 / 1000000) * $pricePerCubicMeter;

        // Legacy fields to maintain DB compatibility
        $componentName = $data['component_type'] ?? 'مكون';
        $unit = $data['unit'] ?? 'قطعة';
        // Compute unit_cost as total_cost / quantity to satisfy NOT NULL column
        $unitCost = $quantity > 0 ? $totalCost / $quantity : 0;

        return ManufacturingOrderComponent::create([
            'order_id' => $order->id,
            'component_name' => $componentName,  // legacy
            'component_type' => $data['component_type'] ?? null,
            'quantity' => $quantity,
            'unit' => $unit,  // legacy
            'thickness_cm' => $thickness,
            'width_cm' => $width,
            'length_cm' => $length,
            'volume_cm3' => $volumeCm3,
            'price_per_cubic_meter' => $pricePerCubicMeter,
            'unit_cost' => $unitCost,  // legacy field
            'total_cost' => $totalCost,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Confirm a manufacturing order (mark as confirmed, ready for production)
     */
    public function confirmOrder(ManufacturingOrder $order): ManufacturingOrder
    {
        if (!$order->canBeConfirmed()) {
            throw new Exception('Order cannot be confirmed. Check status and components.');
        }

        // Validate profit margin warning
        $margin = ($order->selling_price_per_unit - $order->cost_per_unit) / $order->cost_per_unit * 100;
        if ($margin < 10) {
            Log::warning('Low profit margin on manufacturing order', [
                'order_number' => $order->order_number,
                'margin' => $margin
            ]);
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'confirmed',
                'updated_by' => Auth::id(),
            ]);

            Log::info('Manufacturing order confirmed', ['order_number' => $order->order_number]);

            return $order->fresh();
        });
    }

    /**
     * Complete a manufacturing order and add to inventory
     * This is the CORE transaction that integrates with the product catalog
     * Creates the product if it doesn't exist, then updates pricing and adds stock
     */
    public function completeOrder(ManufacturingOrder $order, int $warehouseId, ?int $productId = null): ManufacturingOrder
    {
        if (!$order->canBeCompleted()) {
            throw new Exception('Order cannot be completed. Must be confirmed first.');
        }

        return DB::transaction(function () use ($order, $warehouseId, $productId) {

            // ✅ STEP 1: Prepare product data
            $productData = [
                'name' => $order->product_name,
                'product_type' => 'manufactured',
                'is_manufactured' => true,
                'base_unit' => 'piece',
                'category' => 'Manufactured Products',
                'purchase_price' => $order->cost_per_unit,
                'selling_price' => $order->selling_price_per_unit,
                'warehouses' => [
                    [
                        'warehouse_id' => $warehouseId,
                        'quantity' => $order->quantity_produced,
                        'min_stock' => 10,
                    ]
                ],
                'is_active' => true,
            ];

            // ✅ STEP 2: Use ProductService to create/update product
            // This ensures base units and selling units are created properly
            $product = $this->productService->createProduct($productData);

            // ✅ STEP 3: Record production inventory movement using InventoryMovementService
            $this->inventoryService->recordMovement([
                'warehouse_id' => $warehouseId,
                'product_id' => $product->id,
                'movement_type' => 'production',
                'quantity_change' => $order->quantity_produced,
                'unit_cost' => $order->cost_per_unit,
                'unit_price' => $order->selling_price_per_unit,
                'reference_type' => ManufacturingOrder::class,
                'reference_id' => $order->id,
                'notes' => "Production from order {$order->order_number}",
                'created_by' => Auth::id(),
            ]);

            // ✅ STEP 4: Complete the order
            $order->update([
                'status' => 'completed',
                'product_id' => $product->id,
                'produced_at' => now(),
                'completed_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            Log::info('Manufacturing order completed', [
                'order_number' => $order->order_number,
                'product_id' => $product->id,
                'quantity_produced' => $order->quantity_produced,
                'warehouse_id' => $warehouseId,
            ]);

            return $order->fresh(['components', 'product', 'inventoryMovements']);
        });
    }

    /**
     * Cancel a manufacturing order
     */
    public function cancelOrder(ManufacturingOrder $order, string $reason = null): ManufacturingOrder
    {
        if (!in_array($order->status, ['draft', 'confirmed'])) {
            throw new Exception('Cannot cancel an order that is ' . $order->status);
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->update([
                'status' => 'cancelled',
                'notes' => $order->notes . "\n\nCancelled: " . $reason,
                'updated_by' => Auth::id(),
            ]);

            Log::info('Manufacturing order cancelled', [
                'order_number' => $order->order_number,
                'reason' => $reason
            ]);

            return $order->fresh();
        });
    }

    /**
     * Delete a manufacturing order (only draft orders)
     */
    public function deleteOrder(ManufacturingOrder $order): void
    {
        if (!$order->is_draft) {
            throw new Exception('Cannot delete an order that is not in draft status');
        }

        DB::transaction(function () use ($order) {
            $order->components()->delete();
            $order->delete();

            Log::info('Manufacturing order deleted', ['order_number' => $order->order_number]);
        });
    }

    /**
     * Calculate costs from components
     */
    public function calculateCosts(array $components): array
    {
        $totalCost = 0;

        foreach ($components as $component) {
            $quantity = (float) ($component['quantity'] ?? 0);
            $thickness = (float) ($component['thickness_cm'] ?? 0);
            $width = (float) ($component['width_cm'] ?? 0);
            $length = (float) ($component['length_cm'] ?? 0);
            $pricePerCubicMeter = (float) ($component['price_per_cubic_meter'] ?? 0);

            $volumeCm3 = $quantity * $thickness * $width * $length;
            $totalCost += ($volumeCm3 / 1000000) * $pricePerCubicMeter;
        }

        return [
            'components_total_cost' => round($totalCost, 4),
        ];
    }

    /**
     * Get manufacturing orders for a product
     */
    public function getOrdersForProduct(int $productId, array $filters = [])
    {
        $query = ManufacturingOrder::with(['components', 'creator', 'completer'])
            ->where('product_id', $productId)
            ->latest();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get manufacturing order statistics
     */
    public function getStatistics(array $filters = []): array
    {
        $query = ManufacturingOrder::query();

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = "draft" THEN 1 ELSE 0 END) as draft_orders,
            SUM(CASE WHEN status = "confirmed" THEN 1 ELSE 0 END) as confirmed_orders,
            SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
            SUM(total_cost) as total_manufacturing_cost,
            SUM(quantity_produced) as total_quantity_produced
        ')->first();

        return [
            'total_orders' => $stats->total_orders ?? 0,
            'draft_orders' => $stats->draft_orders ?? 0,
            'confirmed_orders' => $stats->confirmed_orders ?? 0,
            'completed_orders' => $stats->completed_orders ?? 0,
            'cancelled_orders' => $stats->cancelled_orders ?? 0,
            'total_manufacturing_cost' => $stats->total_manufacturing_cost ?? 0,
            'total_quantity_produced' => $stats->total_quantity_produced ?? 0,
        ];
    }
}