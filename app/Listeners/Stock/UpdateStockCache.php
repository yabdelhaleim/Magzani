<?php
namespace App\Listeners\Stock;

use App\Events\Stock\StockUpdated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateStockCache
{
    public function handle(StockUpdated $event): void
    {
        try {
            // مسح Cache المخزون
            Cache::forget('inventory_report_all');
            Cache::forget('inventory_report_' . $event->warehouseId);
            Cache::forget('product_stock_' . $event->productId);
            Cache::forget('low_stock_products');

            // تسجيل التغيير
            Log::info('Stock Updated', [
                'product_id' => $event->productId,
                'warehouse_id' => $event->warehouseId,
                'old_quantity' => $event->oldQuantity,
                'new_quantity' => $event->newQuantity,
                'difference' => $event->newQuantity - $event->oldQuantity,
                'operation' => $event->operation,
                'updated_by' => $event->updatedBy,
            ]);

            // فحص المخزون المنخفض بعد التحديث
            if ($event->newQuantity <= $this->getMinStock($event->productId, $event->warehouseId)) {
                $product = \App\Models\Product::find($event->productId);
                $warehouse = \App\Models\Warehouse::find($event->warehouseId);
                
                event(new \App\Events\Stock\StockLow(
                    $product,
                    $warehouse,
                    $event->newQuantity,
                    $this->getMinStock($event->productId, $event->warehouseId)
                ));
            }

        } catch (\Exception $e) {
            Log::error('Failed to update stock cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getMinStock(int $productId, int $warehouseId): int
    {
        return \Illuminate\Support\Facades\DB::table('product_warehouse')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('min_stock') ?? 10;
    }
}

