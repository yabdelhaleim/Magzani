<?php

namespace App\Services;

use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use App\Models\ProductWarehouse;
use App\Traits\TransferValidationTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class TransferService
{
    use TransferValidationTrait;

    private const CHUNK_SIZE = 500;

    public function __construct(
        private InventoryMovementService $movementService
    ) {}

    /**
     * ✅ جلب كل التحويلات مع الفلاتر - محسّن
     */
    public function getAllTransfers(array $filters = [])
    {
        try {
            $query = WarehouseTransfer::query();

            // ✅ الفلاتر
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

            // ✅ العلاقات والإحصائيات
            return $query->forListing()
                ->paginate($filters['per_page'] ?? 20)
                ->withQueryString();

        } catch (\Exception $e) {
            Log::error('❌ getAllTransfers failed', [
                'error' => $e->getMessage()
            ]);

            // Return empty paginator
            return new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                20,
                1
            );
        }
    }

    /**
     * ✅ إنشاء وتنفيذ التحويل - محسّن للبيانات الكبيرة
     */
    public function createTransfer(array $data): WarehouseTransfer
    {
        return DB::transaction(function () use ($data) {
            
            // 1. التحقق من صحة البيانات
            $this->validateTransfer($data);

            Log::info('🔄 بدء إنشاء تحويل', [
                'from' => $data['from_warehouse_id'],
                'to' => $data['to_warehouse_id'],
                'items_count' => count($data['items'])
            ]);

            // 2. إنشاء التحويل
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

            // 3. إضافة العناصر - Bulk Insert
            $this->bulkInsertItems($transfer->id, $data['items']);

            // 4. تنفيذ التحويل بالـ Chunks
            $this->executeTransferInChunks($transfer);

            // 5. مسح الكاش
            $this->clearWarehousesCache([
                $data['from_warehouse_id'],
                $data['to_warehouse_id']
            ]);

            Log::info('✅ تم التحويل بنجاح', [
                'transfer_id' => $transfer->id,
                'transfer_number' => $transfer->transfer_number
            ]);

            return $transfer->fresh(['items.product', 'fromWarehouse', 'toWarehouse']);
        });
    }

    /**
     * ✅ Bulk Insert للـ Items
     */
    private function bulkInsertItems(int $transferId, array $items): void
    {
        $itemsData = [];
        $now = now();

        foreach ($items as $item) {
            $itemsData[] = [
                'warehouse_transfer_id' => $transferId,
                'product_id'           => $item['product_id'],
                'quantity_sent'        => $item['quantity'],
                'quantity_received'    => 0,
                'notes'                => $item['notes'] ?? null,
                'created_at'           => $now,
                'updated_at'           => $now,
            ];
        }

        WarehouseTransferItem::insert($itemsData);
    }

    /**
     * ✅ تنفيذ التحويل بالـ Chunks - معالجة 500 منتج كل مرة
     */
    private function executeTransferInChunks(WarehouseTransfer $transfer): void
    {
        if ($transfer->status !== 'draft') {
            throw new Exception('❌ التحويل تم تنفيذه مسبقاً');
        }

        $allItems = $transfer->items()->with('product:id,name')->get();
        $totalItems = $allItems->count();

        Log::info('📦 معالجة المنتجات', ['total' => $totalItems]);

        // معالجة بالـ Chunks
        $allItems->chunk(self::CHUNK_SIZE)->each(function ($chunk, $index) use ($transfer, $totalItems) {
            $chunkNumber = $index + 1;
            $processed = min(($index + 1) * self::CHUNK_SIZE, $totalItems);

            Log::info("⚙️ Chunk {$chunkNumber}", [
                'processed' => $processed,
                'total' => $totalItems
            ]);

            $this->processTransferChunk($transfer, $chunk);
        });

        // تحديث حالة التحويل
        $transfer->update([
            'status'         => 'received',
            'received_date'  => now(),
            'confirmed_by'   => auth()->id(),
            'confirmed_at'   => now(),
            'received_by'    => auth()->id(),
        ]);

        // Bulk Update للـ Items
        WarehouseTransferItem::where('warehouse_transfer_id', $transfer->id)
            ->update(['quantity_received' => DB::raw('quantity_sent')]);
    }

    /**
     * ✅ معالجة Chunk واحد (500 منتج)
     */
    private function processTransferChunk(WarehouseTransfer $transfer, $items): void
    {
        $productIds = $items->pluck('product_id')->toArray();

        // جلب المخزون بـ Batch واحد
        $sourceStocks = ProductWarehouse::where('warehouse_id', $transfer->from_warehouse_id)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        $destStocks = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        // Arrays للـ Bulk Operations
        $movements = [];

        foreach ($items as $item) {
            $productId = $item->product_id;
            $quantity = $item->quantity_sent;

            // التحقق من المخزون
            if (!isset($sourceStocks[$productId])) {
                throw new Exception("❌ المنتج {$item->product->name} غير موجود في المخزن المصدر");
            }

            if ($sourceStocks[$productId]->quantity < $quantity) {
                throw new Exception("❌ مخزون غير كافي للمنتج {$item->product->name}");
            }

            // خصم من المصدر
            DB::table('product_warehouse')
                ->where('product_id', $productId)
                ->where('warehouse_id', $transfer->from_warehouse_id)
                ->decrement('quantity', $quantity);

            // إضافة للوجهة
            if (isset($destStocks[$productId])) {
                DB::table('product_warehouse')
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->increment('quantity', $quantity);
            } else {
                ProductWarehouse::create([
                    'product_id'       => $productId,
                    'warehouse_id'     => $transfer->to_warehouse_id,
                    'quantity'         => $quantity,
                    'min_stock'        => 10,
                    'reserved_quantity' => 0,
                ]);
            }

            // تحضير الحركات للـ Bulk Insert
            $movements[] = [
                'warehouse_id'   => $transfer->from_warehouse_id,
                'product_id'     => $productId,
                'movement_type'  => 'transfer_out',
                'quantity_change' => -$quantity,
                'notes'          => "تحويل صادر #{$transfer->transfer_number}",
                'reference_type' => WarehouseTransfer::class,
                'reference_id'   => $transfer->id,
                'movement_date'  => $transfer->transfer_date,
                'movement_number' => 'MV' . now()->format('YmdHis') . $productId,
            ];

            $movements[] = [
                'warehouse_id'   => $transfer->to_warehouse_id,
                'product_id'     => $productId,
                'movement_type'  => 'transfer_in',
                'quantity_change' => $quantity,
                'notes'          => "تحويل وارد #{$transfer->transfer_number}",
                'reference_type' => WarehouseTransfer::class,
                'reference_id'   => $transfer->id,
                'movement_date'  => $transfer->transfer_date,
                'movement_number' => 'MV' . now()->format('YmdHis') . $productId . 'IN',
            ];
        }

        // تسجيل الحركات دفعة واحدة
        if (!empty($movements)) {
            $this->movementService->recordBulkMovements($movements);
        }
    }

    /**
     * ✅ عكس التحويل - محسّن
     */
    public function reverseTransfer(int $transferId): WarehouseTransfer
    {
        return DB::transaction(function () use ($transferId) {
            
            $transfer = WarehouseTransfer::with('items.product')
                ->lockForUpdate()
                ->findOrFail($transferId);

            $this->validateReversal($transfer);

            Log::info('🔄 بدء عكس التحويل', [
                'transfer_id' => $transferId,
                'transfer_number' => $transfer->transfer_number
            ]);

            $items = $transfer->items;
            $productIds = $items->pluck('product_id')->toArray();

            // جلب المخزون
            $destStocks = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $sourceStocks = ProductWarehouse::where('warehouse_id', $transfer->from_warehouse_id)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            // عكس كل منتج
            $movements = [];

            foreach ($items as $item) {
                $productId = $item->product_id;
                $quantity = $item->quantity_sent;

                // خصم من الوجهة
                if (!isset($destStocks[$productId])) {
                    throw new Exception("❌ المنتج غير موجود في مخزن الوجهة");
                }

                DB::table('product_warehouse')
                    ->where('product_id', $productId)
                    ->where('warehouse_id', $transfer->to_warehouse_id)
                    ->decrement('quantity', $quantity);

                // إضافة للمصدر
                if (isset($sourceStocks[$productId])) {
                    DB::table('product_warehouse')
                        ->where('product_id', $productId)
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->increment('quantity', $quantity);
                } else {
                    ProductWarehouse::create([
                        'product_id'       => $productId,
                        'warehouse_id'     => $transfer->from_warehouse_id,
                        'quantity'         => $quantity,
                        'min_stock'        => 10,
                        'reserved_quantity' => 0,
                    ]);
                }

                // تسجيل الحركات
                $movements[] = [
                    'warehouse_id'   => $transfer->to_warehouse_id,
                    'product_id'     => $productId,
                    'movement_type'  => 'transfer_out',
                    'quantity_change' => -$quantity,
                    'notes'          => "عكس تحويل #{$transfer->transfer_number}",
                    'reference_type' => WarehouseTransfer::class,
                    'reference_id'   => $transfer->id,
                    'movement_date'  => now(),
                    'movement_number' => 'MV' . now()->format('YmdHis') . $productId . 'REV',
                ];

                $movements[] = [
                    'warehouse_id'   => $transfer->from_warehouse_id,
                    'product_id'     => $productId,
                    'movement_type'  => 'transfer_in',
                    'quantity_change' => $quantity,
                    'notes'          => "عكس تحويل #{$transfer->transfer_number}",
                    'reference_type' => WarehouseTransfer::class,
                    'reference_id'   => $transfer->id,
                    'movement_date'  => now(),
                    'movement_number' => 'MV' . now()->format('YmdHis') . $productId . 'REVIN',
                ];
            }

            // تسجيل الحركات
            if (!empty($movements)) {
                $this->movementService->recordBulkMovements($movements);
            }

            // تحديث الحالة
            $transfer->update([
                'status'      => 'reversed',
                'reversed_at' => now(),
            ]);

            $this->clearWarehousesCache([
                $transfer->from_warehouse_id,
                $transfer->to_warehouse_id
            ]);

            Log::info('✅ تم عكس التحويل', ['transfer_id' => $transferId]);

            return $transfer->fresh(['items.product', 'fromWarehouse', 'toWarehouse']);
        });
    }

    /**
     * ✅ إلغاء التحويل
     */
    public function cancelTransfer(int $transferId): WarehouseTransfer
    {
        return DB::transaction(function () use ($transferId) {
            
            $transfer = WarehouseTransfer::lockForUpdate()->findOrFail($transferId);

            $this->validateCancellation($transfer);

            Log::info('🔄 بدء إلغاء التحويل', [
                'transfer_id' => $transferId,
                'status' => $transfer->status
            ]);

            // إذا كان مستلم، عكس الكميات
            if ($transfer->status === 'received') {
                return $this->reverseTransfer($transferId);
            }

            // إلغاء مباشر
            $transfer->update(['status' => 'cancelled']);

            $this->clearWarehousesCache([
                $transfer->from_warehouse_id,
                $transfer->to_warehouse_id
            ]);

            Log::info('✅ تم الإلغاء', ['transfer_id' => $transferId]);

            return $transfer->fresh();
        });
    }

    /**
     * ✅ التحويلات المعلقة
     */
    public function getPendingTransfers()
    {
        return WarehouseTransfer::whereIn('status', ['draft', 'pending', 'in_transit'])
            ->withStats()
            ->withRelations()
            ->latest('transfer_date')
            ->get();
    }

    /**
     * ✅ تفاصيل التحويل
     */
    public function getTransferDetails(int $transferId)
    {
        return WarehouseTransfer::with([
            'items.product:id,name,code,sku,unit',
            'fromWarehouse:id,name,code',
            'toWarehouse:id,name,code',
            'createdBy:id,name',
            'confirmedBy:id,name',
            'receivedBy:id,name',
        ])->findOrFail($transferId);
    }

    /**
     * ✅ توليد رقم تحويل فريد
     */
    private function generateTransferNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TR' . $date;

        $lastNumber = WarehouseTransfer::where('transfer_number', 'like', $prefix . '%')
            ->latest('id')
            ->value('transfer_number');

        $sequence = $lastNumber ? intval(substr($lastNumber, -4)) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ✅ مسح كاش المخازن
     */
    private function clearWarehousesCache(array $warehouseIds): void
    {
        foreach ($warehouseIds as $id) {
            Cache::forget("warehouse_products_stock_{$id}");
            Cache::forget("warehouse_stats_{$id}");
        }
    }
}