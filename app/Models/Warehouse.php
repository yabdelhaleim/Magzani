<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

    // ==================== Configuration ====================
    
    protected $fillable = [
        'name',
        'code',
        'status',
        'location',
        'address',
        'city',
        'area',
        'phone',
        'email',
        'manager_name',
        'manager_id',
        'description',
        'is_active',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
    ];

    // ==================== Relationships ====================

    public function productWarehouses(): HasMany
    {
        return $this->hasMany(ProductWarehouse::class, 'warehouse_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function transfersFrom(): HasMany
    {
        return $this->hasMany(WarehouseTransfer::class, 'from_warehouse_id');
    }

    public function transfersTo(): HasMany
    {
        return $this->hasMany(WarehouseTransfer::class, 'to_warehouse_id');
    }

    // ==================== Query Scopes ====================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * ✅ جلب الإحصائيات في استعلام واحد - محسّن للأداء
     */
    public function scopeWithStats(Builder $query): Builder
    {
        return $query->withCount('productWarehouses as products_count')
            ->addSelect([
                'total_quantity' => ProductWarehouse::selectRaw('COALESCE(SUM(quantity), 0)')
                    ->whereColumn('warehouse_id', 'warehouses.id'),
                
                'total_value' => ProductWarehouse::selectRaw('COALESCE(SUM(quantity * average_cost), 0)')
                    ->whereColumn('warehouse_id', 'warehouses.id'),
            ]);
    }

    public function scopeWithLowStockCount(Builder $query): Builder
    {
        return $query->addSelect([
            'low_stock_count' => ProductWarehouse::selectRaw('COUNT(*)')
                ->whereColumn('warehouse_id', 'warehouses.id')
                ->whereRaw('quantity <= min_stock'),
        ]);
    }

    public function scopeWithManager(Builder $query): Builder
    {
        return $query->with(['manager:id,name,email']);
    }

    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('updated_at', 'desc');
    }

    public function scopeForDashboard(Builder $query): Builder
    {
        return $query->active()
            ->withStats()
            ->withLowStockCount()
            ->withManager()
            ->latest();
    }

    // ==================== Accessors ====================

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'active' => 'نشط',
            'inactive' => 'متوقف',
            'maintenance' => 'صيانة',
            default => 'غير محدد'
        };
    }

    public function getFullAddressAttribute(): string
    {
        return trim(implode(', ', array_filter([
            $this->address,
            $this->area,
            $this->city,
        ])));
    }

    // ==================== Helper Methods ====================

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    public function hasManager(): bool
    {
        return !is_null($this->manager_id);
    }
}