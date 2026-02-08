<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBasePricing;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncProductBasePricing extends Command
{
    protected $signature = 'products:sync-base-pricing';
    protected $description = 'مزامنة product_base_pricing من products';

    public function handle()
    {
        $products = Product::whereDoesntHave('basePricing')
            ->where('is_active', true)
            ->get();

        if ($products->isEmpty()) {
            $this->info('✅ لا توجد منتجات تحتاج للمزامنة');
            return 0;
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            ProductBasePricing::create([
                'product_id' => $product->id,
                'base_unit' => $product->base_unit ?? 'UNIT',
                'base_purchase_price' => $product->purchase_price ?? 0,
                'base_selling_price' => $product->selling_price ?? 0,
                'profit_type' => 'fixed',
                'profit_value' => ($product->selling_price ?? 0) - ($product->purchase_price ?? 0),
                'is_active' => true,
                'is_current' => true,
                'created_by' => 1,
            ]);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ تمت المزامنة بنجاح!');
        return 0;
    }
}