<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseOutboundOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'warehouse_id',
        'order_date',
        'reference_number',
        'purpose',
        'recipient_name',
        'notes',
        'status',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(WarehouseOutboundOrderItem::class, 'outbound_order_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generateOrderNumber()
    {
        $prefix = 'OUT-' . date('Y');
        $lastOrder = self::where('order_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -6);
            $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '000001';
        }

        return $prefix . '-' . $newNumber;
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = $order->generateOrderNumber();
            }
        });

        // خصم المخزون وتسجيل الحركات يتم من WarehouseOrderController@outboundApprove
        // عبر InventoryMovementService لتفادي قيم movement_type / أعمدة غير متوافقة مع الجدول.
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">معلق</span>',
            'completed' => '<span class="badge bg-success">مكتمل</span>',
            'cancelled' => '<span class="badge bg-danger">ملغي</span>',
            default => '<span class="badge bg-secondary">' . $this->status . '</span>',
        };
    }

    public function getPurposeTextAttribute()
    {
        return match($this->purpose) {
            'sale' => 'بيع',
            'transfer' => 'تحويل',
            'return' => 'مرتجع',
            'damage' => 'تالف',
            'sample' => 'عينة',
            'other' => 'أخرى',
            default => $this->purpose,
        };
    }
}
