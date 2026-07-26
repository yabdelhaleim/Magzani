<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Expense extends Model
{
    /**
     * 🐛 Bug Fix: تم تحديث fillable ليتطابق مع الأعمدة الفعلية في DB:
     *  - expense_number (unique, was missing)
     *  - expense_category_id (FK, replaced 'category')
     *  - reference (replaced 'reference_number')
     *  - attachment (string, was missing)
     *  - created_by (FK to users, was missing)
     *  - حذف payment_method (غير موجود في DB)
     */
    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'amount',
        'expense_date',
        'description',
        'reference',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * العلاقة مع تصنيف المصروف.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * العلاقة مع المستخدم الذي أنشأ المصروف.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * العلاقة مع حركة النقدية (morphOne).
     */
    public function cashTransaction(): MorphOne
    {
        return $this->morphOne(CashTransaction::class, 'reference');
    }

    /**
     * عرض اسم التصنيف بالعربية (للتوافق مع الكود القديم).
     * يقرأ من العلاقة category.name.
     */
    public function getTypeAttribute(): string
    {
        $categories = [
            'rent' => 'إيجار',
            'salaries' => 'رواتب',
            'utilities' => 'مرافق',
            'maintenance' => 'صيانة',
            'supplies' => 'مستلزمات',
            'marketing' => 'تسويق ودعاية',
            'transportation' => 'مواصلات',
            'communication' => 'اتصالات',
            'insurance' => 'تأمينات',
            'taxes' => 'ضرائب ورسوم',
            'other' => 'أخرى',
        ];

        // يقرأ اسم التصنيف من العلاقة
        $categoryName = $this->category?->name ?? $this->getRawOriginal('expense_category_id');

        return $categories[$categoryName] ?? $categoryName ?? '-';
    }

    /**
     * Backward compatibility: get date formatted (was .date attribute).
     */
    public function getDateAttribute()
    {
        return $this->expense_date;
    }

    /**
     * Backward compatibility: get notes from description.
     */
    public function getNotesAttribute()
    {
        return $this->description;
    }

    /**
     * Backward compatibility: get reference_number from reference.
     */
    public function getReferenceNumberAttribute()
    {
        return $this->reference;
    }

    /**
     * توليد رقم مصروف فريد تلقائياً.
     */
    public static function generateNumber(): string
    {
        $prefix = 'EXP';
        $date = now()->format('Ymd');
        $lastId = (self::max('id') ?? 0) + 1;

        return sprintf('%s-%s-%05d', $prefix, $date, $lastId);
    }

    /**
     * عند الإنشاء: إذا لم يُحدد رقم المصروف، نولّد واحداً تلقائياً.
     */
    protected static function booted(): void
    {
        static::creating(function (Expense $expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = self::generateNumber();
            }
        });
    }
}
