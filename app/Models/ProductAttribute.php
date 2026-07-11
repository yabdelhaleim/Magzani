<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ProductAttribute extends Model
{
    use HasFactory;

    protected $table = 'product_attributes';

    protected $fillable = [
        'attributable_type',
        'attributable_id',
        'attribute_key',
        'attribute_value',
        'value_type',
    ];

    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get value cast to its type
     */
    public function getTypedValueAttribute()
    {
        return match ($this->value_type) {
            'decimal', 'double', 'float' => (float) $this->attribute_value,
            'integer', 'int' => (int) $this->attribute_value,
            'boolean', 'bool' => filter_var($this->attribute_value, FILTER_VALIDATE_BOOLEAN),
            default => $this->attribute_value,
        };
    }
}
