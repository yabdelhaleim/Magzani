<?php

namespace App\Traits;

use App\Models\StockCount;
use App\Models\StockCountItem;
use Illuminate\Support\Facades\DB;

trait ManagesStockCountStatus
{
    /**
     * التحقق من إمكانية تغيير حالة الجرد
     */
    protected function canChangeStatus(StockCount $stockCount, string $targetStatus): bool
    {
        // قواعد الانتقال بين الحالات
        $allowedTransitions = [
            'draft' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [], // لا يمكن تغيير الحالة
            'cancelled' => [], // لا يمكن تغيير الحالة
        ];

        return in_array(
            $targetStatus,
            $allowedTransitions[$stockCount->status] ?? []
        );
    }

    /**
     * التحقق من صحة حالة الجرد قبل التنفيذ
     */
    protected function validateStockCountStatus(StockCount $stockCount, string $requiredStatus): void
    {
        if ($stockCount->status !== $requiredStatus) {
            throw new \Exception(
                "❌ الجرد يجب أن يكون في حالة '{$requiredStatus}' وليس '{$stockCount->status}'"
            );
        }
    }

    /**
     * التحقق من عدم وجود جرد نشط للمخزن
     */
    protected function validateNoActiveCount(int $warehouseId): void
    {
        $exists = StockCount::where('warehouse_id', $warehouseId)
            ->whereIn('status', ['draft', 'in_progress'])
            ->exists();

        if ($exists) {
            throw new \Exception(
                '❌ يوجد جرد نشط بالفعل لهذا المخزن. يجب إكماله أو إلغاؤه أولاً'
            );
        }
    }

    /**
     * التحقق من اكتمال الجرد
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
     * التحقق من وجود موافقات على الفروقات
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
     * تغيير حالة الجرد بشكل آمن
     */
    protected function changeStockCountStatus(
        StockCount $stockCount,
        string $newStatus,
        array $additionalData = []
    ): void {
        if (!$this->canChangeStatus($stockCount, $newStatus)) {
            throw new \Exception(
                "❌ لا يمكن تغيير حالة الجرد من '{$stockCount->status}' إلى '{$newStatus}'"
            );
        }

        $updateData = array_merge(['status' => $newStatus], $additionalData);
        
        $stockCount->update($updateData);
    }
}