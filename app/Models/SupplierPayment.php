<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierPayment extends Model
{
    use HasFactory; 
    protected $fillable = [
        'supplier_id','payment_date','amount','method','notes','journal_entry_id'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
