<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosShift extends Model
{
    protected $table = 'pos_shifts';

    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'opening_balance',
        'closing_balance_actual',
        'closing_balance_expected',
        'difference',
        'total_sales',
        'total_returns',
        'sales_count',
        'returns_count',
        'net_sales',
        'expected_cash',
        'actual_cash',
        'cash_difference',
        'status',
        'notes',
        'journal_entry_id',
    ];

    /** قيم الحالة المسموحة */
    public const STATUS_OPEN        = 'open';
    public const STATUS_CLOSED      = 'closed';
    public const STATUS_AUTO_CLOSED = 'auto_closed';

    protected $casts = [
        'opened_at'               => 'datetime',
        'closed_at'               => 'datetime',
        'opening_balance'         => 'decimal:2',
        'closing_balance_actual'  => 'decimal:2',
        'closing_balance_expected'=> 'decimal:2',
        'difference'              => 'decimal:2',
        'total_sales'             => 'decimal:2',
        'total_returns'           => 'decimal:2',
        'net_sales'               => 'decimal:2',
        'expected_cash'           => 'decimal:2',
        'actual_cash'             => 'decimal:2',
        'cash_difference'         => 'decimal:2',
    ];

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class, 'shift_id');
    }

    public function salesReturns(): HasMany
    {
        return $this->hasMany(SalesReturn::class, 'shift_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    // ==================== Scopes ====================

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', [self::STATUS_CLOSED, self::STATUS_AUTO_CLOSED]);
    }

    public function scopeAutoClosed($query)
    {
        return $query->where('status', self::STATUS_AUTO_CLOSED);
    }

    // ==================== Helpers ====================

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_AUTO_CLOSED]);
    }

    public function isAutoClosed(): bool
    {
        return $this->status === self::STATUS_AUTO_CLOSED;
    }

    /**
     * جلب الوردية المفتوحة للمستخدم الحالي (اليوم فقط).
     */
    public static function getActiveShift(): ?self
    {
        return self::where('user_id', auth()->id())
            ->where('status', self::STATUS_OPEN)
            ->latest('opened_at')
            ->first();
    }

    /**
     * إغلاق تلقائي لأي وردية مفتوحة من يوم سابق للمستخدم الحالي.
     * يُستدعى قبل فتح وردية جديدة.
     */
    public static function autoCloseStaleShifts(?int $userId = null): void
    {
        $userId ??= auth()->id();

        self::where('user_id', $userId)
            ->where('status', self::STATUS_OPEN)
            ->whereDate('opened_at', '<', today())
            ->get()
            ->each(function (self $shift) {
                $shift->update([
                    'status'     => self::STATUS_AUTO_CLOSED,
                    'closed_at'  => now(),
                    'notes'      => trim(($shift->notes ?? '') . "\nأُغلقت تلقائياً عند فتح وردية جديدة."),
                ]);
            });
    }

    /**
     * حساب الرصيد المتوقع عند الإغلاق.
     */
    public function calculateExpectedBalance(): float
    {
        return (float) $this->opening_balance
            + (float) $this->total_sales
            - (float) $this->total_returns;
    }

    /**
     * تحديث إحصائيات الوردية بعد كل فاتورة.
     */
    public function recalculateTotals(): void
    {
        $salesData = $this->salesInvoices()
            ->where('status', 'confirmed')
            ->where('source', 'pos')
            ->selectRaw('SUM(total) as total_sum, COUNT(*) as count_num')
            ->first();

        $returnsData = $this->salesReturns()
            ->where('status', 'confirmed')
            ->selectRaw('SUM(total) as total_sum, COUNT(*) as count_num')
            ->first();

        $totalSales = $salesData->total_sum ?? 0;
        $totalReturns = $returnsData->total_sum ?? 0;
        $netSales = $totalSales - $totalReturns;

        $this->update([
            'total_sales'   => $totalSales,
            'sales_count'   => $salesData->count_num ?? 0,
            'total_returns' => $totalReturns,
            'returns_count' => $returnsData->count_num ?? 0,
            'net_sales'     => $netSales,
        ]);
    }

    /**
     * حساب وحفظ فرق الصندوق (الفعلي − المتوقع).
     * يُستدعى عند إغلاق الوردية.
     */
    public function computeAndSaveDifference(): void
    {
        $actual   = (float) $this->closing_balance_actual;
        $expected = (float) $this->closing_balance_expected;

        $this->difference = $actual - $expected;
        
        $this->actual_cash = $actual;
        $this->expected_cash = $expected;
        $this->cash_difference = $actual - $expected;

        $this->save();
    }

    /**
     * مدة الوردية المنسقة.
     */
    public function getDurationAttribute(): string
    {
        $end = $this->closed_at ?? now();
        $diff = $this->opened_at->diff($end);
        return sprintf('%d ساعة %d دقيقة', $diff->h + ($diff->days * 24), $diff->i);
    }
}
