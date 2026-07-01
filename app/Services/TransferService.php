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

    public function getWarehouseTransfers(int $warehouseId, array $filters = [])
    {
        $query = WarehouseTransfer::query()
            ->where('from_warehouse_id', $warehouseId)
            ->orWhere('to_warehouse_id', $warehouseId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['date_from'])) {
            $query->whereDate('transfer_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('transfer_date', '<=', $filters['date_to']);
        }

        $query->with([
            'fromWarehouse:id,name,code',
            'toWarehouse:id,name,code',
            'items.product:id,name,code',
            'createdBy:id,name',
        ]);

        return $query->orderByDesc('created_at')->paginate($filters['per_page'] ?? 20);
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
        $posSetting = \App\Models\PosSetting::where('default_warehouse_id', $fromWarehouseId)->first()
            ?? \App\Models\PosSetting::first()
            ?? \App\Models\PosSetting::getSolo();

        if (!$posSetting->allow_negative_stock) {
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
                throw new \App\Exceptions\InsufficientStockException("❌ أخطاء في المخزون:\n" . implode("\n", $errors));
            }
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
            // 1. Source Warehouse: Deduct Stock
            app(\App\Services\StockService::class)->adjust(
                warehouseId: $transfer->from_warehouse_id,
                productId: $productId,
                qty: -$quantity,
                type: \App\Services\StockService::TRANSFER_OUT,
                referenceId: $transfer->id
            );

            // 2. Calculate source average cost to pass to target warehouse
            $sourceStock = ProductWarehouse::where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_id', $productId)
                ->first();
            $sourceAvgCost = $sourceStock ? (float) $sourceStock->average_cost : 0.0;
            if ($sourceAvgCost <= 0.0 && $item->product) {
                $sourceAvgCost = (float) ($item->product->purchase_price ?? 0.0);
            }

            // 3. Target Warehouse: Add Stock
            app(\App\Services\StockService::class)->adjust(
                warehouseId: $transfer->to_warehouse_id,
                productId: $productId,
                qty: $quantity,
                type: \App\Services\StockService::TRANSFER_IN,
                referenceId: $transfer->id,
                unitCost: $sourceAvgCost
            );

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('❌ فشل نقل منتج واحد', ['product_id' => $productId, 'error' => $e->getMessage()]);
            throw $e;
        }
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
            // 1. Destination Warehouse: Deduct Stock
            app(\App\Services\StockService::class)->adjust(
                warehouseId: $transfer->to_warehouse_id,
                productId: $productId,
                qty: -$quantity,
                type: \App\Services\StockService::TRANSFER_OUT,
                referenceId: $transfer->id
            );

            // 2. Calculate destination average cost
            $destStock = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
                ->where('product_id', $productId)
                ->first();
            $destAvgCost = $destStock ? (float) $destStock->average_cost : 0.0;
            if ($destAvgCost <= 0.0 && $item->product) {
                $destAvgCost = (float) ($item->product->purchase_price ?? 0.0);
            }

            // 3. Source Warehouse: Re-add Stock
            app(\App\Services\StockService::class)->adjust(
                warehouseId: $transfer->from_warehouse_id,
                productId: $productId,
                qty: $quantity,
                type: \App\Services\StockService::TRANSFER_IN,
                referenceId: $transfer->id,
                unitCost: $destAvgCost
            );

        } catch (Exception $e) {
            Log::error('❌ فشل عكس منتج واحد', ['product_id' => $productId, 'error' => $e->getMessage()]);
            throw $e;
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