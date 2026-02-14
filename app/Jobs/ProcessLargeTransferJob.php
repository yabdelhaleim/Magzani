<?php

namespace App\Jobs;

use App\Models\WarehouseTransfer;
use App\Services\TransferService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessLargeTransferJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * عدد المحاولات
     */
    public $tries = 3;

    /**
     * المهلة الزمنية (10 دقائق)
     */
    public $timeout = 600;

    /**
     * معرّف التحويل
     */
    protected int $transferId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $transferId)
    {
        $this->transferId = $transferId;
        $this->onQueue('transfers'); // قائمة انتظار مخصصة
    }

    /**
     * Execute the job.
     */
    public function handle(TransferService $transferService): void
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        Log::info('🚀 بدء معالجة التحويل الضخم في الخلفية', [
            'transfer_id' => $this->transferId,
            'job_id' => $this->job->getJobId(),
            'attempt' => $this->attempts()
        ]);

        try {
            DB::transaction(function () use ($transferService, &$startTime, &$startMemory) {
                
                $transfer = WarehouseTransfer::with('items')
                    ->lockForUpdate()
                    ->findOrFail($this->transferId);

                // التحقق من الحالة
                if ($transfer->status !== 'draft') {
                    throw new \Exception('التحويل تم معالجته مسبقاً');
                }

                // تحديث حالة المعالجة
                $transfer->update([
                    'status' => 'processing',
                    'processing_started_at' => now()
                ]);

                $totalItems = $transfer->items()->count();

                Log::info('📦 بدء معالجة المنتجات', [
                    'transfer_id' => $this->transferId,
                    'total_items' => $totalItems
                ]);

                // معالجة بالـ Chunks مع تتبع التقدم
                $processedCount = 0;
                $chunkSize = 500;

                $transfer->items()
                    ->with('product:id,name,code')
                    ->lazy($chunkSize)
                    ->chunk($chunkSize)
                    ->each(function ($chunk, $index) use ($transfer, $totalItems, &$processedCount, $chunkSize) {
                        
                        $chunkNumber = $index + 1;
                        $processedCount += $chunk->count();
                        $progress = round(($processedCount / $totalItems) * 100, 2);

                        // تحديث التقدم في الـ job
                        $this->updateProgress($processedCount, $totalItems);

                        Log::info("⚙️ معالجة Chunk {$chunkNumber}", [
                            'transfer_id' => $this->transferId,
                            'processed' => $processedCount,
                            'total' => $totalItems,
                            'progress' => $progress . '%',
                            'memory' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
                        ]);

                        // معالجة الـ chunk
                        $this->processChunk($transfer, $chunk);

                        // تحديث سجل التحويل بالتقدم
                        $transfer->update([
                            'processing_progress' => $progress,
                            'last_processed_at' => now()
                        ]);
                    });

                // تحديث حالة التحويل إلى مكتمل
                $transfer->update([
                    'status' => 'received',
                    'received_date' => now(),
                    'confirmed_by' => $transfer->created_by,
                    'confirmed_at' => now(),
                    'received_by' => $transfer->created_by,
                    'processing_completed_at' => now(),
                    'processing_progress' => 100
                ]);

                // تحديث quantity_received للعناصر
                $transfer->items()->chunkById(1000, function ($items) {
                    DB::table('warehouse_transfer_items')
                        ->whereIn('id', $items->pluck('id'))
                        ->update(['quantity_received' => DB::raw('quantity_sent')]);
                });

                $endTime = microtime(true);
                $endMemory = memory_get_usage(true);
                $duration = round($endTime - $startTime, 2);
                $memoryUsed = round(($endMemory - $startMemory) / 1024 / 1024, 2);

                Log::info('✅ اكتمال معالجة التحويل الضخم', [
                    'transfer_id' => $this->transferId,
                    'total_items' => $totalItems,
                    'duration' => $duration . ' seconds',
                    'memory_used' => $memoryUsed . ' MB',
                    'items_per_second' => round($totalItems / $duration, 2)
                ]);

                // مسح الكاش
                $this->clearCache($transfer);

                // إرسال إشعار للمستخدم (اختياري)
                // $this->notifyUser($transfer);
            });

        } catch (\Exception $e) {
            Log::error('❌ فشل معالجة التحويل الضخم', [
                'transfer_id' => $this->transferId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempt' => $this->attempts()
            ]);

            // تحديث حالة الفشل
            try {
                WarehouseTransfer::where('id', $this->transferId)
                    ->update([
                        'status' => 'failed',
                        'error_message' => $e->getMessage(),
                        'failed_at' => now()
                    ]);
            } catch (\Exception $updateError) {
                Log::error('فشل تحديث حالة الخطأ', [
                    'transfer_id' => $this->transferId,
                    'error' => $updateError->getMessage()
                ]);
            }

            // إعادة رمي الخطأ ليتم إعادة المحاولة
            throw $e;
        }
    }

    /**
     * معالجة chunk من المنتجات
     */
    private function processChunk(WarehouseTransfer $transfer, $items): void
    {
        $productIds = $items->pluck('product_id')->unique()->toArray();

        // جلب المخزون بـ Batch واحد
        $sourceStocks = DB::table('product_warehouse')
            ->where('warehouse_id', $transfer->from_warehouse_id)
            ->whereIn('product_id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');

        $destStocks = DB::table('product_warehouse')
            ->where('warehouse_id', $transfer->to_warehouse_id)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $movements = [];
        $now = now();

        foreach ($items as $item) {
            $productId = $item->product_id;
            $quantity = $item->quantity_sent;

            // التحقق من المخزون
            if (!isset($sourceStocks[$productId])) {
                throw new \Exception("المنتج {$item->product->name} غير موجود في المخزن المصدر");
            }

            if ($sourceStocks[$productId]->quantity < $quantity) {
                throw new \Exception("مخزون غير كافي للمنتج {$item->product->name}");
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
                DB::table('product_warehouse')->insert([
                    'product_id' => $productId,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'quantity' => $quantity,
                    'min_stock' => 10,
                    'reserved_quantity' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // تحضير الحركات
            $movements[] = [
                'warehouse_id' => $transfer->from_warehouse_id,
                'product_id' => $productId,
                'movement_type' => 'transfer_out',
                'quantity' => $quantity,
                'quantity_change' => -$quantity,
                'quantity_before' => $sourceStocks[$productId]->quantity,
                'quantity_after' => $sourceStocks[$productId]->quantity - $quantity,
                'notes' => "تحويل صادر #{$transfer->transfer_number}",
                'reference_type' => WarehouseTransfer::class,
                'reference_id' => $transfer->id,
                'movement_date' => $transfer->transfer_date,
                'movement_number' => 'MV' . $now->format('YmdHis') . $productId,
                'created_by' => $transfer->created_by,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $movements[] = [
                'warehouse_id' => $transfer->to_warehouse_id,
                'product_id' => $productId,
                'movement_type' => 'transfer_in',
                'quantity' => $quantity,
                'quantity_change' => $quantity,
                'quantity_before' => $destStocks[$productId]->quantity ?? 0,
                'quantity_after' => ($destStocks[$productId]->quantity ?? 0) + $quantity,
                'notes' => "تحويل وارد #{$transfer->transfer_number}",
                'reference_type' => WarehouseTransfer::class,
                'reference_id' => $transfer->id,
                'movement_date' => $transfer->transfer_date,
                'movement_number' => 'MV' . $now->format('YmdHis') . $productId . 'IN',
                'created_by' => $transfer->created_by,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // إدراج الحركات بـ bulk
        if (!empty($movements)) {
            collect($movements)->chunk(500)->each(function ($chunk) {
                DB::table('inventory_movements')->insert($chunk->toArray());
            });
        }
    }

    /**
     * تحديث التقدم
     */
    private function updateProgress(int $processed, int $total): void
    {
        // يمكن استخدام Redis أو Cache لتتبع التقدم في الوقت الفعلي
        cache()->put(
            "transfer_progress_{$this->transferId}",
            [
                'processed' => $processed,
                'total' => $total,
                'percentage' => round(($processed / $total) * 100, 2),
                'updated_at' => now()->toDateTimeString()
            ],
            now()->addHours(2)
        );
    }

    /**
     * مسح الكاش
     */
    private function clearCache(WarehouseTransfer $transfer): void
    {
        cache()->forget("warehouse_products_stock_{$transfer->from_warehouse_id}");
        cache()->forget("warehouse_products_stock_{$transfer->to_warehouse_id}");
        cache()->forget("warehouse_stats_{$transfer->from_warehouse_id}");
        cache()->forget("warehouse_stats_{$transfer->to_warehouse_id}");
        cache()->forget("transfer_progress_{$this->transferId}");
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('❌ فشل Job معالجة التحويل بعد كل المحاولات', [
            'transfer_id' => $this->transferId,
            'exception' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // تحديث حالة التحويل
        try {
            WarehouseTransfer::where('id', $this->transferId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $exception->getMessage(),
                    'failed_at' => now()
                ]);
        } catch (\Exception $e) {
            Log::error('فشل تحديث حالة الفشل النهائية', [
                'error' => $e->getMessage()
            ]);
        }

        // إرسال إشعار للإدارة (اختياري)
        // Notification::route('mail', 'admin@example.com')
        //     ->notify(new TransferFailedNotification($this->transferId, $exception));
    }
}