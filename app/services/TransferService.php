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

    private const CHUNK_SIZE = 500;
    private const BATCH_INSERT_SIZE = 1000;
    private const CACHE_TTL = 300;

    public function __construct(
        private InventoryMovementService $movementService
    ) {}

    /**
     * ✅ جلب كل التحويلات مع الفلاتر - محسّن ومصلح
     */
    public function getAllTransfers(array $filters = [])
    {
        try {
            $query = WarehouseTransfer::query();

            // الفلاتر
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

            // ✅ العلاقات المصلحة - مع eager loading صحيح
            $query->with([
                'fromWarehouse:id,name,code',
                'toWarehouse:id,name,code',
                'items' => function($q) {
                    $q->select('id', 'warehouse_transfer_id', 'product_id', 'quantity_sent', 'quantity_received', 'notes')
                      ->with('product:id,name,code,sku,purchase_price');
                },
                'createdBy:id,name'
            ]);

            // ✅ إضافة إحصائيات ديناميكية
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
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'filters' => $filters
            ]);

            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20, 1);
        }
    }

    /**
     * ✅ إنشاء وتنفيذ التحويل - مصلح بالكامل
     */
    public function createTransfer(array $data): WarehouseTransfer
    {
        // ✅ التحقق من البيانات قبل البدء
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new Exception('❌ لا توجد منتجات للتحويل');
        }

        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            throw new Exception('❌ لا يمكن التحويل من نفس المخزن إلى نفسه');
        }

        return DB::transaction(function () use ($data) {
            
            try {
                // 1. التحقق من صحة البيانات
                $this->validateTransfer($data);

                Log::info('🔄 بدء إنشاء تحويل', [
                    'from' => $data['from_warehouse_id'],
                    'to' => $data['to_warehouse_id'],
                    'items_count' => count($data['items'])
                ]);

                // 2. التحقق من توفر المخزون لكل المنتجات قبل البدء
                $this->validateStockAvailability($data);

                // 3. إنشاء التحويل
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

                Log::info('✅ تم إنشاء سجل التحويل', [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number
                ]);

                // 4. إضافة العناصر بشكل صحيح
                $itemsData = [];
                $now = now();
                
                foreach ($data['items'] as $item) {
                    // ✅ التحقق من صحة البيانات
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
                
                // ✅ Bulk insert مع معالجة الأخطاء
                if (!empty($itemsData)) {
                    collect($itemsData)->chunk(self::BATCH_INSERT_SIZE)->each(function ($chunk) {
                        WarehouseTransferItem::insert($chunk->toArray());
                    });

                    Log::info('✅ تم إضافة منتجات التحويل', [
                        'items_count' => count($itemsData)
                    ]);
                } else {
                    throw new Exception('❌ لا توجد منتجات صالحة للتحويل');
                }

                // 5. ✅ تنفيذ التحويل الفعلي
                $this->executeTransfer($transfer);

                // 6. تحديث حالة التحويل
                $transfer->update([
                    'status'         => 'received',
                    'received_date'  => now(),
                    'confirmed_at'   => now(),
                    'received_by'    => auth()->id(),
                    'confirmed_by'   => auth()->id(),
                ]);

                // 7. مسح الكاش
                $this->clearWarehousesCache([
                    $data['from_warehouse_id'],
                    $data['to_warehouse_id']
                ]);

                Log::info('✅ تم التحويل بنجاح', [
                    'transfer_id' => $transfer->id,
                    'transfer_number' => $transfer->transfer_number,
                    'status' => $transfer->status
                ]);

                return $transfer->fresh(['items.product', 'fromWarehouse', 'toWarehouse']);

            } catch (Exception $e) {
                Log::error('❌ فشل إنشاء التحويل', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'data' => $data
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * ✅ التحقق من توفر المخزون لكل المنتجات
     */
    private function validateStockAvailability(array $data): void
    {
        $fromWarehouseId = $data['from_warehouse_id'];
        $errors = [];

        foreach ($data['items'] as $index => $item) {
            $productId = $item['product_id'];
            $requestedQty = floatval($item['quantity']);

            // جلب المخزون الحالي
            $stock = ProductWarehouse::where('warehouse_id', $fromWarehouseId)
                ->where('product_id', $productId)
                ->first();

            if (!$stock) {
                $errors[] = "المنتج #{$productId} غير موجود في المخزن المصدر";
                continue;
            }

            if ($stock->quantity < $requestedQty) {
                $errors[] = "المنتج #{$productId}: المخزون غير كافي (متوفر: {$stock->quantity}, مطلوب: {$requestedQty})";
            }
        }

        if (!empty($errors)) {
            throw new Exception("❌ أخطاء في المخزون:\n" . implode("\n", $errors));
        }

        Log::info('✅ تم التحقق من توفر المخزون لجميع المنتجات');
    }

    /**
     * ✅ تنفيذ التحويل الفعلي - مصلح ومحسّن
     */
    private function executeTransfer(WarehouseTransfer $transfer): void
    {
        Log::info('🔄 بدء تنفيذ التحويل الفعلي', [
            'transfer_id' => $transfer->id
        ]);

        $itemsCount = 0;
        $successCount = 0;
        $failedItems = [];

        $transfer->items()
            ->with('product:id,name,code,sku,purchase_price')
            ->chunk(self::CHUNK_SIZE, function ($items) use ($transfer, &$itemsCount, &$successCount, &$failedItems) {
                foreach ($items as $item) {
                    $itemsCount++;
                    
                    try {
                        $this->transferSingleProduct($transfer, $item);
                        $successCount++;
                        
                        Log::debug('✅ تم نقل المنتج', [
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity_sent
                        ]);
                        
                    } catch (Exception $e) {
                        $failedItems[] = [
                            'product_id' => $item->product_id,
                            'error' => $e->getMessage()
                        ];
                        
                        Log::error('❌ فشل نقل المنتج', [
                            'product_id' => $item->product_id,
                            'error' => $e->getMessage()
                        ]);
                        
                        throw $e; // إعادة رفع الخطأ لإلغاء التحويل بالكامل
                    }
                }
            });

        if ($successCount !== $itemsCount) {
            throw new Exception("❌ فشل نقل بعض المنتجات: {$successCount}/{$itemsCount}");
        }

        Log::info('✅ تم تنفيذ تحويل المخزون بنجاح', [
            'transfer_id' => $transfer->id,
            'items_processed' => $itemsCount,
            'items_success' => $successCount
        ]);
    }

    /**
     * ✅ نقل منتج واحد - مصلح بالكامل ومحسّن للـ Production
     */
    private function transferSingleProduct(WarehouseTransfer $transfer, WarehouseTransferItem $item): void
    {
        $productId = $item->product_id;
        $quantity = floatval($item->quantity_sent);

        if ($quantity <= 0) {
            throw new Exception("❌ الكمية يجب أن تكون أكبر من صفر");
        }

        DB::beginTransaction();

        try {
            // 1. ✅ الخصم من المخزن المصدر - مع قفل الصف
            $sourceStock = ProductWarehouse::where('warehouse_id', $transfer->from_warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (!$sourceStock) {
                throw new Exception("❌ المنتج #{$productId} غير موجود في المخزن المصدر");
            }

            // ✅ التحقق من الكمية المتاحة
            if ($sourceStock->quantity < $quantity) {
                throw new Exception(
                    "❌ المخزون غير كافي للمنتج #{$productId} - " .
                    "متوفر: " . number_format($sourceStock->quantity, 2) . ", " .
                    "مطلوب: " . number_format($quantity, 2)
                );
            }

            // ✅ حفظ الكميات قبل التعديل
            $sourceQuantityBefore = floatval($sourceStock->quantity);

            // 2. ✅ الإضافة إلى المخزن الوجهة - مع قفل الصف
            $destinationStock = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            $destQuantityBefore = $destinationStock ? floatval($destinationStock->quantity) : 0;

            // 3. ✅ خصم من المصدر
            $sourceStock->quantity = $sourceStock->quantity - $quantity;
            
            if ($sourceStock->quantity < 0) {
                throw new Exception("❌ خطأ في الحساب: الكمية الناتجة سالبة");
            }
            
            $sourceStock->save();
            $sourceStock->refresh();
            $sourceQuantityAfter = floatval($sourceStock->quantity);

            // 4. ✅ إضافة للوجهة
            if ($destinationStock) {
                $destinationStock->quantity = $destinationStock->quantity + $quantity;
                $destinationStock->save();
                $destinationStock->refresh();
                $destQuantityAfter = floatval($destinationStock->quantity);
            } else {
                // إنشاء سجل جديد في المخزن الوجهة
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

            // 5. ✅ جلب سعر الشراء
            $purchasePrice = 0;
            if ($item->product) {
                $purchasePrice = floatval($item->product->purchase_price ?? 0);
            }

            // 6. ✅ التحقق من صحة الحسابات
            $expectedSourceAfter = $sourceQuantityBefore - $quantity;
            $expectedDestAfter = $destQuantityBefore + $quantity;

            if (abs($sourceQuantityAfter - $expectedSourceAfter) > 0.001) {
                throw new Exception(
                    "❌ خطأ في حساب المخزن المصدر - " .
                    "متوقع: " . number_format($expectedSourceAfter, 2) . ", " .
                    "فعلي: " . number_format($sourceQuantityAfter, 2)
                );
            }

            if (abs($destQuantityAfter - $expectedDestAfter) > 0.001) {
                throw new Exception(
                    "❌ خطأ في حساب المخزن الوجهة - " .
                    "متوقع: " . number_format($expectedDestAfter, 2) . ", " .
                    "فعلي: " . number_format($destQuantityAfter, 2)
                );
            }

            // 7. ✅ تسجيل الحركات بالبيانات الصحيحة
            $this->recordStockMovements(
                $transfer, 
                $productId, 
                $quantity, 
                $sourceQuantityBefore,
                $sourceQuantityAfter,
                $destQuantityBefore,
                $destQuantityAfter,
                $purchasePrice
            );

            DB::commit();

            Log::debug('✅ نقل منتج واحد بنجاح', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'source_before' => $sourceQuantityBefore,
                'source_after' => $sourceQuantityAfter,
                'dest_before' => $destQuantityBefore,
                'dest_after' => $destQuantityAfter,
                'purchase_price' => $purchasePrice
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('❌ فشل نقل منتج واحد', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }

    /**
     * ✅ تسجيل حركات المخزون - مصلح بالكامل ومحسّن
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
        // ✅ توليد رقم فريد للحركة
        $timestamp = now()->format('YmdHis');
        $uniqueId = $timestamp . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // ✅ حساب التكاليف
        $totalCost = $quantity * $purchasePrice;

        // ✅ التحقق من صحة البيانات
        if ($sourceQuantityBefore < 0 || $sourceQuantityAfter < 0 || 
            $destQuantityBefore < 0 || $destQuantityAfter < 0 || 
            $quantity <= 0) {
            throw new Exception("❌ خطأ في بيانات حركة المخزون");
        }

        try {
            // ✅ حركة الخروج من المصدر
            InventoryMovement::create([
                'warehouse_id'    => $transfer->from_warehouse_id,
                'product_id'      => $productId,
                'movement_type'   => 'transfer_out',
                'quantity'        => $quantity,
                'quantity_change' => -$quantity, // ✅ سالب للخروج
                'quantity_before' => $sourceQuantityBefore, // ✅ الكمية قبل الخصم
                'quantity_after'  => $sourceQuantityAfter,  // ✅ الكمية بعد الخصم
                'unit_cost'       => $purchasePrice,
                'unit_price'      => 0,
                'total_cost'      => $totalCost,
                'total_price'     => 0,
                'movement_date'   => $transfer->transfer_date,
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "تحويل صادر #{$transfer->transfer_number} إلى {$transfer->toWarehouse->name}",
                'movement_number' => "TOUT-{$uniqueId}",
                'created_by'      => auth()->id() ?? 1,
            ]);

            // ✅ حركة الدخول للوجهة
            InventoryMovement::create([
                'warehouse_id'    => $transfer->to_warehouse_id,
                'product_id'      => $productId,
                'movement_type'   => 'transfer_in',
                'quantity'        => $quantity,
                'quantity_change' => $quantity, // ✅ موجب للدخول
                'quantity_before' => $destQuantityBefore, // ✅ الكمية قبل الإضافة
                'quantity_after'  => $destQuantityAfter,  // ✅ الكمية بعد الإضافة
                'unit_cost'       => $purchasePrice,
                'unit_price'      => 0,
                'total_cost'      => $totalCost,
                'total_price'     => 0,
                'movement_date'   => $transfer->transfer_date,
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "تحويل وارد #{$transfer->transfer_number} من {$transfer->fromWarehouse->name}",
                'movement_number' => "TIN-{$uniqueId}",
                'created_by'      => auth()->id() ?? 1,
            ]);

            Log::debug('✅ تم تسجيل حركات المخزون', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'movement_number' => "TIN/TOUT-{$uniqueId}"
            ]);

        } catch (Exception $e) {
            Log::error('❌ فشل تسجيل حركات المخزون', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            
            throw new Exception("❌ فشل تسجيل حركات المخزون: " . $e->getMessage());
        }
    }

    /**
     * ✅ عكس التحويل - مصلح ومحسّن
     */
    public function reverseTransfer(int $transferId): WarehouseTransfer
    {
        return DB::transaction(function () use ($transferId) {
            
            try {
                $transfer = WarehouseTransfer::with(['items.product', 'fromWarehouse', 'toWarehouse'])
                    ->lockForUpdate()
                    ->findOrFail($transferId);

                $this->validateReversal($transfer);

                Log::info('🔄 بدء عكس التحويل', [
                    'transfer_id' => $transferId,
                    'transfer_number' => $transfer->transfer_number
                ]);

                // عكس كل المنتجات
                foreach ($transfer->items as $item) {
                    $this->reverseSingleProduct($transfer, $item);
                }

                // تحديث حالة التحويل
                $transfer->update([
                    'status'      => 'reversed',
                    'reversed_at' => now(),
                    'reversed_by' => auth()->id(),
                ]);

                // مسح الكاش
                $this->clearWarehousesCache([
                    $transfer->from_warehouse_id,
                    $transfer->to_warehouse_id
                ]);

                Log::info('✅ تم عكس التحويل بنجاح', [
                    'transfer_id' => $transferId
                ]);

                return $transfer->fresh();

            } catch (Exception $e) {
                Log::error('❌ فشل عكس التحويل', [
                    'transfer_id' => $transferId,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * ✅ عكس منتج واحد - مصلح ومحسّن
     */
    private function reverseSingleProduct(WarehouseTransfer $transfer, WarehouseTransferItem $item): void
    {
        $productId = $item->product_id;
        $quantity = floatval($item->quantity_sent);

        if ($quantity <= 0) {
            throw new Exception("❌ الكمية يجب أن تكون أكبر من صفر");
        }

        try {
            // 1. قفل الصفوف
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

            if ($destStock->quantity < $quantity) {
                throw new Exception(
                    "❌ لا يمكن عكس التحويل - مخزون غير كافي في المخزن الوجهة - " .
                    "متوفر: " . number_format($destStock->quantity, 2) . ", " .
                    "مطلوب: " . number_format($quantity, 2)
                );
            }

            // 2. حفظ الكميات قبل التعديل
            $sourceQuantityBefore = $sourceStock ? floatval($sourceStock->quantity) : 0;
            $destQuantityBefore = floatval($destStock->quantity);

            // 3. تنفيذ العكس
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

            // 4. تحديث الكميات
            $sourceStock->quantity = $sourceStock->quantity + $quantity;
            $sourceStock->save();
            $sourceStock->refresh();

            $destStock->quantity = $destStock->quantity - $quantity;
            
            if ($destStock->quantity < 0) {
                throw new Exception("❌ خطأ في الحساب: الكمية الناتجة سالبة");
            }
            
            $destStock->save();
            $destStock->refresh();

            $sourceQuantityAfter = floatval($sourceStock->quantity);
            $destQuantityAfter = floatval($destStock->quantity);

            // 5. ✅ جلب سعر الشراء
            $purchasePrice = 0;
            if ($item->product) {
                $purchasePrice = floatval($item->product->purchase_price ?? 0);
            }

            // 6. التحقق من صحة الحسابات
            $expectedSourceAfter = $sourceQuantityBefore + $quantity;
            $expectedDestAfter = $destQuantityBefore - $quantity;

            if (abs($sourceQuantityAfter - $expectedSourceAfter) > 0.001) {
                throw new Exception("❌ خطأ في حساب المخزن المصدر عند العكس");
            }

            if (abs($destQuantityAfter - $expectedDestAfter) > 0.001) {
                throw new Exception("❌ خطأ في حساب المخزن الوجهة عند العكس");
            }

            // 7. تسجيل حركات العكس
            $this->recordReversalMovements(
                $transfer, 
                $productId, 
                $quantity, 
                $sourceQuantityBefore,
                $sourceQuantityAfter,
                $destQuantityBefore,
                $destQuantityAfter,
                $purchasePrice
            );

            Log::debug('✅ عكس منتج واحد بنجاح', [
                'product_id' => $productId,
                'quantity' => $quantity
            ]);

        } catch (Exception $e) {
            Log::error('❌ فشل عكس منتج واحد', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * ✅ تسجيل حركات العكس - مصلح ومحسّن
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
        $uniqueId = $timestamp . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $totalCost = $quantity * $purchasePrice;

        try {
            // حركة العودة للمصدر
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
                'movement_date'   => now(),
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "عكس تحويل #{$transfer->transfer_number}",
                'movement_number' => "RTF-{$uniqueId}",
                'created_by'      => auth()->id() ?? 1,
            ]);

            // حركة الخروج من الوجهة
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
                'movement_date'   => now(),
                'reference_type'  => WarehouseTransfer::class,
                'reference_id'    => $transfer->id,
                'notes'           => "عكس تحويل #{$transfer->transfer_number}",
                'movement_number' => "TRV-{$uniqueId}",
                'created_by'      => auth()->id() ?? 1,
            ]);

        } catch (Exception $e) {
            throw new Exception("❌ فشل تسجيل حركات العكس: " . $e->getMessage());
        }
    }

    /**
     * ✅ إلغاء التحويل - مصلح
     */
    public function cancelTransfer(int $transferId): WarehouseTransfer
    {
        return DB::transaction(function () use ($transferId) {
            
            try {
                $transfer = WarehouseTransfer::lockForUpdate()->findOrFail($transferId);
                $this->validateCancellation($transfer);

                // إذا كان التحويل مكتمل، نعكسه
                if ($transfer->status === 'received') {
                    return $this->reverseTransfer($transferId);
                }

                // إذا كان في مرحلة مسودة أو معلق، نلغيه فقط
                $transfer->update([
                    'status'       => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                ]);

                $this->clearWarehousesCache([
                    $transfer->from_warehouse_id,
                    $transfer->to_warehouse_id
                ]);

                Log::info('✅ تم إلغاء التحويل', [
                    'transfer_id' => $transferId
                ]);

                return $transfer->fresh();

            } catch (Exception $e) {
                Log::error('❌ فشل إلغاء التحويل', [
                    'transfer_id' => $transferId,
                    'error' => $e->getMessage()
                ]);
                
                throw $e;
            }
        });
    }

    /**
     * ✅ التحويلات المعلقة
     */
    public function getPendingTransfers()
    {
        try {
            return WarehouseTransfer::whereIn('status', ['draft', 'pending', 'in_transit'])
                ->with([
                    'fromWarehouse:id,name,code',
                    'toWarehouse:id,name,code'
                ])
                ->withCount('items')
                ->latest('transfer_date')
                ->latest('id')
                ->get();

        } catch (Exception $e) {
            Log::error('❌ فشل جلب التحويلات المعلقة', [
                'error' => $e->getMessage()
            ]);
            
            return collect([]);
        }
    }

    /**
     * ✅ تفاصيل التحويل - مصلح
     */
    public function getTransferDetails(int $transferId)
    {
        try {
            return WarehouseTransfer::with([
                'items.product:id,name,code,sku,unit,purchase_price', // ✅ مصلح
                'fromWarehouse:id,name,code,location',
                'toWarehouse:id,name,code,location',
                'createdBy:id,name',
                'receivedBy:id,name',
                'confirmedBy:id,name',
                'reversedBy:id,name',
                'cancelledBy:id,name',
            ])->findOrFail($transferId);

        } catch (Exception $e) {
            Log::error('❌ فشل جلب تفاصيل التحويل', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * ✅ توليد رقم تحويل فريد - محسّن
     */
    private function generateTransferNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TR' . $date;

        $lastNumber = WarehouseTransfer::where('transfer_number', 'like', $prefix . '%')
            ->latest('id')
            ->lockForUpdate()
            ->value('transfer_number');

        $sequence = $lastNumber ? intval(substr($lastNumber, -4)) + 1 : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * ✅ مسح كاش المخازن - محسّن
     */
    private function clearWarehousesCache(array $warehouseIds): void
    {
        try {
            foreach ($warehouseIds as $id) {
                $cacheKeys = [
                    "warehouse_stock_{$id}",
                    "warehouse_stats_{$id}",
                    "warehouse_data_{$id}",
                    "warehouse_products_{$id}",
                    "warehouse_summary_{$id}",
                ];
                
                foreach ($cacheKeys as $key) {
                    Cache::forget($key);
                }
            }
            
            // مسح الكاش العام
            Cache::forget('all_warehouses');
            Cache::forget('warehouses_list');
            
            Log::debug('✅ تم مسح كاش المخازن', [
                'warehouse_ids' => $warehouseIds
            ]);

        } catch (Exception $e) {
            Log::warning('⚠️ فشل مسح الكاش', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * ✅ إحصائيات التحويل - مصلح ومحسّن
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

            // حساب القيمة الإجمالية بدقة
            $totalValue = 0;
            $totalCost = 0;
            
            foreach ($transfer->items as $item) {
                $purchasePrice = floatval($item->product->purchase_price ?? 0); // ✅ مصلح
                $itemTotal = floatval($item->quantity_sent) * $purchasePrice;
                $totalValue += $itemTotal;
                $totalCost += $itemTotal;
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
            Log::error('❌ فشل حساب إحصائيات التحويل', [
                'transfer_id' => $transferId,
                'error' => $e->getMessage()
            ]);
            
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
     * ✅ الحصول على تقرير التحويلات
     */
    public function getTransfersReport(array $filters = []): array
    {
        try {
            $query = WarehouseTransfer::query();

            // تطبيق الفلاتر
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

            $transfers = $query->with(['items.product:id,purchase_price'])->get();

            // حساب الإحصائيات
            $totalTransfers = $transfers->count();
            $totalItems = 0;
            $totalQuantity = 0;
            $totalValue = 0;

            foreach ($transfers as $transfer) {
                $totalItems += $transfer->items->count();
                
                foreach ($transfer->items as $item) {
                    $quantity = floatval($item->quantity_sent);
                    $purchasePrice = floatval($item->product->purchase_price ?? 0);
                    
                    $totalQuantity += $quantity;
                    $totalValue += $quantity * $purchasePrice;
                }
            }

            return [
                'total_transfers' => $totalTransfers,
                'total_items'     => $totalItems,
                'total_quantity'  => round($totalQuantity, 2),
                'total_value'     => round($totalValue, 2),
                'transfers'       => $transfers,
            ];

        } catch (Exception $e) {
            Log::error('❌ فشل إنشاء تقرير التحويلات', [
                'error' => $e->getMessage()
            ]);
            
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