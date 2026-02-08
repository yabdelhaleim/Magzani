<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CashTransaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transaction_number',
        'transaction_type',
        'amount',
        'description',
        'transaction_date',
        'category',
        'reference',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Constants for transaction types
     */
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';

    /**
     * Get available transaction types
     *
     * @return array
     */
    public static function getTransactionTypes(): array
    {
        return [
            self::TYPE_DEPOSIT => 'إيداع',
            self::TYPE_WITHDRAWAL => 'سحب',
        ];
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate transaction number if not provided
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = self::generateTransactionNumber($transaction->transaction_type);
            }
        });
    }

    /**
     * Generate unique transaction number
     *
     * @param string $type
     * @return string
     */
    public static function generateTransactionNumber(string $type): string
    {
        $prefix = $type === self::TYPE_DEPOSIT ? 'DEP' : 'WTH';
        $date = now()->format('Ymd');
        
        $lastTransaction = self::where('transaction_number', 'like', $prefix . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }

    // ==================== Relationships ====================

    /**
     * Get the user who created this transaction
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ==================== Scopes ====================

    /**
     * Scope for deposits only
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDeposits(Builder $query): Builder
    {
        return $query->where('transaction_type', self::TYPE_DEPOSIT);
    }

    /**
     * Scope for withdrawals only
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithdrawals(Builder $query): Builder
    {
        return $query->where('transaction_type', self::TYPE_WITHDRAWAL);
    }

    /**
     * Scope for date range
     *
     * @param Builder $query
     * @param string|Carbon $startDate
     * @param string|Carbon|null $endDate
     * @return Builder
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate = null): Builder
    {
        $query->where('transaction_date', '>=', $startDate);
        
        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Scope for specific category
     *
     * @param Builder $query
     * @param string $category
     * @return Builder
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for today's transactions
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * Scope for this month's transactions
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereYear('transaction_date', now()->year)
                    ->whereMonth('transaction_date', now()->month);
    }

    // ==================== Accessors ====================

    /**
     * Get transaction type name in Arabic
     *
     * @return string
     */
    public function getTransactionTypeNameAttribute(): string
    {
        return self::getTransactionTypes()[$this->transaction_type] ?? $this->transaction_type;
    }

    /**
     * Get formatted amount
     *
     * @return string
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount, 2) . ' ريال';
    }

    /**
     * Get formatted date
     *
     * @return string
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->transaction_date->format('Y-m-d');
    }

    // ==================== Mutators ====================

    /**
     * Set amount (ensure it's positive)
     *
     * @param mixed $value
     * @return void
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = abs($value);
    }

    // ==================== Helper Methods ====================

    /**
     * Check if transaction is a deposit
     *
     * @return bool
     */
    public function isDeposit(): bool
    {
        return $this->transaction_type === self::TYPE_DEPOSIT;
    }

    /**
     * Check if transaction is a withdrawal
     *
     * @return bool
     */
    public function isWithdrawal(): bool
    {
        return $this->transaction_type === self::TYPE_WITHDRAWAL;
    }

    /**
     * Get signed amount (positive for deposits, negative for withdrawals)
     *
     * @return float
     */
    public function getSignedAmount(): float
    {
        return $this->isDeposit() ? $this->amount : -$this->amount;
    }
}