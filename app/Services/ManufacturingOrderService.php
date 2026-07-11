<?php

namespace App\Services;

use App\Models\ManufacturingOrder;
use App\Models\ManufacturingOrderComponent;
use App\Models\ManufacturingOrderExtraCost;
use App\Models\MaterialBatch;
use App\Models\MaterialDispensing;
use App\Models\Product;
use App\Models\RawMaterialTemplate;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManufacturingOrderService
{
    public function __construct(
        private InventoryMovementService $inventoryService,
        private ProductService $productService,
        private MaterialStockService $materialStockService,
        // Gap 2 — Standard Costing & Cost Variance (optional, per-tenant).
        private ?StandardCostingService $standardCostingService = null,
        private ?CostVariancePostingService $costVariancePostingService = null,
        // Gap 4 — Batch/Lot Tracking (genealogy recording).
        private ?BatchGenealogyService $batchGenealogyService = null,
    ) {
        // Resolve via container when not explicitly injected — keeps manual
        // instantiation (e.g. unit tests, queued jobs) cheap.
        $this->standardCostingService      ??= app(StandardCostingService::class);
        $this->costVariancePostingService  ??= app(CostVariancePostingService::class);
        $this->batchGenealogyService       ??= app(BatchGenealogyService::class);
    }

    /**
     * Create a new manufacturing order
     */
    public function createOrder(array $data): ManufacturingOrder
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            // Calculate components total (quantity * unit_cost per unit produced)
            $componentsTotal = 0;
            if (!empty($data['components'])) {
                foreach ($data['components'] as $componentData) {
                    $qty = (float) ($componentData['quantity'] ?? 0);
                    $unitCost = (float) ($componentData['unit_cost'] ?? $componentData['cost_per_uom'] ?? 0);
                    if ($unitCost <= 0 && !empty($componentData['material_batch_id'])) {
                        $batch = MaterialBatch::find($componentData['material_batch_id']);
                        $unitCost = $batch ? (float) $batch->unit_cost : 0;
                    }
                    $componentsTotal += $qty * $unitCost;
                }
            }

            // Calculate additional costs from extra_costs array
            $extraTotal = 0;
            if (!empty($data['extra_costs'])) {
                foreach ($data['extra_costs'] as $extra) {
                    $extraTotal += (float) ($extra['amount'] ?? 0);
                }
            }

            // Also support direct labor_cost/transport_cost if passed directly (e.g. from legacy or test wrappers)
            $laborCost = (float) ($data['labor_cost'] ?? 0);
            $transportCost = (float) ($data['transport_cost'] ?? 0);
            $additionalTotal = $extraTotal + $laborCost + $transportCost;

            $costPerUnit = $componentsTotal + $additionalTotal;

            $profitMargin = (float) ($data['profit_margin'] ?? 0);
            $profitAmountPerUnit = $costPerUnit * ($profitMargin / 100);
            $sellingPricePerUnit = $costPerUnit + $profitAmountPerUnit;

            $quantityProduced = (float) ($data['quantity_produced'] ?? 0);
            $totalCost = $costPerUnit * $quantityProduced;
            $totalProfitAmount = $profitAmountPerUnit * $quantityProduced;

            $order = ManufacturingOrder::create([
                'order_number' => ManufacturingOrder::generateOrderNumber(),
                'product_id' => $data['product_id'] ?? null,
                'product_name' => $data['product_name'],
                'quantity_produced' => $quantityProduced,
                'cost_per_unit' => $costPerUnit,
                'total_cost' => $totalCost,
                'selling_price_per_unit' => $sellingPricePerUnit,
                'labor_cost' => $laborCost, // keep labor_cost for Gap 1 accounting
                'profit_margin' => $profitMargin,
                'profit_amount' => $totalProfitAmount,
                'status' => 'draft',
                'warehouse_id' => $data['warehouse_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Save extra costs
            if (!empty($data['extra_costs'])) {
                foreach ($data['extra_costs'] as $extra) {
                    $order->extraCosts()->create([
                        'cost_type' => $extra['cost_type'],
                        'amount' => $extra['amount'],
                        'description' => $extra['description'] ?? null,
                    ]);
                }
            }

            // If transport cost was passed, save it as extra cost
            if ($transportCost > 0) {
                $order->extraCosts()->create([
                    'cost_type' => 'transport',
                    'amount' => $transportCost,
                    'description' => 'تكلفة نقل شحن شاحنة',
                ]);
            }

            // Create components
            if (!empty($data['components'])) {
                foreach ($data['components'] as $componentData) {
                    $this->addComponent($order, $componentData);
                }
            }

            Log::info('Manufacturing order created', [
                'order_number' => $order->order_number,
                'product_name' => $order->product_name,
            ]);

            return $order->fresh(['components', 'product', 'extraCosts']);
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
            $userId = Auth::id();

            // Calculate components total
            $componentsTotal = 0;
            $components = $data['components'] ?? $order->components()->get()->toArray();

            foreach ($components as $componentData) {
                $arr = is_object($componentData) ? $componentData->toArray() : $componentData;
                $qty = (float) ($arr['quantity'] ?? 0);
                $unitCost = (float) ($arr['unit_cost'] ?? $arr['cost_per_uom'] ?? 0);
                if ($unitCost <= 0 && !empty($arr['material_batch_id'])) {
                    $batch = MaterialBatch::find($arr['material_batch_id']);
                    $unitCost = $batch ? (float) $batch->unit_cost : 0;
                }
                $componentsTotal += $qty * $unitCost;
            }

            // Calculate extra costs
            $extraTotal = 0;
            if (isset($data['extra_costs'])) {
                foreach ($data['extra_costs'] as $extra) {
                    $extraTotal += (float) ($extra['amount'] ?? 0);
                }
            } else {
                $extraTotal = $order->extraCosts()->sum('amount');
            }

            $laborCost = (float) ($data['labor_cost'] ?? $order->labor_cost);
            $transportCost = (float) ($data['transport_cost'] ?? 0);
            $additionalTotal = $extraTotal + $laborCost + $transportCost;

            $costPerUnit = $componentsTotal + $additionalTotal;
            $quantityProduced = (float) ($data['quantity_produced'] ?? $order->quantity_produced);
            $profitMargin = (float) ($data['profit_margin'] ?? $order->profit_margin);
            $profitAmountPerUnit = $costPerUnit * ($profitMargin / 100);
            $sellingPricePerUnit = $costPerUnit + $profitAmountPerUnit;
            $totalCost = $costPerUnit * $quantityProduced;
            $totalProfitAmount = $profitAmountPerUnit * $quantityProduced;

            $order->update(array_merge($data, [
                'cost_per_unit' => $costPerUnit,
                'total_cost' => $totalCost,
                'selling_price_per_unit' => $sellingPricePerUnit,
                'profit_margin' => $profitMargin,
                'profit_amount' => $totalProfitAmount,
                'labor_cost' => $laborCost,
                'updated_by' => $userId,
            ]));

            // Update extra costs if provided
            if (isset($data['extra_costs'])) {
                $order->extraCosts()->delete();
                foreach ($data['extra_costs'] as $extra) {
                    $order->extraCosts()->create([
                        'cost_type' => $extra['cost_type'],
                        'amount' => $extra['amount'],
                        'description' => $extra['description'] ?? null,
                    ]);
                }
            }

            // Update components if provided
            if (isset($data['components'])) {
                $order->components()->delete();
                foreach ($data['components'] as $componentData) {
                    $this->addComponent($order, $componentData);
                }
            }

            Log::info('Manufacturing order updated', ['order_number' => $order->order_number]);

            return $order->fresh(['components', 'product', 'extraCosts']);
        });
    }

    /**
     * Add a component to a manufacturing order
     */
    public function addComponent(ManufacturingOrder $order, array $data): ManufacturingOrderComponent
    {
        $quantity = (float) ($data['quantity'] ?? 0);
        $unitCost = (float) ($data['unit_cost'] ?? $data['cost_per_uom'] ?? 0);
        
        if ($unitCost <= 0 && !empty($data['material_batch_id'])) {
            $batch = MaterialBatch::find($data['material_batch_id']);
            $unitCost = $batch ? (float) $batch->unit_cost : 0;
        }

        $totalCost = $quantity * $unitCost;

        return ManufacturingOrderComponent::create([
            'order_id' => $order->id,
            'material_batch_id' => !empty($data['material_batch_id']) ? (int) $data['material_batch_id'] : null,
            'component_name' => $data['component_name'] ?? $data['component_type'] ?? 'مكون عام',
            'component_type' => $data['component_type'] ?? 'general',
            'quantity' => $quantity,
            'uom_id' => $data['uom_id'] ?? null,
            'unit_cost' => $unitCost,
            'total_cost' => $totalCost,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Confirm a manufacturing order
     */
    public function confirmOrder(ManufacturingOrder $order): ManufacturingOrder
    {
        \App\Models\AccountingSetting::checkStrictPostingLimit();

        if (!$order->canBeConfirmed()) {
            throw new Exception('Order cannot be confirmed. Check status and components.');
        }

        return DB::transaction(function () use ($order) {
            $order->load(['components.batch']);

            // Validate and dispense stock
            foreach ($order->components as $component) {
                if ($component->material_batch_id && $component->batch) {
                    $qtyRequired = (float) $component->quantity * (float) $order->quantity_produced;

                    if ($component->batch->remaining_qty < $qtyRequired) {
                        throw new Exception("الكمية المطلوبة للمكوّن '{$component->component_name}' تتجاوز الرصيد المتبقي في دفعة المواد.");
                    }

                    // Dispense stock
                    $this->materialStockService->dispense($component->batch, [
                        'quantity_taken' => $qtyRequired,
                        'manufacturing_order_id' => $order->id,
                        'notes' => 'حجز مواد لتأكيد أمر التصنيع ' . $order->order_number,
                        'dispensed_at' => now()->toDateString(),
                    ]);
                } else {
                    $rawMaterial = RawMaterialTemplate::where('name', $component->component_type ?? '')->first();
                    if ($rawMaterial) {
                        $requiredQty = (float) $component->quantity * (float) $order->quantity_produced;
                        if ($rawMaterial->quantity < $requiredQty) {
                            throw new Exception("الكمية المطلوبة للمادة الخام '{$component->component_type}' غير متوفرة.");
                        }
                        $rawMaterial->decrement('quantity', $requiredQty);
                    }
                }
            }

            $order->update([
                'status' => 'confirmed',
                'updated_by' => Auth::id(),
            ]);

            Log::info('Manufacturing order confirmed and stock reserved', ['order_number' => $order->order_number]);

            // GL: ترحيل سحب المواد الخام إلى WIP
            try {
                app(\App\Services\Accounting\PostingService::class)->postManufacturingConfirm($order);
            } catch (\Throwable $e) {
                Log::warning("[Manufacturing] GL posting failed for confirm: " . $e->getMessage());
            }

            return $order->fresh();
        });
    }

    /**
     * Complete a manufacturing order
     */
    public function completeOrder(ManufacturingOrder $order, int $warehouseId, ?int $productId = null): ManufacturingOrder
    {
        \App\Models\AccountingSetting::checkStrictPostingLimit();

        if (!$order->canBeCompleted()) {
            throw new Exception('Order cannot be completed. Must be confirmed first.');
        }

        return DB::transaction(function () use ($order, $warehouseId) {
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
            if (!$existing) {
                $existing = Product::where('name', $productData['name'])->first();
            }

            if ($existing) {
                $productData['name'] = $existing->name;
                $productData['sku'] = $existing->sku;
                $productData['category'] = $existing->category ?: $productData['category'];
                $productData['base_unit'] = $existing->base_unit ?: $productData['base_unit'];
                $productData['product_type'] = 'manufactured';
                $productData['is_manufactured'] = true;
                $productData['price_change_reason'] = 'إكمال أمر تصنيع ' . $order->order_number;

                $product = $this->productService->updateProduct($existing, $productData);
                $this->productService->ensureProductWarehousePivot($product, $warehouseId, 10);
            } else {
                $product = $this->productService->createProduct($productData);
            }

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
            ]);

            // ── Gap 2: Standard Costing branch ─────────────────────────
            // Either the legacy 2-line WIP→FG entry OR the new 3-line
            // variance-aware entry runs — never both. The variance path
            // internally falls back to the legacy entry when there is no
            // standard cost defined, so behavior is deterministic.
            $standardCostingEnabled = $this->standardCostingService->isEnabled();

            if ($standardCostingEnabled) {
                try {
                    $variance = $this->standardCostingService->calculateVariance($order);

                    $this->standardCostingService->persistVarianceSnapshot($order, $variance);

                    $varianceEntry = $this->costVariancePostingService
                        ->postManufacturingCompleteWithVariance($order, $variance);

                    Log::info('[Manufacturing] Variance posting attempted', [
                        'order'         => $order->order_number,
                        'has_variance'  => $variance['has_variance'] ?? false,
                        'variance'      => $variance['total_variance'] ?? 0,
                        'variance_type' => $variance['variance_type'] ?? 'none',
                        'entry_id'      => $varianceEntry?->id,
                    ]);
                } catch (\Throwable $e) {
                    // Variance is best-effort — never block order completion.
                    Log::warning('[Manufacturing] Standard Costing branch failed: ' . $e->getMessage(), [
                        'order' => $order->order_number,
                    ]);
                }
            } else {
                // GL: ترحيل WIP -> مخزون منتج تام (السلوك الأصلي، لم يتغيّر)
                try {
                    app(\App\Services\Accounting\PostingService::class)->postManufacturingComplete($order);
                } catch (\Throwable $e) {
                    Log::warning("[Manufacturing] GL posting failed for complete: " . $e->getMessage());
                }
            }

            // Gap 4 — Batch Genealogy recording. Best-effort: any failure
            // here must NEVER block order completion (which has already
            // posted to GL by this point). Logs warn on failure.
            try {
                $finishedBatch = $this->batchGenealogyService
                    ->recordGenealogyOnCompletion($order->fresh());

                if ($finishedBatch) {
                    Log::info('[Manufacturing] Batch genealogy recorded', [
                        'order'    => $order->order_number,
                        'fg_batch' => $finishedBatch->batch_code,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('[Manufacturing] Batch genealogy recording failed: ' . $e->getMessage(), [
                    'order' => $order->order_number,
                ]);
            }

            return $order->fresh(['components', 'product', 'inventoryMovements'])
                ->loadMissing(['varianceJournalEntry']);
        });
    }

    /**
     * Cancel a manufacturing order
     */
    public function cancelOrder(ManufacturingOrder $order, ?string $reason = null): ManufacturingOrder
    {
        if (!in_array($order->status, ['draft', 'confirmed'])) {
            throw new Exception('Cannot cancel an order that is ' . $order->status);
        }

        return DB::transaction(function () use ($order, $reason) {
            $oldStatus = $order->status;

            $order->update([
                'status' => 'cancelled',
                'notes' => trim(($order->notes ?? '') . "\n\nCancelled: " . $reason),
                'updated_by' => Auth::id(),
            ]);

            if ($oldStatus === 'confirmed') {
                $order->load(['components.batch']);

                foreach ($order->components as $component) {
                    if ($component->material_batch_id) {
                        $dispensings = MaterialDispensing::where('manufacturing_order_id', $order->id)
                            ->where('material_batch_id', $component->material_batch_id)
                            ->get();

                        foreach ($dispensings as $dispensing) {
                            // Settle back remaining quantity
                            $dispensing->batch->increment('remaining_qty', $dispensing->quantity_taken);

                            $this->inventoryService->recordMovement([
                                'warehouse_id' => $dispensing->batch->warehouse_id,
                                'product_id' => $dispensing->batch->product_id,
                                'movement_type' => 'material_in',
                                'quantity_change' => $dispensing->quantity_taken,
                                'unit_cost' => $dispensing->batch->unit_cost,
                                'unit_price' => $dispensing->batch->unit_cost,
                                'reference_type' => MaterialDispensing::class,
                                'reference_id' => $dispensing->id,
                                'notes' => 'إلغاء صرف لتأكيد أمر التصنيع ' . $order->order_number,
                                'created_by' => auth()->id(),
                            ]);

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

            event(new \App\Events\Manufacturing\ManufacturingOrderCancelled($order, $reason));

            return $order->fresh();
        });
    }

    /**
     * Delete a manufacturing order
     */
    public function deleteOrder(ManufacturingOrder $order): void
    {
        if (!$order->is_draft) {
            throw new Exception('Cannot delete an order that is not in draft status');
        }

        DB::transaction(function () use ($order) {
            $order->extraCosts()->delete();
            $order->components()->delete();
            $order->delete();

            Log::info('Manufacturing order deleted', ['order_number' => $order->order_number]);
        });
    }

    /**
     * Calculate costs
     */
    public function calculateCosts(array $components): array
    {
        $totalCost = 0;
        foreach ($components as $component) {
            $qty = (float) ($component['quantity'] ?? 0);
            $unitCost = (float) ($component['unit_cost'] ?? $component['cost_per_uom'] ?? 0);
            $totalCost += $qty * $unitCost;
        }

        return [
            'components_total_cost' => round($totalCost, 4),
        ];
    }

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
