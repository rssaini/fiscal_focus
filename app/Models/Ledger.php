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
        'chart_of_account_id',
        'folio',
        'is_active',
        'opening_date',
        'opening_balance',
        'balance_type',
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Define the relationship with chart of accounts.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Define the relationship with transactions.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Define the many-to-many relationship with parties.
     */
    public function parties()
    {
        return $this->belongsToMany(Party::class, 'party_has_ledgers', 'ledger_id', 'party_id')
                    ->withTimestamps();
    }

    /**
     * Get current balance of the ledger.
     */
    public function getCurrentBalance()
    {
        $latestTransaction = $this->transactions()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$latestTransaction) {
            return [
                'balance' => $this->opening_balance,
                'type' => $this->balance_type
            ];
        }

        return [
            'balance' => $latestTransaction->running_balance,
            'type' => $latestTransaction->running_balance_type
        ];
    }

    /**
     * Get opening balance for a specific date.
     */
    public function getOpeningBalanceForDate(Carbon $date)
    {
        if ($date->lessThanOrEqualTo($this->opening_date)) {
            return [
                'balance' => $this->opening_balance,
                'type' => $this->balance_type
            ];
        }

        $previousTransaction = $this->transactions()
            ->where('transaction_date', '<', $date)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$previousTransaction) {
            return [
                'balance' => $this->opening_balance,
                'type' => $this->balance_type
            ];
        }

        return [
            'balance' => $previousTransaction->running_balance,
            'type' => $previousTransaction->running_balance_type
        ];
    }

    /**
     * Check if ledger is linked to a specific party.
     */
    public function isLinkedToParty(Party $party): bool
    {
        return $this->parties()->where('party_id', $party->id)->exists();
    }

    /**
     * Link this ledger to a party.
     */
    public function linkToParty(Party $party): bool
    {
        if ($this->isLinkedToParty($party)) {
            return false; // Already linked
        }

        $this->parties()->attach($party->id);
        return true;
    }

    /**
     * Unlink this ledger from a party.
     */
    public function unlinkFromParty(Party $party): bool
    {
        if (!$this->isLinkedToParty($party)) {
            return false; // Not linked
        }

        $this->parties()->detach($party->id);
        return true;
    }

    /**
     * Scope for active ledgers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ledgers with specific chart of account type.
     */
    public function scopeByAccountType($query, string $accountType)
    {
        return $query->whereHas('chartOfAccount', function ($q) use ($accountType) {
            $q->where('account_type', $accountType);
        });
    }
}
