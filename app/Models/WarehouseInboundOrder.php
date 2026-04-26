<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInboundOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'warehouse_id',
        'order_date',
        'reference_number',
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
        return $this->hasMany(WarehouseInboundOrderItem::class, 'inbound_order_id');
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
        $prefix = 'IN-' . date('Y');
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

        static::created(function ($order) {
            // إنشاء حركة مخزون عند إنشاء الأمر
            foreach ($order->items as $item) {
                InventoryMovement::create([
                    'warehouse_id' => $order->warehouse_id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'movement_type' => 'inbound',
                    'reference_type' => 'WarehouseInboundOrder',
                    'reference_id' => $order->id,
                    'notes' => 'أذن إدخال بضاعة رقم: ' . $order->order_number,
                    'created_by' => $order->created_by,
                ]);
            }
        });
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
}
