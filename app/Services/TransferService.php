<?php

namespace App\Services;

use App\Models\ProductWarehouse;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use App\Models\InventoryMovement;
use App\Traits\TransferValidationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class TransferService
{
    use TransferValidationTrait;

    private const CHUNK_SIZE        = 500;
    private const BATCH_INSERT_SIZE = 1000;
    private const CACHE_TTL         = 300;

    public function __construct(
        private InventoryMovementService $movementService
    ) {}

    /**
     * جلب كل التحويلات مع الفلاتر
     */
    public function getAllTransfers(array $filters = [])
    {
        try {
            $query = WarehouseTransfer::query();

            if (!empty($filters['from_warehouse'])) {
                $query->where('from_warehouse_id', $filters['from_warehouse']);
            }
            if (!empty($filters['to_warehouse'])) {
                $query->where('to_warehouse_id', $filters['to_warehouse']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }
            if (!empty($filters['date_from'])) {
                $query->whereDate('transfer_date', '>=', $filters['date_from']);
            }
            if (!empty($filters['date_to'])) {
                $query->whereDate('transfer_date', '<=', $filters['date_to']);
            }
            if (!empty($filters['search'])) {
                $query->where('transfer_number', 'like', '%' . $filters['search'] . '%');
            }

            $query->with([
                'fromWarehouse:id,name,code',
                'toWarehouse:id,name,code',
                'items' => function ($q) {
                    $q->select('id', 'warehouse_transfer_id', 'product_id', 'quantity_sent', 'quantity_received', 'notes')
                      ->with('product:id,name,code,sku,purchase_price');
                },
                'createdBy:id,name',
            ]);

            $query->withCount('items as total_items')
                ->addSelect([
                    'total_quantity_sent' => WarehouseTransferItem::selectRaw('COALESCE(SUM(quantity_sent), 0)')
                        ->whereColumn('warehouse_transfer_id', 'warehouse_transfers.id'),
                    'total_quantity_received' => WarehouseTransferItem::selectRaw('COALESCE(SUM(quantity_received), 0)')
                        ->whereColumn('warehouse_transfer_id', 'warehouse_transfers.id'),
                ]);

            return $query->latest('transfer_date')
                ->latest('id')
                ->paginate($filters['per_page'] ?? 20)
                ->withQueryString();

        } catch (Exception $e) {
            Log::error('❌ getAllTransfers failed', [
                'error'   => $e->getMessage(),
                'filters' => $filters,
            ]);

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1);
        }
    }

    /**
     * إنشاء وتنفيذ التحويل
     */
    public function createTransfer(array $data): WarehouseTransfer
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new Exception('❌ لا توجد منتجات للتحويل');
        }

        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            throw new Exception('❌ لا يمكن التحويل من نفس المخزن إلى نفسه');
        }

        return DB::transaction(function () use ($data) {
            try {
                $this->validateTransfer($data);

                Log::info('🔄 بدء إنشاء تحويل', [
                    'from'        => $data['from_warehouse_id'],
                    'to'          => $data['to_warehouse_id'],
                    'items_count' => count($data['items']),
                ]);

                $this->validateStockAvailability($data);

                $transfer = WarehouseTransfer::create([
                    'from_warehouse_id' => $data['from_warehouse_id'],
                    'to_warehouse_id'   => $data['to_warehouse_id'],
                    'transfer_number'   => $this->generateTransferNumber(),
                    'transfer_date'     => $data['transfer_date'] ?? now(),
                    'expected_date'     => $data['expected_date'] ?? null,
                    'status'            => 'draft',
                    'notes'             => $data['notes'] ?? null,
                    'created_by'        => auth()->id(),
                ]);

                $itemsData = [];
                $now       = now();

                foreach ($data['items'] as $item) {
                    if (empty($item['product_id']) || empty($item['quantity'])) {
                        throw new Exception('❌ بيانات المنتج غير صحيحة');
                    }
                    if ($item['quantity'] <= 0) {
                        throw new Exception('❌ الكمية يجب أن تكون أكبر من صفر');
                    }

                    $itemsData[] = [
                        'warehouse_transfer_id' => $transfer->id,
                        'product_id'            => $item['product_id'],
                        'quantity_sent'         => floatval($item['quantity']),
                        'quantity_received'     => floatval($item['quantity']),
                        'notes'                 => $item['notes'] ?? null,
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ];
                }

                if (!empty($itemsData)) {
                    collect($itemsData)->chunk(self::BATCH_INSERT_SIZE)->each(function ($chunk) {
                        WarehouseTransferItem::insert($chunk->toArray());
                    });
                } else {
                    throw new Exception('❌ لا توجد منتجات صالحة للتحويل');
                }

                $this->executeTransfer($transfer);

                $transfer->update([
                    'status'        => 'received',
                    'received_date' => now(),
                    'confirmed_at'  => now(),
                    'received_by'   => auth()->id(),
                    'confirmed_by'  => auth()->id(),
                ]);

                $this->clearWarehousesCache([
                    $data['from_warehouse_id'],
                    $data['to_warehouse_id'],
                ]);

                Log::info('✅ تم التحويل بنجاح', [
                    'transfer_id'     => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                ]);

                return $transfer->fresh(['items.product', 'fromWarehouse', 'toWarehouse']);

            } catch (Exception $e) {
                Log::error('❌ فشل إنشاء التحويل', ['error' => $e->getMessage(), 'data' => $data]);
                throw $e;
            }
        });
    }

    /**
     * التحقق من توفر المخزون
     */
    private function validateStockAvailability(array $data): void
    {
        $fromWarehouseId = $data['from_warehouse_id'];
        $errors          = [];

        foreach ($data['items'] as $item) {
            $productId    = $item['product_id'];
            $requestedQty = floatval($item['quantity']);

            $stock = ProductWarehouse::where('warehouse_id', $fromWarehouseId)
                ->where('product_id', $productId)
                ->first();

            if (!$stock) {
                $errors[] = "المنتج #{$productId} غير موجود في المخزن المصدر";
                continue;
            }

            if (floatval($stock->quantity) < $requestedQty) {
                $errors[] = "المنتج #{$productId}: المخزون غير كافي (متوفر: {$stock->quantity}, مطلوب: {$requestedQty})";
            }
        }

        if (!empty($errors)) {
            throw new Exception("❌ أخطاء في المخزون:\n" . implode("\n", $errors));
        }
    }

    /**
     * تنفيذ التحويل الفعلي
     */
    private function executeTransfer(WarehouseTransfer $transfer): void
    {
        $itemsCount   = 0;
        $successCount = 0;

        $transfer->items()
            ->with('product:id,name,code,sku,purchase_price')
            ->chunk(self::CHUNK_SIZE, function ($items) use ($transfer, &$itemsCount, &$successCount) {
                foreach ($items as $item) {
                    $itemsCount++;
                    $this->transferSingleProduct($transfer, $item);
                    $successCount++;
                }
            });

        if ($successCount !== $itemsCount) {
            throw new Exception("❌ فشل نقل بعض المنتجات: {$successCount}/{$itemsCount}");
        }
    }

    /**
     * نقل منتج واحد
     */
    private function transferSingleProduct(WarehouseTransfer $transfer, WarehouseTransferItem $item): void
    {
        $productId = $item->product_id;
        $quantity  = floatval($item->quantity_sent);

        if ($quantity <= 0) {
            throw new Exception("❌ الكمية يجب أن تكون أكبر من صفر");
        }

        DB::beginTransaction();

        try {
            $sourceStock = ProductWarehouse::where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$sourceStock) {
                throw new Exception("❌ المنتج #{$productId} غير موجود في المخزن المصدر");
            }

            if (floatval($sourceStock->quantity) < $quantity) {
                throw new Exception(
                    "❌ المخزون غير كافي للمنتج #{$productId} - " .
                    "متوفر: " . number_format(floatval($sourceStock->quantity), 2) . ", " .
                    "مطلوب: " . number_format($quantity, 2)
                );
            }

            $sourceQuantityBefore = floatval($sourceStock->quantity);

            $destinationStock   = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $destQuantityBefore = $destinationStock ? floatval($destinationStock->quantity) : 0;

            $sourceStock->quantity = floatval($sourceStock->quantity) - $quantity;
            if ($sourceStock->quantity < 0) {
                throw new Exception("❌ خطأ في الحساب: الكمية الناتجة سالبة");
            }
            $sourceStock->save();
            $sourceStock->refresh();
            $sourceQuantityAfter = floatval($sourceStock->quantity);

            if ($destinationStock) {
                $destinationStock->quantity = floatval($destinationStock->quantity) + $quantity;
                $destinationStock->save();
                $destinationStock->refresh();
                $destQuantityAfter = floatval($destinationStock->quantity);
            } else {
                $destinationStock = ProductWarehouse::create([
                    'warehouse_id'  => $transfer->to_warehouse_id,
                    'product_id'    => $productId,
                    'quantity'      => $quantity,
                    'min_stock'     => 0,
                    'max_stock'     => 0,
                    'reorder_level' => 0,
                ]);
                $destinationStock->refresh();
                $destQuantityAfter = floatval($quantity);
            }

            $purchasePrice = $item->product ? floatval($item->product->purchase_price ?? 0) : 0;

            if (abs($sourceQuantityAfter - ($sourceQuantityBefore - $quantity)) > 0.001) {
                throw new Exception("❌ خطأ في حساب المخزن المصدر");
            }
            if (abs($destQuantityAfter - ($destQuantityBefore + $quantity)) > 0.001) {
                throw new Exception("❌ خطأ في حساب المخزن الوجهة");
            }

            $this->recordStockMovements(
                $transfer, $productId, $quantity,
                $sourceQuantityBefore, $sourceQuantityAfter,
                $destQuantityBefore, $destQuantityAfter,
                $purchasePrice
            );

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ فشل نقل منتج واحد', ['product_id' => $productId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * تسجيل حركات المخزون
     */
    private function recordStockMovements(
        WarehouseTransfer $transfer,
        int $productId,
        float $quantity,
        float $sourceQuantityBefore,
        float $sourceQuantityAfter,
        float $destQuantityBefore,
        float $destQuantityAfter,
        float $purchasePrice
    ): void {
        $timestamp = now()->format('YmdHis');
        $uniqueId  = $timestamp . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $totalCost = $quantity * $purchasePrice;

        try {
            InventoryMovement::create([
                'warehouse_id'    => $transfer->from_warehouse_id,
                'product_id'      => $productId,
                'movement_type'   => 'transfer_out',
                'quantity'        => $quantity,
                'quantity_change' => -$quantity,
                'quantity_before' => $sourceQuantityBefore,
                'quantity_after'  => $sourceQuantityAfter,
                'unit_cost'       => $purchasePrice,
                'unit_price'      => 0,
                'total_cost'      => $totalCost,
                'total_price'     => 0,
                'movement_date'   => $transfer->transfer_date,
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "تحويل صادر #{$transfer->transfer_number} إلى {$transfer->toWarehouse->name}",
                'movement_number' => "TOUT-{$uniqueId}",
                'created_by'      => null,
            ]);

            InventoryMovement::create([
                'warehouse_id'    => $transfer->to_warehouse_id,
                'product_id'      => $productId,
                'movement_type'   => 'transfer_in',
                'quantity'        => $quantity,
                'quantity_change' => $quantity,
                'quantity_before' => $destQuantityBefore,
                'quantity_after'  => $destQuantityAfter,
                'unit_cost'       => $purchasePrice,
                'unit_price'      => 0,
                'total_cost'      => $totalCost,
                'total_price'     => 0,
                'movement_date'   => $transfer->transfer_date,
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "تحويل وارد #{$transfer->transfer_number} من {$transfer->fromWarehouse->name}",
                'movement_number' => "TIN-{$uniqueId}",
                'created_by'      => null,
            ]);

        } catch (Exception $e) {
            throw new Exception("❌ فشل تسجيل حركات المخزون: " . $e->getMessage());
        }
    }

    /**
     * عكس التحويل
     */
    public function reverseTransfer(int $transferId): WarehouseTransfer
    {
        return DB::transaction(function () use ($transferId) {
            try {
                $transfer = WarehouseTransfer::with(['items.product', 'fromWarehouse', 'toWarehouse'])
                    ->lockForUpdate()
                    ->findOrFail($transferId);

                $this->validateReversal($transfer);

                foreach ($transfer->items as $item) {
                    $this->reverseSingleProduct($transfer, $item);
                }

                $transfer->update([
                    'status'      => 'reversed',
                    'reversed_at' => now(),
                    'reversed_by' => null,
                ]);

                $this->clearWarehousesCache([
                    $transfer->from_warehouse_id,
                    $transfer->to_warehouse_id,
                ]);

                Log::info('✅ تم عكس التحويل بنجاح', ['transfer_id' => $transferId]);

                return $transfer->fresh();

            } catch (Exception $e) {
                Log::error('❌ فشل عكس التحويل', ['transfer_id' => $transferId, 'error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * عكس منتج واحد
     */
    private function reverseSingleProduct(WarehouseTransfer $transfer, WarehouseTransferItem $item): void
    {
        $productId = $item->product_id;
        $quantity  = floatval($item->quantity_sent);

        if ($quantity <= 0) {
            throw new Exception("❌ الكمية يجب أن تكون أكبر من صفر");
        }

        try {
            $sourceStock = ProductWarehouse::where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $destStock = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$destStock) {
                throw new Exception("❌ المنتج غير موجود في المخزن الوجهة");
            }

            if (floatval($destStock->quantity) < $quantity) {
                throw new Exception(
                    "❌ لا يمكن عكس التحويل - مخزون غير كافي في المخزن الوجهة - " .
                    "متوفر: " . number_format(floatval($destStock->quantity), 2) . ", " .
                    "مطلوب: " . number_format($quantity, 2)
                );
            }

            $sourceQuantityBefore = $sourceStock ? floatval($sourceStock->quantity) : 0;
            $destQuantityBefore   = floatval($destStock->quantity);

            if (!$sourceStock) {
                $sourceStock = ProductWarehouse::create([
                    'warehouse_id'  => $transfer->from_warehouse_id,
                    'product_id'    => $productId,
                    'quantity'      => 0,
                    'min_stock'     => 0,
                    'max_stock'     => 0,
                    'reorder_level' => 0,
                ]);
                $sourceQuantityBefore = 0;
            }

            $sourceStock->quantity = floatval($sourceStock->quantity) + $quantity;
            $sourceStock->save();
            $sourceStock->refresh();

            $destStock->quantity = floatval($destStock->quantity) - $quantity;
            if ($destStock->quantity < 0) {
                throw new Exception("❌ خطأ في الحساب: الكمية الناتجة سالبة");
            }
            $destStock->save();
            $destStock->refresh();

            $sourceQuantityAfter = floatval($sourceStock->quantity);
            $destQuantityAfter   = floatval($destStock->quantity);

            $purchasePrice = $item->product ? floatval($item->product->purchase_price ?? 0) : 0;

            if (abs($sourceQuantityAfter - ($sourceQuantityBefore + $quantity)) > 0.001) {
                throw new Exception("❌ خطأ في حساب المخزن المصدر عند العكس");
            }
            if (abs($destQuantityAfter - ($destQuantityBefore - $quantity)) > 0.001) {
                throw new Exception("❌ خطأ في حساب المخزن الوجهة عند العكس");
            }

            $this->recordReversalMovements(
                $transfer, $productId, $quantity,
                $sourceQuantityBefore, $sourceQuantityAfter,
                $destQuantityBefore, $destQuantityAfter,
                $purchasePrice
            );

        } catch (Exception $e) {
            Log::error('❌ فشل عكس منتج واحد', ['product_id' => $productId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * تسجيل حركات العكس
     */
    private function recordReversalMovements(
        WarehouseTransfer $transfer,
        int $productId,
        float $quantity,
        float $sourceQuantityBefore,
        float $sourceQuantityAfter,
        float $destQuantityBefore,
        float $destQuantityAfter,
        float $purchasePrice
    ): void {
        $timestamp = now()->format('YmdHis');
        $uniqueId  = $timestamp . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $totalCost = $quantity * $purchasePrice;

        try {
            InventoryMovement::create([
                'warehouse_id'    => $transfer->from_warehouse_id,
                'product_id'      => $productId,
                'movement_type'   => 'return_from_transfer',
                'quantity'        => $quantity,
                'quantity_change' => $quantity,
                'quantity_before' => $sourceQuantityBefore,
                'quantity_after'  => $sourceQuantityAfter,
                'unit_cost'       => $purchasePrice,
                'unit_price'      => 0,
                'total_cost'      => $totalCost,
                'total_price'     => 0,
                'movement_date'   => now()->toDateString(),
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "عكس تحويل #{$transfer->transfer_number}",
                'movement_number' => "RTF-{$uniqueId}",
                'created_by'      => null,
            ]);

            InventoryMovement::create([
                'warehouse_id'    => $transfer->to_warehouse_id,
                'product_id'      => $productId,
                'movement_type'   => 'transfer_reversed',
                'quantity'        => $quantity,
                'quantity_change' => -$quantity,
                'quantity_before' => $destQuantityBefore,
                'quantity_after'  => $destQuantityAfter,
                'unit_cost'       => $purchasePrice,
                'unit_price'      => 0,
                'total_cost'      => $totalCost,
                'total_price'     => 0,
                'movement_date'   => now()->toDateString(),
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "عكس تحويل #{$transfer->transfer_number}",
                'movement_number' => "TRV-{$uniqueId}",
                'created_by'      => null,
            ]);

        } catch (Exception $e) {
            throw new Exception("❌ فشل تسجيل حركات العكس: " . $e->getMessage());
        }
    }

    /**
     * إلغاء التحويل
     */
    public function cancelTransfer(int $transferId): WarehouseTransfer
    {
        return DB::transaction(function () use ($transferId) {
            try {
                $transfer = WarehouseTransfer::lockForUpdate()->findOrFail($transferId);
                $this->validateCancellation($transfer);

                if ($transfer->status === 'received') {
                    return $this->reverseTransfer($transferId);
                }

                $transfer->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                ]);

                $this->clearWarehousesCache([
                    $transfer->from_warehouse_id,
                    $transfer->to_warehouse_id,
                ]);

                Log::info('✅ تم إلغاء التحويل', ['transfer_id' => $transferId]);

                return $transfer->fresh();

            } catch (Exception $e) {
                Log::error('❌ فشل إلغاء التحويل', ['transfer_id' => $transferId, 'error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * التحويلات المعلقة
     */
    public function getPendingTransfers()
    {
        try {
            return WarehouseTransfer::whereIn('status', ['draft', 'pending', 'in_transit'])
                ->with(['fromWarehouse:id,name,code', 'toWarehouse:id,name,code'])
                ->withCount('items')
                ->latest('transfer_date')
                ->latest('id')
                ->get();

        } catch (Exception $e) {
            Log::error('❌ فشل جلب التحويلات المعلقة', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    /**
     * ✅ تفاصيل التحويل - مع حساب كميات قبل/بعد لكل منتج
     */
    public function getTransferDetails(int $transferId): WarehouseTransfer
    {
        try {
            $transfer = WarehouseTransfer::with([
                'items.product:id,name,code,sku,unit,purchase_price,selling_price',
                'fromWarehouse:id,name,code,location',
                'toWarehouse:id,name,code,location',
                'createdBy:id,name',
                'receivedBy:id,name',
                'confirmedBy:id,name',
                'reversedBy:id,name',
                'cancelledBy:id,name',
            ])->findOrFail($transferId);

            // ✅ حساب الكميات قبل وبعد لكل منتج بناءً على الحالة
            foreach ($transfer->items as $item) {
                $quantity = floatval($item->quantity_sent);

                // الكميات الحالية في المخازن
                $currentFromQty = floatval(
                    DB::table('product_warehouse')
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->value('quantity') ?? 0
                );

                $currentToQty = floatval(
                    DB::table('product_warehouse')
                        ->where('warehouse_id', $transfer->to_warehouse_id)
                        ->where('product_id', $item->product_id)
                        ->value('quantity') ?? 0
                );

                // حساب قبل/بعد حسب حالة التحويل
                switch ($transfer->status) {
                    case 'received':
                        // التحويل تم - المصدر نقص والوجهة زادت
                        $item->before_from_qty = $currentFromQty + $quantity;
                        $item->after_from_qty  = $currentFromQty;
                        $item->before_to_qty   = $currentToQty - $quantity;
                        $item->after_to_qty    = $currentToQty;
                        break;

                    case 'reversed':
                        // التحويل معكوس - المخزون رجع لحالته الأصلية
                        $item->before_from_qty = $currentFromQty - $quantity;
                        $item->after_from_qty  = $currentFromQty;
                        $item->before_to_qty   = $currentToQty + $quantity;
                        $item->after_to_qty    = $currentToQty;
                        break;

                    default:
                        // draft / pending / cancelled - لا تغيير
                        $item->before_from_qty = $currentFromQty;
                        $item->after_from_qty  = $currentFromQty;
                        $item->before_to_qty   = $currentToQty;
                        $item->after_to_qty    = $currentToQty;
                        break;
                }

                // ✅ الـ view بيستخدم $item->quantity - نضيفها كـ alias لـ quantity_sent
                $item->quantity = $quantity;
            }

            return $transfer;

        } catch (Exception $e) {
            Log::error('❌ فشل جلب تفاصيل التحويل', [
                'transfer_id' => $transferId,
                'error'       => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * توليد رقم تحويل فريد
     */
    private function generateTransferNumber(): string
    {
        $date   = now()->format('Ymd');
        $prefix = 'TR' . $date;

        $lastNumber = WarehouseTransfer::where('transfer_number', 'like', $prefix . '%')
            ->latest('id')
            ->lockForUpdate()
            ->value('transfer_number');

        $sequence = $lastNumber ? intval(substr($lastNumber, -4)) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * مسح كاش المخازن
     */
    private function clearWarehousesCache(array $warehouseIds): void
    {
        try {
            foreach ($warehouseIds as $id) {
                foreach ([
                    "warehouse_stock_{$id}",
                    "warehouse_stats_{$id}",
                    "warehouse_data_{$id}",
                    "warehouse_products_{$id}",
                    "warehouse_summary_{$id}",
                ] as $key) {
                    Cache::forget($key);
                }
            }
            Cache::forget('all_warehouses');
            Cache::forget('warehouses_list');

        } catch (Exception $e) {
            Log::warning('⚠️ فشل مسح الكاش', ['error' => $e->getMessage()]);
        }
    }

    /**
     * إحصائيات التحويل
     */
    public function getTransferStatistics(int $transferId): array
    {
        try {
            $transfer = WarehouseTransfer::with(['items.product:id,purchase_price'])
                ->findOrFail($transferId);

            $stats = $transfer->items()
                ->selectRaw('
                    COUNT(*) as total_items,
                    COALESCE(SUM(quantity_sent), 0) as total_quantity_sent,
                    COALESCE(SUM(quantity_received), 0) as total_quantity_received
                ')
                ->first();

            $totalValue = 0;
            $totalCost  = 0;

            foreach ($transfer->items as $item) {
                $purchasePrice = floatval($item->product->purchase_price ?? 0);
                $itemTotal     = floatval($item->quantity_sent) * $purchasePrice;
                $totalValue   += $itemTotal;
                $totalCost    += $itemTotal;
            }

            return [
                'total_items'             => intval($stats->total_items ?? 0),
                'total_quantity_sent'     => floatval($stats->total_quantity_sent ?? 0),
                'total_quantity_received' => floatval($stats->total_quantity_received ?? 0),
                'total_value'             => round($totalValue, 2),
                'total_cost'              => round($totalCost, 2),
                'status'                  => $transfer->status,
                'transfer_number'         => $transfer->transfer_number,
                'transfer_date'           => $transfer->transfer_date,
            ];

        } catch (Exception $e) {
            Log::error('❌ فشل حساب إحصائيات التحويل', ['transfer_id' => $transferId, 'error' => $e->getMessage()]);

            return [
                'total_items'             => 0,
                'total_quantity_sent'     => 0,
                'total_quantity_received' => 0,
                'total_value'             => 0,
                'total_cost'              => 0,
            ];
        }
    }

    /**
     * تقرير التحويلات
     */
    public function getTransfersReport(array $filters = []): array
    {
        try {
            $query = WarehouseTransfer::query();

            if (!empty($filters['from_date'])) {
                $query->whereDate('transfer_date', '>=', $filters['from_date']);
            }
            if (!empty($filters['to_date'])) {
                $query->whereDate('transfer_date', '<=', $filters['to_date']);
            }
            if (!empty($filters['from_warehouse'])) {
                $query->where('from_warehouse_id', $filters['from_warehouse']);
            }
            if (!empty($filters['to_warehouse'])) {
                $query->where('to_warehouse_id', $filters['to_warehouse']);
            }
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $transfers     = $query->with(['items.product:id,purchase_price'])->get();
            $totalItems    = 0;
            $totalQuantity = 0;
            $totalValue    = 0;

            foreach ($transfers as $transfer) {
                $totalItems += $transfer->items->count();
                foreach ($transfer->items as $item) {
                    $quantity      = floatval($item->quantity_sent);
                    $purchasePrice = floatval($item->product->purchase_price ?? 0);
                    $totalQuantity += $quantity;
                    $totalValue    += $quantity * $purchasePrice;
                }
            }

            return [
                'total_transfers' => $transfers->count(),
                'total_items'     => $totalItems,
                'total_quantity'  => round($totalQuantity, 2),
                'total_value'     => round($totalValue, 2),
                'transfers'       => $transfers,
            ];

        } catch (Exception $e) {
            Log::error('❌ فشل إنشاء تقرير التحويلات', ['error' => $e->getMessage()]);

            return [
                'total_transfers' => 0,
                'total_items'     => 0,
                'total_quantity'  => 0,
                'total_value'     => 0,
                'transfers'       => collect([]),
            ];
        }
    }
}