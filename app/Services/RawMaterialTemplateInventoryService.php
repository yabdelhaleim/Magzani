<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\RawMaterialTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * يزامن قوالب الخامات مع جدول المنتجات وربط المخزن حتى تظهر في صفحة تفاصيل المخزن.
 */
class RawMaterialTemplateInventoryService
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function sync(RawMaterialTemplate $template, ?int $previousWarehouseId = null): void
    {
        if (! $template->warehouse_id) {
            return;
        }

        DB::transaction(function () use ($template, $previousWarehouseId) {
            $purchase = max(0.0, (float) $template->buy_price);
            $sale = max((float) $template->sale_price, $purchase);

            $product = $this->resolveProduct($template, $purchase, $sale);

            $product->update([
                'purchase_price' => $purchase,
                'selling_price' => $sale,
                'product_type' => 'raw_material',
                'is_manufactured' => false,
                'is_active' => true,
            ]);

            if ($previousWarehouseId && $previousWarehouseId !== (int) $template->warehouse_id) {
                ProductWarehouse::query()
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $previousWarehouseId)
                    ->delete();
                $this->forgetWarehouseCache($previousWarehouseId);
            }

            $this->syncPivot($product, $template);

            if (! $template->product_id || (int) $template->product_id !== (int) $product->id) {
                $template->forceFill(['product_id' => $product->id])->saveQuietly();
            }

            $this->forgetWarehouseCache((int) $template->warehouse_id);
        });
    }

    public function forgetWarehouseCache(int $warehouseId): void
    {
        Cache::forget("warehouse_details_v2_{$warehouseId}");
    }

    private function resolveProduct(RawMaterialTemplate $template, float $purchase, float $sale): Product
    {
        if ($template->product_id) {
            $p = Product::query()->find($template->product_id);
            if ($p) {
                return $p;
            }
        }

        $byName = Product::query()
            ->where('product_type', 'raw_material')
            ->where('name', trim($template->name))
            ->first();

        if ($byName) {
            return $byName;
        }

        return $this->productService->createProduct([
            'name' => trim($template->name),
            'category' => 'خامات تصنيع',
            'base_unit' => 'piece',
            'base_unit_label' => 'قطعة',
            'purchase_price' => $purchase,
            'selling_price' => $sale,
            'product_type' => 'raw_material',
            'is_manufactured' => false,
            'is_active' => true,
            'warehouses' => [
                [
                    'warehouse_id' => (int) $template->warehouse_id,
                    'quantity' => max(0.0, (float) $template->quantity),
                    'min_stock' => 0,
                ],
            ],
        ]);
    }

    private function syncPivot(Product $product, RawMaterialTemplate $template): void
    {
        $qty = max(0, round((float) $template->quantity, 3));
        $avg = max(0, round((float) $template->buy_price, 2));

        ProductWarehouse::query()->updateOrCreate(
            [
                'product_id' => $product->id,
                'warehouse_id' => (int) $template->warehouse_id,
            ],
            [
                'quantity' => $qty,
                'reserved_quantity' => 0,
                'average_cost' => $avg,
                'min_stock' => 10,
            ]
        );

        Log::info('مزامنة خامة مع المخزن', [
            'raw_material_template_id' => $template->id,
            'product_id' => $product->id,
            'warehouse_id' => $template->warehouse_id,
            'quantity' => $qty,
        ]);
    }
}
