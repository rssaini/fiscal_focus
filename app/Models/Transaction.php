<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'ledger_id',
        'uuid',
        'transaction_date',
        'particular',
        'debit',
        'credit',
        'running_balance',
        'running_balance_type',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'running_balance' => 'decimal:2'
    ];

    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }
}
