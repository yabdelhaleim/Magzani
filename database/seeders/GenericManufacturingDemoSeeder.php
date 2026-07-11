<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use App\Models\UomConversion;
use App\Models\ComponentCategory;
use App\Models\MaterialBatch;
use App\Models\ManufacturingCost;
use App\Models\BomComponent;
use App\Models\User;
use Illuminate\Database\Seeder;

class GenericManufacturingDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get or Create Warehouse
        $warehouse = Warehouse::first() ?: Warehouse::create([
            'name' => 'المخزن الرئيسي',
            'code' => 'WH-001',
            'is_active' => true,
        ]);

        // 2. Get or Create Supplier
        $supplier = Supplier::first() ?: Supplier::create([
            'name' => 'شركة النيل للأخشاب والمواد الخام',
            'code' => 'SUPP-NILE',
            'phone' => '01099999999',
            'is_active' => true,
        ]);

        // 3. Create Units of Measure
        $m3 = UnitOfMeasure::firstOrCreate(
            ['code' => 'm3'],
            ['name' => 'متر مكعب', 'type' => 'volume', 'is_active' => true]
        );

        $board = UnitOfMeasure::firstOrCreate(
            ['code' => 'board'],
            ['name' => 'لوح خشب', 'type' => 'volume', 'is_active' => true]
        );

        $piece = UnitOfMeasure::firstOrCreate(
            ['code' => 'piece'],
            ['name' => 'قطعة', 'type' => 'count', 'is_active' => true]
        );

        // 4. Create UoM Conversion: 1 board = 0.024 m3
        UomConversion::firstOrCreate(
            ['from_uom_id' => $board->id, 'to_uom_id' => $m3->id],
            ['factor' => 0.024]
        );

        // 5. Create Component Categories
        $woodCategory = ComponentCategory::firstOrCreate(
            ['name_ar' => 'أخشاب ومواد هيكلية'],
            ['name_en' => 'Wood & Structural Materials']
        );

        $packCategory = ComponentCategory::firstOrCreate(
            ['name_ar' => 'مواد تعبئة وتغليف'],
            ['name_en' => 'Packaging Materials']
        );

        // 6. Create Raw Material Product
        $rawProduct = Product::firstOrCreate(
            ['code' => 'RAW-MOSKY'],
            [
                'name' => 'لوح خشب موسكي 120×10×2 سم',
                'sku' => 'MOSKY-BOARD',
                'product_type' => 'raw_material',
                'base_unit' => 'm3',
                'purchase_price' => 1000.00, // per m3
                'selling_price' => 1200.00,
                'is_active' => true,
            ]
        );

        // 7. Create Target Manufactured Product
        $manufacturedProduct = Product::firstOrCreate(
            ['code' => 'PROD-PALLET-STD'],
            [
                'name' => 'طبلية خشبية قياسية مصنعة',
                'sku' => 'PALLET-STD-01',
                'product_type' => 'manufactured',
                'is_manufactured' => true,
                'base_unit' => 'piece',
                'purchase_price' => 410.00,
                'selling_price' => 600.00,
                'is_active' => true,
            ]
        );

        // 8. Create Material Batch: 500 boards at cost 24.00 per board
        $batch = MaterialBatch::firstOrCreate(
            ['product_id' => $rawProduct->id, 'warehouse_id' => $warehouse->id, 'uom_id' => $board->id],
            [
                'quantity' => 500,
                'remaining_qty' => 500,
                'unit_cost' => 24.00, // 0.024 * 1000
                'supplier_id' => $supplier->id,
                'purchase_reference' => 'PO-2026-001',
                'received_at' => '2026-07-01',
            ]
        );

        // 9. Create BOM Recipe (ManufacturingCost)
        $user = User::first();
        $bom = ManufacturingCost::firstOrCreate(
            ['product_id' => $manufacturedProduct->id],
            [
                'product_name' => $manufacturedProduct->name,
                'material_cost' => 360.00, // 15 boards * 24.00
                'total_cost' => 360.00,
                'profit_percentage' => 40.00,
                'profit_amount' => 144.00,
                'final_price' => 504.00,
                'status' => 'confirmed',
                'created_by' => $user?->id ?? 1,
            ]
        );

        // 10. Add Component to BOM
        BomComponent::firstOrCreate(
            ['manufacturing_cost_id' => $bom->id, 'component_product_id' => $rawProduct->id],
            [
                'component_name' => 'لوح خشب موسكي',
                'quantity' => 15.00,
                'uom_id' => $board->id,
                'cost_per_uom' => 24.00,
                'component_category_id' => $woodCategory->id,
                'sort_order' => 1,
            ]
        );
    }
}
