<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Party extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'credit_limit',
        'credit_days',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
    ];

    /**
     * Define the many-to-many relationship with ledgers.
     */
    public function ledgers()
    {
        return $this->belongsToMany(Ledger::class, 'party_has_ledgers', 'party_id', 'ledger_id')
                    ->withTimestamps();
    }

    /**
     * Define the relationship with contacts.
     */
    public function contacts()
    {
        return $this->hasMany(PartyContact::class);
    }

    /**
     * Get ledgers summary with financial information.
     */
    public function getLedgersSummaryAttribute()
    {
        if ($this->ledgers->isEmpty()) {
            return [
                'total_payable' => 0,
                'total_receivable' => 0,
                'net_payable' => 0,
                'ledger_count' => 0
            ];
        }

        $totalPayable = 0;
        $totalReceivable = 0;

        foreach ($this->ledgers as $ledger) {
            $balance = $ledger->getCurrentBalance();

            if ($balance['type'] === 'credit') {
                // Credit balance means we owe them (payable)
                $totalPayable += $balance['balance'];
            } else {
                // Debit balance means they owe us (receivable)
                $totalReceivable += $balance['balance'];
            }
        }

        return [
            'total_payable' => $totalPayable,
            'total_receivable' => $totalReceivable,
            'net_payable' => $totalPayable - $totalReceivable,
            'ledger_count' => $this->ledgers->count()
        ];
    }

    /**
     * Get formatted display of ledgers with balances.
     */
    public function getLinkedLedgersDisplayAttribute(): string
    {
        if ($this->ledgers->isEmpty()) {
            return 'No linked ledgers';
        }

        $ledgerNames = $this->ledgers->map(function ($ledger) {
            $balance = $ledger->getCurrentBalance();
            $formattedBalance = number_format($balance['balance'], 2);
            $type = strtoupper($balance['type']);
            return "{$ledger->name} ({$type} {$formattedBalance})";
        });

        return implode(', ', $ledgerNames->toArray());
    }

    /**
     * Scope to filter parties with specific ledger.
     */
    public function scopeWithLedger($query, Ledger $ledger)
    {
        return $query->whereHas('ledgers', function ($q) use ($ledger) {
            $q->where('ledger_id', $ledger->id);
        });
    }

    /**
     * Check if party has a specific ledger linked.
     */
    public function hasLedger(Ledger $ledger): bool
    {
        return $this->ledgers()->where('ledger_id', $ledger->id)->exists();
    }

    /**
     * Link a ledger to this party.
     */
    public function linkLedger(Ledger $ledger): bool
    {
        if ($this->hasLedger($ledger)) {
            return false; // Already linked
        }

        $this->ledgers()->attach($ledger->id);
        return true;
    }

    /**
     * Unlink a ledger from this party.
     */
    public function unlinkLedger(Ledger $ledger): bool
    {
        if (!$this->hasLedger($ledger)) {
            return false; // Not linked
        }

        $this->ledgers()->detach($ledger->id);
        return true;
    }

    /**
     * Boot the model to add event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        // When deleting a party, all ledger relationships are automatically deleted
        // due to cascade delete in the migration
        static::deleting(function ($party) {
            // Any additional cleanup logic can go here
        });
    }

    /**
     * Activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'credit_limit', 'credit_days'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
