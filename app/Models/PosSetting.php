<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PosSetting extends Model
{
    use HasFactory;

    protected $table = 'pos_settings';

    protected $fillable = [
        'pos_name',
        'default_warehouse_id',
        'default_payment_method',
        'require_shift',
        'auto_print_receipt',
        'allow_negative_stock',
        'receipt_header_text',
        'receipt_footer_text',
    ];

    protected $casts = [
        'require_shift' => 'boolean',
        'auto_print_receipt' => 'boolean',
        'allow_negative_stock' => 'boolean',
    ];

    // ==================== Relationships ====================

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'default_warehouse_id');
    }

    // ==================== Singleton Pattern ====================

    /**
     * جلب إعدادات الكاشير الوحيدة للمستأجر، وإنشاء الافتراضية إن لم تكن موجودة.
     */
    public static function getSolo(): self
    {
        $settings = self::first();

        if (!$settings) {
            // نحاول العثور على أي مستودع نشط لتخصيصه كافتراضي
            $defaultWarehouseId = Warehouse::where('is_active', true)->orderBy('id')->value('id');

            $settings = self::create([
                'pos_name'               => 'الكاشير الرئيسي',
                'default_warehouse_id'   => $defaultWarehouseId,
                'default_payment_method' => 'cash',
                'require_shift'          => true,
                'auto_print_receipt'     => false,
                'allow_negative_stock'   => false,
                'receipt_header_text'    => 'أهلاً بكم في متجرنا',
                'receipt_footer_text'    => 'شكراً لزيارتكم! يرجى الاحتفاظ بالإيصال عند الاسترجاع.',
            ]);
        }

        return $settings;
    }
}
