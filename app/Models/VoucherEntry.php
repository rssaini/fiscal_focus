<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'ledger_id',
        'particular',
        'debit',
        'credit',
        'narration',
        'sort_order'
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2'
    ];

    // Relationships
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    // Methods
    public function getAmountAttribute()
    {
        return $this->debit > 0 ? $this->debit : $this->credit;
    }

    public function getTypeAttribute()
    {
        return $this->debit > 0 ? 'debit' : 'credit';
    }
}
