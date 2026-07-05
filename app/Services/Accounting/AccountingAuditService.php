<?php

namespace App\Services\Accounting;

use App\Models\AccountingAuditLog;
use Illuminate\Support\Collection;

class AccountingAuditService
{
    /**
     * Get paginated audit logs with optional filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getLogs(array $filters = [])
    {
        $query = AccountingAuditLog::with('user')
            ->orderByDesc('id');

        if (!empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from'] . ' 00:00:00');
        }

        if (!empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to'] . ' 23:59:59');
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->paginate(25);
    }
}
