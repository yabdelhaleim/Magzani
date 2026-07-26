<?php

namespace App\Listeners\Stock;

use App\Events\Stock\StockLow;
use App\Events\Stock\StockUpdated;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateStockCache
{
    public function handle(StockUpdated $event): void
    {
        try {
            // مسح Cache المخزون
            Cache::forget('inventory_report_all');
            Cache::forget('inventory_report_'.$event->warehouseId);
            Cache::forget('product_stock_'.$event->productId);
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

            // إرسال التنبيه فقط عند عبور الحد الأدنى من أعلى إلى أسفل.
            $minimumStock = $this->getMinStock($event->productId, $event->warehouseId);
            $crossedMinimum = self::crossedMinimum(
                $event->oldQuantity,
                $event->newQuantity,
                $minimumStock
            );

            if ($crossedMinimum) {
                $product = Product::find($event->productId);
                $warehouse = Warehouse::find($event->warehouseId);

                if ($product && $warehouse) {
                    event(new StockLow(
                        $product,
                        $warehouse,
                        $event->newQuantity,
                        $minimumStock
                    ));
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to update stock cache', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function crossedMinimum(float $oldQuantity, float $newQuantity, float $minimumStock): bool
    {
        return $minimumStock > 0
            && $oldQuantity > $minimumStock
            && $newQuantity <= $minimumStock;
    }

    private function getMinStock(int $productId, int $warehouseId): float
    {
        return (float) (DB::table('product_warehouse')
            ->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->value('min_stock') ?? 0);
    }
}
