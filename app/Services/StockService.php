<?php

namespace App\Services;

use App\Models\ProductWarehouse;
use App\Models\InventoryMovement;
use App\Exceptions\StockAdjustmentException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StockService
{
    // constants for allowed type values
    public const SALE = 'SALE';
    public const PURCHASE = 'PURCHASE';
    public const TRANSFER_IN = 'TRANSFER_IN';
    public const TRANSFER_OUT = 'TRANSFER_OUT';
    public const MANUFACTURING_IN = 'MANUFACTURING_IN';
    public const MANUFACTURING_OUT = 'MANUFACTURING_OUT';
    public const RETURN_IN = 'RETURN_IN';
    public const ADJUSTMENT = 'ADJUSTMENT';
    public const STOCK_COUNT = 'STOCK_COUNT';
    public const INBOUND = 'INBOUND';
    public const OUTBOUND = 'OUTBOUND';
    public const PURCHASE_RETURN = 'PURCHASE_RETURN';




    /**
     * Adjust stock for a product in a warehouse.
     *
     * @param int $warehouseId
     * @param int $productId
     * @param float $qty
     * @param string $type
     * @param int $referenceId
     * @param float|null $unitCost
     * @return void
     * @throws StockAdjustmentException
     */
    public function adjust(
        int $warehouseId,
        int $productId,
        float $qty,
        string $type,
        int $referenceId,
        ?float $unitCost = null
    ): void {
        $allowedTypes = [
            self::SALE,
            self::PURCHASE,
            self::TRANSFER_IN,
            self::TRANSFER_OUT,
            self::MANUFACTURING_IN,
            self::MANUFACTURING_OUT,
            self::RETURN_IN,
            self::ADJUSTMENT,
            self::STOCK_COUNT,
            self::INBOUND,
            self::OUTBOUND,
            self::PURCHASE_RETURN,
        ];

        if (!in_array($type, $allowedTypes)) {
            throw new StockAdjustmentException("Invalid stock adjustment type: {$type}");
        }

        try {
            DB::transaction(function () use ($warehouseId, $productId, $qty, $type, $referenceId, $unitCost) {
                // 1. Get or create product_warehouse record
                $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouseId)
                    ->where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();

                if (!$productWarehouse) {
                    $productWarehouse = ProductWarehouse::create([
                        'warehouse_id' => $warehouseId,
                        'product_id' => $productId,
                        'quantity' => 0.0,
                        'reserved_quantity' => 0.0,
                        'average_cost' => 0.0,
                        'min_stock' => 0.0,
                    ]);

                    // Lock the newly created record
                    $productWarehouse = ProductWarehouse::where('warehouse_id', $warehouseId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();
                }

                $currentQuantity = (float) $productWarehouse->quantity;
                $currentReserved = (float) $productWarehouse->reserved_quantity;
                $currentAverageCost = (float) $productWarehouse->average_cost;

                // 2. Update quantity dynamically (qty can be positive or negative)
                $newQuantity = $currentQuantity + $qty;

                // 3. Recalculate available_quantity
                $newAvailableQuantity = $newQuantity - $currentReserved;

                // 4. Update average_cost using moving average if unitCost is present
                $newAverageCost = $currentAverageCost;
                if ($unitCost !== null && $type !== self::PURCHASE_RETURN) {
                    if ($unitCost < 0) {
                        throw new \InvalidArgumentException("Unit cost cannot be negative: {$unitCost}");
                    }

                    $qtyForFormula = (float) $qty;
                    $currentQtyForFormula = max(0.0, (float) $currentQuantity);
                    $newQtyForFormula = $currentQtyForFormula + $qtyForFormula;

                    if ($newQtyForFormula > 0) {
                        $newAverageCost = (($currentQtyForFormula * $currentAverageCost) + ($qtyForFormula * $unitCost)) / $newQtyForFormula;
                        $newAverageCost = round($newAverageCost, 2);
                    } else {
                        $newAverageCost = $unitCost;
                    }
                }

                // Check negative stock condition
                if ($qty < 0) {
                    $currentAvailable = $currentQuantity - $currentReserved;
                    
                    // Fetch PosSetting for the current warehouse
                    $posSetting = \App\Models\PosSetting::where('default_warehouse_id', $warehouseId)->first()
                        ?? \App\Models\PosSetting::first()
                        ?? \App\Models\PosSetting::getSolo();

                    if (!$posSetting->allow_negative_stock) {
                        if ($currentAvailable + $qty < 0) {
                            throw new \App\Exceptions\InsufficientStockException(
                                "الكمية المتاحة {$currentAvailable} أقل من المطلوب"
                            );
                        }
                    } else {
                        if ($currentAvailable + $qty < 0) {
                            \Illuminate\Support\Facades\Log::warning("Negative stock warning: Warehouse {$warehouseId}, Product {$productId}. Available {$currentAvailable}, requested change {$qty}. Negative stock allowed by POS settings.");
                        }
                    }
                }

                // Update product_warehouse record
                $productWarehouse->update([
                    'quantity'           => $newQuantity,
                    'average_cost'       => $newAverageCost,
                ]);

                // Map type to database movement_type enum value
                $dbType = match ($type) {
                    self::SALE => 'sale',
                    self::PURCHASE => 'purchase',
                    self::TRANSFER_IN => 'transfer_in',
                    self::TRANSFER_OUT => 'transfer_out',
                    self::MANUFACTURING_IN => 'production',
                    self::MANUFACTURING_OUT => 'consumption',
                    self::RETURN_IN => 'return_in',
                    self::ADJUSTMENT => 'adjustment',
                    self::STOCK_COUNT => 'adjustment',
                    self::INBOUND => 'adjustment_in',
                    self::OUTBOUND => 'adjustment_out',
                    self::PURCHASE_RETURN => 'return_out',
                    default => strtolower($type),
                };

                // Map polymorphic reference_type
                $referenceType = match ($type) {
                    self::SALE => \App\Models\SalesInvoice::class,
                    self::PURCHASE => \App\Models\PurchaseInvoice::class,
                    self::TRANSFER_IN, self::TRANSFER_OUT => \App\Models\WarehouseTransfer::class,
                    self::MANUFACTURING_IN, self::MANUFACTURING_OUT => \App\Models\ManufacturingOrder::class,
                    self::RETURN_IN => \App\Models\SalesReturn::class,
                    self::ADJUSTMENT => \App\Models\StockAdjustment::class,
                    self::STOCK_COUNT => \App\Models\StockCount::class,
                    self::INBOUND => \App\Models\WarehouseInboundOrder::class,
                    self::OUTBOUND => \App\Models\WarehouseOutboundOrder::class,
                    self::PURCHASE_RETURN => \App\Models\PurchaseReturn::class,
                    default => null,
                };

                // Generate a unique movement number
                $prefix = match ($dbType) {
                    'purchase' => 'PUR',
                    'sale' => 'SAL',
                    'return_in' => 'RIN',
                    'return_out' => 'ROUT',
                    'transfer_in' => 'TIN',
                    'transfer_out' => 'TOUT',
                    'adjustment' => 'ADJ',
                    'adjustment_in' => 'ADJ',
                    'adjustment_out' => 'ADJ',
                    'production' => 'PRD',
                    'consumption' => 'CON',
                    default => 'MOV',
                };
                $movementNumber = sprintf(
                    '%s-%s-%s-%s',
                    $prefix,
                    now()->format('Ymd'),
                    now()->format('His'),
                    str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT)
                );

                // Build movement record
                $movementData = [
                    'movement_number'  => $movementNumber,
                    'warehouse_id'     => $warehouseId,
                    'product_id'       => $productId,
                    'movement_type'    => $dbType,
                    'quantity'         => abs($qty),
                    'quantity_change'  => $qty,
                    'quantity_before'  => $currentQuantity,
                    'quantity_after'   => $newQuantity,
                    'unit_cost'        => $unitCost ?? 0,
                    'unit_cost_snapshot' => $currentAverageCost,
                    'total_cost'       => ($unitCost ?? 0) * abs($qty),
                    'reference_type'   => $referenceType,
                    'reference_id'     => $referenceId,
                    'movement_date'    => now()->toDateString(),
                    'created_by'       => auth()->id(),
                ];

                // Link to specific invoice fields where schema supports it and record exists (prevents FK failures in tests)
                if ($type === self::SALE && DB::table('sales_invoices')->where('id', $referenceId)->exists()) {
                    $movementData['sales_invoice_id'] = $referenceId;
                } elseif ($type === self::PURCHASE && DB::table('purchase_invoices')->where('id', $referenceId)->exists()) {
                    $movementData['purchase_invoice_id'] = $referenceId;
                } elseif (in_array($type, [self::TRANSFER_IN, self::TRANSFER_OUT]) && DB::table('warehouse_transfers')->where('id', $referenceId)->exists()) {
                    $movementData['transfer_id'] = $referenceId;
                }

                // 5. Register in inventory_movements
                InventoryMovement::create($movementData);

                // Clear caches for this warehouse
                Cache::forget("warehouse_stock_{$warehouseId}");
                Cache::forget("warehouse_stats_{$warehouseId}");
                Cache::forget("warehouse_data_{$warehouseId}");
                Cache::forget("warehouse_details_{$warehouseId}");
                Cache::forget("warehouse_details_v2_{$warehouseId}");
                Cache::forget("warehouse_products_stock_{$warehouseId}");
            });
        } catch (\App\Exceptions\InsufficientStockException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new StockAdjustmentException($e->getMessage(), 0, $e);
        }
    }
}
