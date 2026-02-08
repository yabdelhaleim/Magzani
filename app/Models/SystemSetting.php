<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'default_currency',
        'date_format',
        'default_tax',
        'rows_per_page',
        'low_stock_alert',
        'allow_negative_stock',
        'confirm_before_delete',
        'auto_invoice_number',
        'auto_print_invoice',
        'auto_email_invoice',
    ];
}
