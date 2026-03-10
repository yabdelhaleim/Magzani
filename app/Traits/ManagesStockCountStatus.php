<?php

namespace App\Traits;

use App\Models\StockCount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 🎯 Trait لإدارة حالات الجرد
 *
 * يُستخدم في: StockCountController
 */
trait ManagesStockCountStatus
{
    /**
     * قواعد الانتقال المسموح بها بين الحالات
     */
    protected array $allowedTransitions = [
        'draft'       => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed'   => [],  // نهائية - لا تغيير
        'cancelled'   => [],  // نهائية - لا تغيير
        'reversed'    => [],  // نهائية - لا تغيير
    ];

    /**
     * هل يمكن تغيير الحالة؟
     */
    protected function canChangeStatus(StockCount $stockCount, string $targetStatus): bool
    {
        $allowed = $this->allowedTransitions[$stockCount->status] ?? [];
        return in_array($targetStatus, $allowed);
    }

    /**
     * التحقق من حالة الجرد - يرمي Exception إذا كانت غلط
     */
    protected function validateStockCountStatus(StockCount $stockCount, string $requiredStatus): void
    {
        if ($stockCount->status !== $requiredStatus) {
            throw new \Exception(
                "❌ الجرد يجب أن يكون في حالة '{$this->getStatusLabel($requiredStatus)}' وليس '{$this->getStatusLabel($stockCount->status)}'"
            );
        }
    }

    /**
     * التحقق من عدم وجود جرد نشط لنفس المخزن
     */
    protected function validateNoActiveCount(int $warehouseId, ?int $excludeId = null): void
    {
        $query = StockCount::where('warehouse_id', $warehouseId)
            ->whereIn('status', ['draft', 'in_progress']);

        // ✅ استثناء جرد معين (مفيد عند التعديل)
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \Exception(
                '❌ يوجد جرد نشط بالفعل لهذا المخزن. يجب إكماله أو إلغاؤه أولاً'
            );
        }
    }

    /**
     * التحقق من اكتمال جرد كل المنتجات قبل الإغلاق
     */
    protected function validateCountComplete(StockCount $stockCount): void
    {
        $pendingCount = $stockCount->items()
            ->where('status', 'pending')
            ->count();

        if ($pendingCount > 0) {
            throw new \Exception(
                "❌ يوجد {$pendingCount} منتج لم يتم جرده بعد. يجب إكمال الجرد أولاً"
            );
        }
    }

    /**
     * هل كل الفروقات تم الموافقة عليها؟
     */
    protected function validateApprovalsExist(StockCount $stockCount): bool
    {
        $unapprovedCount = $stockCount->items()
            ->where('variance', '!=', 0)
            ->where('adjustment_approved', false)
            ->count();

        return $unapprovedCount === 0;
    }

    /**
     * عدد الفروقات غير المعتمدة
     */
    protected function getPendingApprovalsCount(StockCount $stockCount): int
    {
        return $stockCount->items()
            ->where('variance', '!=', 0)
            ->where('adjustment_approved', false)
            ->count();
    }

    /**
     * تغيير حالة الجرد بشكل آمن مع تسجيل
     */
    protected function changeStockCountStatus(
        StockCount $stockCount,
        string $newStatus,
        array $additionalData = []
    ): void {
        if (!$this->canChangeStatus($stockCount, $newStatus)) {
            throw new \Exception(
                "❌ لا يمكن تغيير حالة الجرد من '{$this->getStatusLabel($stockCount->status)}' إلى '{$this->getStatusLabel($newStatus)}'"
            );
        }

        $oldStatus = $stockCount->status;

        $updateData = array_merge(
            ['status' => $newStatus, 'updated_at' => now()],
            $additionalData
        );

        // ✅ إضافة timestamps للحالات الخاصة
        if ($newStatus === 'in_progress' && empty($additionalData['started_at'])) {
            $updateData['started_at'] = now();
        }

        if ($newStatus === 'completed' && empty($additionalData['completed_at'])) {
            $updateData['completed_at'] = now();
        }

        if ($newStatus === 'cancelled' && empty($additionalData['cancelled_at'])) {
            $updateData['cancelled_at'] = now();
        }

        $stockCount->update($updateData);

        Log::info("StockCount #{$stockCount->id}: تم تغيير الحالة من '{$oldStatus}' إلى '{$newStatus}'");
    }

    /**
     * ✅ تغيير حالة الجرد داخل transaction (آمن أكثر)
     */
    protected function changeStockCountStatusInTransaction(
        StockCount $stockCount,
        string $newStatus,
        array $additionalData = [],
        ?\Closure $callback = null
    ): void {
        DB::transaction(function () use ($stockCount, $newStatus, $additionalData, $callback) {
            $this->changeStockCountStatus($stockCount, $newStatus, $additionalData);

            // تنفيذ أي منطق إضافي (مثل تعديل المخزون)
            if ($callback) {
                $callback($stockCount);
            }
        });
    }

    /**
     * الحصول على تسمية الحالة بالعربي
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'draft'       => 'مسودة',
            'in_progress' => 'جاري الجرد',
            'completed'   => 'مكتمل',
            'cancelled'   => 'ملغي',
            'reversed'    => 'معكوس',
            default       => $status,
        };
    }

    /**
     * الحصول على كل الانتقالات المتاحة من الحالة الحالية
     */
    protected function getAvailableTransitions(StockCount $stockCount): array
    {
        $transitions = $this->allowedTransitions[$stockCount->status] ?? [];

        return array_map(fn($s) => [
            'status' => $s,
            'label'  => $this->getStatusLabel($s),
        ], $transitions);
    }

    /**
     * ✅ التحقق الشامل قبل إتمام الجرد
     */
    protected function validateBeforeComplete(StockCount $stockCount): void
    {
        // 1. الحالة الصحيحة
        $this->validateStockCountStatus($stockCount, 'in_progress');

        // 2. كل المنتجات تم جردها
        $this->validateCountComplete($stockCount);

        // 3. تحذير لو في فروقات غير معتمدة (لكن لا نوقف العملية)
        $pendingApprovals = $this->getPendingApprovalsCount($stockCount);
        if ($pendingApprovals > 0) {
            Log::warning(
                "StockCount #{$stockCount->id}: إتمام الجرد مع وجود {$pendingApprovals} فرق غير معتمد"
            );
        }
    }
}