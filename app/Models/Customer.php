<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'company_name',
        'customer_type',
        'email',
        'phone',
        'mobile',
        'website',
        'gstin',
        'pan',
        'billing_address',
        'shipping_address',
        'city',
        'state',
        'country',
        'pincode',
        'status',
        'credit_limit',
        'credit_days',
        'opening_balance',
        'opening_balance_type',
        'opening_date',
        'ledger_id',
        'notes',
        'additional_fields'
    ];

    protected $casts = [
        'opening_date' => 'date',
        'opening_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'additional_fields' => 'array'
    ];

    // Relationships
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    public function contacts()
    {
        return $this->hasMany(CustomerContact::class);
    }

    public function primaryContact()
    {
        return $this->hasOne(CustomerContact::class)->where('is_primary', true);
    }

    public function documents()
    {
        return $this->hasMany(CustomerDocument::class);
    }

    public function transactions()
    {
        return $this->hasManyThrough(Transaction::class, Ledger::class, 'id', 'ledger_id', 'ledger_id', 'id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('customer_code', 'like', "%{$search}%")
              ->orWhere('company_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%");
        });
    }

    // Methods
    public function generateCustomerCode()
    {
        $prefix = $this->customer_type === 'business' ? 'CUST' : 'IND';
        $year = now()->format('Y');

        $lastCustomer = static::where('customer_code', 'like', "{$prefix}{$year}%")
            ->orderBy('customer_code', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastNumber = intval(substr($lastCustomer->customer_code, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getDisplayNameAttribute()
    {
        if ($this->customer_type === 'business' && $this->company_name) {
            return $this->company_name . ' (' . $this->name . ')';
        }
        return $this->name;
    }

    public function getFullAddressAttribute()
    {
        return $this->billing_address . ', ' . $this->city . ', ' . $this->state . ' - ' . $this->pincode;
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-warning">Inactive</span>',
            'blocked' => '<span class="badge bg-danger">Blocked</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    public function getCurrentBalance()
    {
        if (!$this->ledger) {
            return [
                'balance' => $this->opening_balance,
                'type' => $this->opening_balance_type
            ];
        }

        return $this->ledger->getCurrentBalance();
    }

    public function getOutstandingAmount()
    {
        $balance = $this->getCurrentBalance();

        // For customers, debit balance means they owe us money (outstanding)
        if ($balance['type'] === 'debit') {
            return $balance['balance'];
        }

        return 0; // Credit balance means they have advance payment
    }

    public function getAdvanceAmount()
    {
        $balance = $this->getCurrentBalance();

        // For customers, credit balance means advance payment
        if ($balance['type'] === 'credit') {
            return $balance['balance'];
        }

        return 0;
    }

    public function isOverCreditLimit()
    {
        if ($this->credit_limit <= 0) {
            return false;
        }

        return $this->getOutstandingAmount() > $this->credit_limit;
    }

    public function getOverdueAmount()
    {
        if (!$this->ledger) {
            return 0;
        }

        $cutoffDate = now()->subDays($this->credit_days);

        $overdueTransactions = $this->ledger->transactions()
            ->where('transaction_date', '<', $cutoffDate)
            ->where('debit', '>', 0) // Only debit transactions (invoices)
            ->get();

        return $overdueTransactions->sum('debit');
    }

    public function getCreditUtilizationPercentage()
    {
        if ($this->credit_limit <= 0) {
            return 0;
        }

        return ($this->getOutstandingAmount() / $this->credit_limit) * 100;
    }

    public function getRecentTransactions($limit = 10)
    {
        if (!$this->ledger) {
            return collect();
        }

        return $this->ledger->transactions()
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get();
    }

    public function createLedger()
    {
        if ($this->ledger_id) {
            return $this->ledger;
        }

        // Find or create Accounts Receivable chart of account
        $accountsReceivable = \App\Models\ChartOfAccount::where('account_code', '1200')
            ->orWhere('account_name', 'Accounts Receivable')
            ->first();

        if (!$accountsReceivable) {
            throw new \Exception('Accounts Receivable chart of account not found. Please create it first.');
        }

        $ledger = \App\Models\Ledger::create([
            'name' => $this->display_name,
            'folio' => $this->customer_code,
            'opening_date' => $this->opening_date,
            'opening_balance' => $this->opening_balance,
            'balance_type' => $this->opening_balance_type,
            'chart_of_account_id' => $accountsReceivable->id,
            'is_active' => $this->status === 'active'
        ]);

        $this->update(['ledger_id' => $ledger->id]);

        return $ledger;
    }
}
