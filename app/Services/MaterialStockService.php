<?php

namespace App\Services;

use App\Models\MaterialBatch;
use App\Models\MaterialDispensing;
use App\Models\ManufacturingOrder;
use App\Models\Warehouse;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaterialStockService
{
    public function __construct(
        private QuantityCalculationService $calculationService,
        private InventoryMovementService $inventoryService,
        private ProductService $productService
    ) {}

    public function createStock(array $data): MaterialBatch
    {
        return DB::transaction(function () use ($data) {
            $warehouseId = $data['warehouse_id'] ?? null;
            if (!$warehouseId) {
                $warehouse = Warehouse::where('is_active', true)->first();
                $warehouseId = $warehouse?->id;
                $data['warehouse_id'] = $warehouseId;
            }

            // Set initial remaining qty
            $data['remaining_qty'] = $data['quantity'];

            // Create material batch
            $batch = MaterialBatch::create($data);

            // Record inventory movement
            $product = Product::find($batch->product_id);
            $supplierName = $batch->supplier->name ?? 'غير محدد';
            
            $this->inventoryService->recordMovement([
                'warehouse_id' => $warehouseId,
                'product_id' => $batch->product_id,
                'movement_type' => 'material_in',
                'quantity_change' => $batch->quantity,
                'unit_cost' => $batch->unit_cost,
                'unit_price' => $batch->unit_cost,
                'reference_type' => MaterialBatch::class,
                'reference_id' => $batch->id,
                'notes' => "استلام مواد خام من {$supplierName} - {$batch->purchase_reference}",
                'created_by' => auth()->id(),
            ]);

            return $batch->fresh(['warehouse', 'product', 'inventoryMovements']);
        });
    }

    public function dispense(MaterialBatch $batch, array $data): MaterialDispensing
    {
        // Capture the batch id for use inside the closure (the model
        // instance itself can become stale; we re-fetch a locked copy
        // inside the transaction).
        $batchId = $batch->id;

        return DB::transaction(function () use ($batchId, $data) {
            // Pessimistic-lock the batch row FIRST so concurrent dispenses
            // serialize at the database level. Without this, two TXs that
            // each read remaining_qty=10 could both decrement to 9
            // (lost update) or, worse, both succeed when only one had
            // enough stock.
            $locked = MaterialBatch::where('id', $batchId)
                ->lockForUpdate()
                ->firstOrFail();

            $qtyTaken = (float) $data['quantity_taken'];

            if ((float) $locked->remaining_qty < $qtyTaken) {
                throw ValidationException::withMessages([
                    'quantity_taken' => 'الكمية المطلوبة تتجاوز الكمية المتبقية في هذه الدفعة.',
                ]);
            }

            // Update remaining qty on the LOCKED row.
            $locked->decrement('remaining_qty', $qtyTaken);

            $data['user_id'] = auth()->id() ?? 1;

            $dispensing = $locked->dispensings()->create($data);

            // Record inventory movement
            $this->inventoryService->recordMovement([
                'warehouse_id' => $locked->warehouse_id,
                'product_id' => $locked->product_id,
                'movement_type' => 'material_out',
                'quantity_change' => -$qtyTaken,
                'unit_cost' => $locked->unit_cost,
                'unit_price' => $locked->unit_cost,
                'reference_type' => MaterialDispensing::class,
                'reference_id' => $dispensing->id,
                'notes' => $data['notes'] ?? 'صرف مواد خام للتصنيع',
                'created_by' => auth()->id(),
            ]);

            return $dispensing;
        });
    }

    public function getStockSummary(): array
    {
        $batches = MaterialBatch::all();

        return [
            'total_qty' => $batches->sum('quantity'),
            'remaining_qty' => $batches->sum('remaining_qty'),
            'batches_count' => $batches->count(),
        ];
    }

    public function getStockForOrder(ManufacturingOrder $order): Collection
    {
        return MaterialBatch::withStock()->get();
    }
}
