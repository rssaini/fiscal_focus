<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Ledger extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'folio',
        'is_active',
        'opening_date',
        'opening_balance',
        'balance_type',
        'chart_of_account_id'
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    // ... existing methods remain the same ...

    public function getOpeningBalanceForDate($date)
    {
        $startDate = Carbon::parse($date)->startOfMonth();
        $previousMonth = $startDate->copy()->subMonth();

        // If the date is before ledger opening date, return opening balance
        if ($startDate->lt($this->opening_date)) {
            return [
                'balance' => $this->opening_balance,
                'type' => $this->balance_type
            ];
        }

        // Get last transaction before the start date
        $lastTransaction = $this->transactions()
            ->where('transaction_date', '<', $startDate)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            return [
                'balance' => $lastTransaction->running_balance,
                'type' => $lastTransaction->running_balance_type
            ];
        }

        return [
            'balance' => $this->opening_balance,
            'type' => $this->balance_type
        ];
    }

    public function getCurrentBalance()
    {
        $lastTransaction = $this->transactions()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            return [
                'balance' => $lastTransaction->running_balance,
                'type' => $lastTransaction->running_balance_type
            ];
        }

        return [
            'balance' => $this->opening_balance,
            'type' => $this->balance_type
        ];
    }

    // Get suggested balance type based on chart of account
    public function getSuggestedBalanceType()
    {
        if ($this->chartOfAccount) {
            return $this->chartOfAccount->normal_balance;
        }
        return 'debit'; // default
    }

    // Get account type display
    public function getAccountTypeDisplay()
    {
        if ($this->chartOfAccount) {
            return ucfirst(str_replace('_', ' ', $this->chartOfAccount->account_type));
        }
        return 'N/A';
    }
}
