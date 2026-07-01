<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
        'is_active',
        'color',
        'icon',
        'sort_order',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==================== Relationships ====================

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== Accessors ====================

    /**
     * الألوان المتاحة للاختيار
     */
    public static function availableColors(): array
    {
        return [
            '#6366f1', // بنفسجي (افتراضي)
            '#3b82f6', // أزرق
            '#10b981', // أخضر
            '#f59e0b', // ذهبي
            '#ef4444', // أحمر
            '#8b5cf6', // بنفسجي غامق
            '#ec4899', // وردي
            '#14b8a6', // تيل
            '#f97316', // برتقالي
            '#64748b', // رمادي
        ];
    }

    /**
     * الأيقونات المتاحة للاختيار
     */
    public static function availableIcons(): array
    {
        return [
            'fa-tag'            => 'تاج / عام',
            'fa-burger'         => 'وجبات',
            'fa-mug-hot'        => 'مشروبات ساخنة',
            'fa-wine-glass'     => 'مشروبات',
            'fa-cake-candles'   => 'حلويات',
            'fa-pizza-slice'    => 'بيتزا',
            'fa-fish'           => 'أسماك',
            'fa-carrot'         => 'خضروات',
            'fa-apple-whole'    => 'فواكه',
            'fa-box'            => 'صناديق',
            'fa-shirt'          => 'ملابس',
            'fa-shoe-prints'    => 'أحذية',
            'fa-mobile-screen'  => 'إلكترونيات',
            'fa-tv'             => 'أجهزة',
            'fa-baby'           => 'أطفال',
            'fa-spa'            => 'عناية',
            'fa-dumbbell'       => 'رياضة',
            'fa-book'           => 'كتب',
            'fa-graduation-cap' => 'تعليم',
            'fa-tools'          => 'أدوات',
            'fa-star'           => 'مميز',
            'fa-fire'           => 'عروض',
        ];
    }
}
