<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number',
        'voucher_type',
        'voucher_date',
        'reference_number',
        'narration',
        'total_amount',
        'status',
        'created_by',
        'approved_by',
        'posted_at',
        'remarks',
        'attachments'
    ];

    protected $casts = [
        'voucher_date' => 'date',
        'posted_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'attachments' => 'array'
    ];

    // Relationships
    public function entries()
    {
        return $this->hasMany(VoucherEntry::class)->orderBy('sort_order');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'uuid', 'voucher_number');
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('voucher_type', $type);
    }

    public function scopePosted($query)
    {
        return $query->where('status', 'posted');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('voucher_date', [$startDate, $endDate]);
    }

    // Methods
    public function generateVoucherNumber()
    {
        $prefix = $this->getVoucherPrefix();
        $date = $this->voucher_date->format('Y');

        $lastVoucher = static::where('voucher_type', $this->voucher_type)
            ->where('voucher_number', 'like', "{$prefix}{$date}%")
            ->orderBy('voucher_number', 'desc')
            ->first();

        if ($lastVoucher) {
            $lastNumber = intval(substr($lastVoucher->voucher_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    private function getVoucherPrefix()
    {
        return match($this->voucher_type) {
            'journal' => 'JV',
            'payment' => 'PV',
            'receipt' => 'RV',
            'contra' => 'CV',
            default => 'GV'
        };
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }

    public function canBePosted()
    {
        return $this->status === 'draft' && $this->isBalanced();
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['draft', 'posted']);
    }

    public function isBalanced()
    {
        $totalDebit = $this->entries->sum('debit');
        $totalCredit = $this->entries->sum('credit');
        return abs($totalDebit - $totalCredit) < 0.01;
    }

    public function getTotalDebitAttribute()
    {
        return $this->entries->sum('debit');
    }

    public function getTotalCreditAttribute()
    {
        return $this->entries->sum('credit');
    }

    public function getVoucherTypeDisplayAttribute()
    {
        return match($this->voucher_type) {
            'journal' => 'Journal Voucher',
            'payment' => 'Payment Voucher',
            'receipt' => 'Receipt Voucher',
            'contra' => 'Contra Voucher',
            default => 'General Voucher'
        };
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'draft' => '<span class="badge bg-warning">Draft</span>',
            'posted' => '<span class="badge bg-success">Posted</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    public function post()
    {
        if (!$this->canBePosted()) {
            throw new \Exception('Voucher cannot be posted');
        }

        \DB::transaction(function () {
            // Create transactions from voucher entries
            foreach ($this->entries as $entry) {
                $this->createTransaction($entry);
            }

            // Update voucher status
            $this->update([
                'status' => 'posted',
                'posted_at' => now(),
                'approved_by' => auth()->id()
            ]);
        });
    }

    private function createTransaction($entry)
    {
        $ledger = $entry->ledger;

        // Calculate running balance
        $runningBalance = $this->calculateRunningBalance($ledger, $entry->debit, $entry->credit);

        Transaction::create([
            'ledger_id' => $entry->ledger_id,
            'uuid' => $this->voucher_number,
            'transaction_date' => $this->voucher_date,
            'particular' => $entry->particular,
            'debit' => $entry->debit,
            'credit' => $entry->credit,
            'running_balance' => $runningBalance['balance'],
            'running_balance_type' => $runningBalance['type'],
            'notes' => "Voucher: {$this->voucher_number} - {$this->narration}"
        ]);

        // Update subsequent running balances
        $this->updateSubsequentBalances($ledger);
    }

    private function calculateRunningBalance($ledger, $debit, $credit)
    {
        $lastTransaction = $ledger->transactions()
            ->where('transaction_date', '<=', $this->voucher_date)
            ->where('uuid', '!=', $this->voucher_number)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastTransaction) {
            $currentBalance = $lastTransaction->running_balance;
            $currentType = $lastTransaction->running_balance_type;
        } else {
            $currentBalance = $ledger->opening_balance;
            $currentType = $ledger->balance_type;
        }

        // Calculate new balance
        if ($currentType == 'debit') {
            $newBalance = $currentBalance + $debit - $credit;
        } else {
            $newBalance = $currentBalance - $debit + $credit;
        }

        $newType = $newBalance >= 0 ? $currentType : ($currentType == 'debit' ? 'credit' : 'debit');
        $newBalance = abs($newBalance);

        return [
            'balance' => $newBalance,
            'type' => $newType
        ];
    }

    private function updateSubsequentBalances($ledger)
    {
        $subsequentTransactions = $ledger->transactions()
            ->where('transaction_date', '>', $this->voucher_date)
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $runningBalance = $ledger->opening_balance;
        $runningType = $ledger->balance_type;

        // Recalculate all balances from the beginning
        $allTransactions = $ledger->transactions()
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        foreach ($allTransactions as $transaction) {
            if ($runningType == 'debit') {
                $runningBalance = $runningBalance + $transaction->debit - $transaction->credit;
            } else {
                $runningBalance = $runningBalance - $transaction->debit + $transaction->credit;
            }

            if ($runningBalance < 0) {
                $runningType = $runningType == 'debit' ? 'credit' : 'debit';
                $runningBalance = abs($runningBalance);
            }

            $transaction->update([
                'running_balance' => $runningBalance,
                'running_balance_type' => $runningType
            ]);
        }
    }
}
