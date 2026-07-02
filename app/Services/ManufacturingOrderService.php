<?php

namespace App\Services;

use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderComponent;
use App\Models\Product;
use App\Models\RawMaterialTemplate;
use App\Models\WoodStock;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManufacturingOrderService
{
    public function __construct(
        private InventoryMovementService $inventoryService,
        private ProductService $productService,
        private WoodStockService $woodStockService
    ) {}

    /**
     * حجم المكوّن بالسم³: العدد × سمك × عرض × طول (كلها بالسنتيمتر)
     */
    private function componentVolumeCm3(array $data): float
    {
        return (float) ($data['quantity'] ?? 0)
            * (float) ($data['thickness_cm'] ?? 0)
            * (float) ($data['width_cm'] ?? 0)
            * (float) ($data['length_cm'] ?? 0);
    }

    /**
     * سعر المتر المكعب للمكوّن: من الحقل، أو من دفعة الخشب، أو من قالب الخامة بالاسم
     */
    private function resolveComponentPricePerM3(array $data): float
    {
        $price = (float) ($data['price_per_cubic_meter'] ?? 0);
        if ($price > 0) {
            return $price;
        }

        if (! empty($data['wood_stock_id'])) {
            $stock = WoodStock::query()->find((int) $data['wood_stock_id']);
            if ($stock && (float) $stock->unit_cost > 0) {
                return (float) $stock->unit_cost;
            }
        }

        if (! empty($data['component_type'])) {
            $raw = RawMaterialTemplate::where('name', $data['component_type'])->first();

            return $raw ? (float) $raw->buy_price : 0;
        }

        return 0;
    }

    /**
     * Create a new manufacturing order
     * Calculates all costs automatically from components + additional costs
     */
    public function createOrder(array $data): ManufacturingOrder
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            // Calculate components total (م³ × سعر المتر المكعب)
            $componentsTotal = 0;
            if (! empty($data['components'])) {
                foreach ($data['components'] as $componentData) {
                    $volumeCm3 = $this->componentVolumeCm3($componentData);
                    $pricePerM3 = $this->resolveComponentPricePerM3($componentData);
                    $componentsTotal += ($volumeCm3 / 1_000_000) * $pricePerM3;
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
                'customer_id' => $data['customer_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Create components
            if (! empty($data['components'])) {
                foreach ($data['components'] as $componentData) {
                    $this->addComponent($order, $componentData);
                }

            }

            Log::info('Manufacturing order created', [
                'order_number' => $order->order_number,
                'product_name' => $order->product_name,
            ]);

            return $order->fresh(['components', 'product']);
        });
    }

    /**
     * Update an existing manufacturing order
     */
    public function updateOrder(ManufacturingOrder $order, array $data): ManufacturingOrder
    {
        if (! $order->can_edit) {
            throw new Exception('Cannot update an order that is '.$order->status);
        }

        return DB::transaction(function () use ($order, $data) {
            // If components or costs are being updated, recalculate
            if (isset($data['components']) || isset($data['waste_cost']) || isset($data['profit_margin'])) {
                // Calculate components total
                $componentsTotal = 0;
                $components = $data['components'] ?? $order->components()->get()->toArray();

                foreach ($components as $componentData) {
                    if (is_object($componentData)) {
                        $arr = [
                            'quantity' => (float) $componentData->quantity,
                            'thickness_cm' => (float) $componentData->thickness_cm,
                            'width_cm' => (float) $componentData->width_cm,
                            'length_cm' => (float) $componentData->length_cm,
                            'price_per_cubic_meter' => (float) $componentData->price_per_cubic_meter,
                            'wood_stock_id' => $componentData->wood_stock_id,
                            'component_type' => $componentData->component_type,
                        ];
                    } else {
                        $arr = $componentData;
                    }
                    $volumeCm3 = $this->componentVolumeCm3($arr);
                    $ppm = $this->resolveComponentPricePerM3($arr);
                    $componentsTotal += ($volumeCm3 / 1_000_000) * $ppm;
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
        $quantity = (float) ($data['quantity'] ?? 0);
        $thickness = (float) ($data['thickness_cm'] ?? 0);
        $width = (float) ($data['width_cm'] ?? 0);
        $length = (float) ($data['length_cm'] ?? 0);
        $pricePerCubicMeter = $this->resolveComponentPricePerM3($data);

        $volumeCm3 = $this->componentVolumeCm3($data);

        // component_total = (volume_cm3 / 1,000,000) × price_per_cubic_meter
        $totalCost = ($volumeCm3 / 1_000_000) * $pricePerCubicMeter;

        // Legacy fields to maintain DB compatibility
        $componentName = $data['component_type'] ?? 'مكون';
        $unit = $data['unit'] ?? 'قطعة';
        // Compute unit_cost as total_cost / quantity to satisfy NOT NULL column
        $unitCost = $quantity > 0 ? $totalCost / $quantity : 0;

        return ManufacturingOrderComponent::create([
            'order_id' => $order->id,
            'wood_stock_id' => ! empty($data['wood_stock_id']) ? (int) $data['wood_stock_id'] : null,
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
        if (! $order->canBeConfirmed()) {
            throw new Exception('Order cannot be confirmed. Check status and components.');
        }

        // Validate profit margin warning
        $margin = ($order->selling_price_per_unit - $order->cost_per_unit) / $order->cost_per_unit * 100;
        if ($margin < 10) {
            Log::warning('Low profit margin on manufacturing order', [
                'order_number' => $order->order_number,
                'margin' => $margin,
            ]);
        }

        return DB::transaction(function () use ($order) {
            $order->load(['components.woodStock']);

            // Validate and reserve/deduct stock immediately
            foreach ($order->components as $component) {
                if ($component->wood_stock_id && $component->woodStock) {
                    $volumePerPalletCm3 = (float) $component->volume_cm3;
                    $volumeTakenCm3 = $volumePerPalletCm3 * (float) $order->quantity_produced;

                    // Verify availability first
                    if ($component->woodStock->remaining_cm3 < $volumeTakenCm3) {
                        throw new Exception("حجم الخشب المطلوب للمكون '{$component->component_name}' يتجاوز الرصيد المتبقي في دفعة الخشب.");
                    }

                    // Dispense stock (this decrements remaining wood stock volume and records movement)
                    $this->woodStockService->dispense($component->woodStock, [
                        'volume_cm3_taken' => $volumeTakenCm3,
                        'manufacturing_order_id' => $order->id,
                        'client_id' => $order->customer_id,
                        'notes' => 'حجز خشب لتأكيد أمر التصنيع '.$order->order_number,
                        'dispensed_at' => now()->toDateString(),
                    ]);
                } else {
                    $rawMaterial = RawMaterialTemplate::where('name', $component->component_type ?? '')->first();
                    if ($rawMaterial) {
                        $requiredQty = (float) $component->quantity * (float) $order->quantity_produced;
                        if ($rawMaterial->quantity < $requiredQty) {
                            throw new Exception("الكمية المطلوبة للمادة الخام '{$component->component_type}' غير متوفرة في المخزن.");
                        }
                        // Decrement stock immediately to reserve it
                        $rawMaterial->decrement('quantity', $requiredQty);
                    }
                }
            }

            $order->update([
                'status' => 'confirmed',
                'updated_by' => Auth::id(),
            ]);

            Log::info('Manufacturing order confirmed and stock reserved', ['order_number' => $order->order_number]);

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
        if (! $order->canBeCompleted()) {
            throw new Exception('Order cannot be completed. Must be confirmed first.');
        }

        return DB::transaction(function () use ($order, $warehouseId) {

            // Stock was already reserved and deducted at confirmation stage.

            // ✅ STEP 1–2: الكتالوج + وحدات البيع متوافقة مع فاتورة المبيعات (سعر/تكلفة من أمر التصنيع)
            $productData = [
                'name' => trim($order->product_name),
                'product_type' => 'manufactured',
                'is_manufactured' => true,
                'base_unit' => 'piece',
                'category' => 'Manufactured Products',
                'purchase_price' => $order->cost_per_unit,
                'selling_price' => $order->selling_price_per_unit,
                'warehouses' => [
                    [
                        'warehouse_id' => $warehouseId,
                        'quantity' => 0,
                        'min_stock' => 10,
                    ],
                ],
                'is_active' => true,
            ];

            $existing = $order->product_id ? Product::find($order->product_id) : null;
            if (! $existing) {
                $existing = Product::where('name', $productData['name'])->first();
            }

            if ($existing) {
                $productData['name'] = $existing->name;
                $productData['sku'] = $existing->sku;
                $productData['category'] = $existing->category ?: $productData['category'];
                $productData['base_unit'] = $existing->base_unit ?: $productData['base_unit'];
                $productData['product_type'] = 'manufactured';
                $productData['is_manufactured'] = true;
                $productData['price_change_reason'] = 'إكمال أمر تصنيع '.$order->order_number;

                $product = $this->productService->updateProduct($existing, $productData);
                $this->productService->ensureProductWarehousePivot($product, $warehouseId, 10);
            } else {
                $product = $this->productService->createProduct($productData);
            }

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

            // NOTE: Stock updates are handled by:
            // - woodStockService->dispense()      → raw wood components (wood_stock table)
            // - RawMaterialTemplate->decrement()  → other raw materials
            // - inventoryService->recordMovement() → finished product added to product_warehouse
            // No additional StockService calls needed here to avoid double-counting.

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
    public function cancelOrder(ManufacturingOrder $order, ?string $reason = null): ManufacturingOrder
    {
        if (! in_array($order->status, ['draft', 'confirmed'])) {
            throw new Exception('Cannot cancel an order that is '.$order->status);
        }

        return DB::transaction(function () use ($order, $reason) {
            $oldStatus = $order->status;

            $order->update([
                'status' => 'cancelled',
                'notes' => trim(($order->notes ?? '') . "\n\nCancelled: " . $reason),
                'updated_by' => Auth::id(),
            ]);

            // If the order was confirmed, we must release the reserved stock!
            if ($oldStatus === 'confirmed') {
                $order->load(['components.woodStock']);

                foreach ($order->components as $component) {
                    if ($component->wood_stock_id) {
                        // Find wood dispensing for this order and this wood stock
                        $dispensings = \App\Models\WoodDispensing::where('manufacturing_order_id', $order->id)
                            ->where('wood_stock_id', $component->wood_stock_id)
                            ->get();

                        foreach ($dispensings as $dispensing) {
                            // Reverse dispensing by deleting the dispensing record and its inventory movement
                            \App\Models\InventoryMovement::where('reference_type', \App\Models\WoodDispensing::class)
                                ->where('reference_id', $dispensing->id)
                                ->delete();
                            $dispensing->delete();
                        }
                    } else {
                        $rawMaterial = RawMaterialTemplate::where('name', $component->component_type ?? '')->first();
                        if ($rawMaterial) {
                            $requiredQty = (float) $component->quantity * (float) $order->quantity_produced;
                            $rawMaterial->increment('quantity', $requiredQty);
                        }
                    }
                }
            }

            Log::info('Manufacturing order cancelled and stock released', [
                'order_number' => $order->order_number,
                'reason' => $reason,
            ]);

            return $order->fresh();
        });
    }

    /**
     * Delete a manufacturing order (only draft orders)
     */
    public function deleteOrder(ManufacturingOrder $order): void
    {
        if (! $order->is_draft) {
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
            $volumeCm3 = $this->componentVolumeCm3($component);
            $ppm = $this->resolveComponentPricePerM3($component);
            $totalCost += ($volumeCm3 / 1_000_000) * $ppm;
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

        if (! empty($filters['status'])) {
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

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
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
