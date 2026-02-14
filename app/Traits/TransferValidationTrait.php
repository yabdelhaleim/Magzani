<?php

namespace App\Traits;

use App\Models\WarehouseTransfer;
use App\Models\ProductWarehouse;
use Exception;

trait TransferValidationTrait
{   
    /**
     * التحقق من صحة التحويل
     */
    public function validateTransfer(array $data): void
    {
        if (empty($data['items']) || !is_array($data['items'])) {
            throw new Exception('❌ يجب إضافة منتجات للتحويل');
        }

        if ($data['from_warehouse_id'] == $data['to_warehouse_id']) {
            throw new Exception('❌ لا يمكن التحويل لنفس المخزن');
        }

        // التحقق من المخزون لكل منتج
        foreach ($data['items'] as $item) {
            $stock = ProductWarehouse::where('warehouse_id', $data['from_warehouse_id'])
                ->where('product_id', $item['product_id'])
                ->first();

            if (!$stock) {
                throw new Exception("❌ المنتج غير موجود في المخزن المصدر");
            }

            if ($stock->quantity < $item['quantity']) {
                throw new Exception("❌ المخزون غير كافي للمنتج");
            }
        }
    }

    /**
     * التحقق من إمكانية عكس التحويل
     */
    public function validateReversal(WarehouseTransfer $transfer): void
    {
        if ($transfer->status !== 'received') {
            throw new Exception('❌ يمكن عكس التحويلات المستلمة فقط');
        }

        if ($transfer->status === 'reversed') {
            throw new Exception('❌ التحويل معكوس مسبقاً');
        }

        if ($transfer->status === 'cancelled') {
            throw new Exception('❌ التحويل ملغي ولا يمكن عكسه');
        }

        // التحقق من المخزون في المخزن الوجهة
        foreach ($transfer->items as $item) {
            $stock = ProductWarehouse::where('warehouse_id', $transfer->to_warehouse_id)
                ->where('product_id', $item->product_id)
                ->first();

            if (!$stock || $stock->quantity < $item->quantity_sent) {
                throw new Exception("❌ لا يمكن عكس التحويل - مخزون غير كافي في المخزن الوجهة");
            }
        }
    }

    /**
     * التحقق من إمكانية الإلغاء
     */
    public function validateCancellation(WarehouseTransfer $transfer): void
    {
        if ($transfer->status === 'reversed') {
            throw new Exception('❌ التحويل معكوس بالفعل');
        }

        if ($transfer->status === 'cancelled') {
            throw new Exception('❌ التحويل ملغي بالفعل');
        }
    }
}