<?php

namespace App\Traits;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\WarehouseTransfer;
use Illuminate\Support\Facades\DB;
use Exception;

trait TransferValidationTrait
{
    /**
     * ✅ التحقق من صحة التحويل - كل الشروط
     */
    protected function validateTransfer(array $data): void
    {
        // 1. التحقق من المخازن
        $this->validateWarehouses(
            $data['from_warehouse_id'],
            $data['to_warehouse_id']
        );

        // 2. التحقق من المنتجات والكميات
        $this->validateTransferItems(
            $data['from_warehouse_id'],
            $data['items']
        );
    }

    /**
     * ✅ التحقق من المخازن
     */
    protected function validateWarehouses(int $fromWarehouseId, int $toWarehouseId): void
    {
        // نفس المخزن
        if ($fromWarehouseId === $toWarehouseId) {
            throw new Exception('❌ لا يمكن التحويل إلى نفس المخزن');
        }

        // المخازن موجودة ونشطة - استعلام واحد
        $warehouses = Warehouse::whereIn('id', [$fromWarehouseId, $toWarehouseId])
            ->where('is_active', true)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        if (count($warehouses) !== 2) {
            throw new Exception('❌ أحد المخازن غير نشط أو غير موجود');
        }
    }

    /**
     * ✅ التحقق من المنتجات والكميات - استعلام واحد محسّن
     */
    protected function validateTransferItems(int $warehouseId, array $items): void
    {
        if (empty($items)) {
            throw new Exception('❌ لا يمكن إنشاء تحويل بدون منتجات');
        }

        // جمع IDs المنتجات
        $productIds = array_column($items, 'product_id');

        // جلب كل المنتجات مرة واحدة مع المخزون
        $productsStock = ProductWarehouse::where('warehouse_id', $warehouseId)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        // جلب بيانات المنتجات
        $products = Product::whereIn('id', $productIds)
            ->where('is_active', true)
            ->pluck('name', 'id');

        // التحقق من كل منتج
        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];

            // الكمية صفر أو سالبة
            if ($quantity <= 0) {
                throw new Exception('❌ الكمية يجب أن تكون أكبر من صفر');
            }

            // المنتج غير نشط
            if (!isset($products[$productId])) {
                throw new Exception('❌ المنتج غير نشط أو غير موجود');
            }

            // المنتج غير موجود في المخزن
            if (!isset($productsStock[$productId])) {
                throw new Exception(
                    "❌ '{$products[$productId]}' غير موجود في المخزن المصدر"
                );
            }

            $stock = $productsStock[$productId];
            $available = $stock->quantity - ($stock->reserved_quantity ?? 0);

            // الكمية غير كافية
            if ($available < $quantity) {
                throw new Exception(
                    "❌ '{$products[$productId]}' - المتوفر: {$available}, المطلوب: {$quantity}"
                );
            }
        }
    }

    /**
     * ✅ التحقق من إمكانية عكس التحويل
     */
    protected function validateReversal(WarehouseTransfer $transfer): void
    {
        // الحالة خاطئة
        if ($transfer->status !== 'received') {
            throw new Exception(
                '❌ يمكن عكس التحويلات المستلمة فقط (الحالة: ' . $transfer->status_label . ')'
            );
        }

        // التحقق من توفر الكميات في المخزن الهدف
        $items = $transfer->items()->with('product:id,name')->get();
        
        $productIds = $items->pluck('product_id')->toArray();
        
        // استعلام واحد للتحقق من المخزون
        $stocks = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        foreach ($items as $item) {
            if (!isset($stocks[$item->product_id])) {
                throw new Exception(
                    "❌ '{$item->product->name}' غير موجود في المخزن الهدف للعكس"
                );
            }

            $stock = $stocks[$item->product_id];
            $available = $stock->quantity - ($stock->reserved_quantity ?? 0);

            if ($available < $item->quantity_sent) {
                throw new Exception(
                    "❌ '{$item->product->name}' - المتوفر: {$available}, المطلوب للعكس: {$item->quantity_sent}"
                );
            }
        }
    }

    /**
     * ✅ التحقق من إمكانية الإلغاء
     */
    protected function validateCancellation(WarehouseTransfer $transfer): void
    {
        $allowedStatuses = ['draft', 'pending', 'in_transit'];

        if (!in_array($transfer->status, $allowedStatuses)) {
            throw new Exception(
                '❌ لا يمكن إلغاء تحويل بحالة: ' . $transfer->status_label
            );
        }
    }

    /**
     * ✅ التحقق من إمكانية التعديل
     */
    protected function validateEdit(WarehouseTransfer $transfer): void
    {
        $allowedStatuses = ['draft', 'pending'];

        if (!in_array($transfer->status, $allowedStatuses)) {
            throw new Exception(
                '❌ لا يمكن تعديل تحويل بحالة: ' . $transfer->status_label
            );
        }
    }

    /**
     * ✅ التحقق من رقم التحويل الفريد
     */
    protected function ensureUniqueTransferNumber(string $number): void
    {
        $exists = WarehouseTransfer::where('transfer_number', $number)->exists();

        if ($exists) {
            throw new Exception('❌ رقم التحويل موجود مسبقاً');
        }
    }
}