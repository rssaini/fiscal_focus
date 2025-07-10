<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EntityManagement extends Model
{
    use HasFactory;

    protected $table = 'entity_management';

    protected $fillable = [
        'head_name',
        'chart_of_account_id',
        'voucher_type',
        'ledger_id',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Head name constants
    const HEAD_CUSTOMERS = 'customers';
    const HEAD_EMPLOYEES = 'employees';
    const HEAD_PARTNERS = 'partners';
    const HEAD_VENDORS = 'vendors';
    const HEAD_SUPPLIERS = 'suppliers';

    // Voucher type constants
    const VOUCHER_SALE = 'sale';
    const VOUCHER_EXPENSE = 'expense';
    const VOUCHER_PURCHASE = 'purchase';
    const VOUCHER_RECEIPT = 'receipt';
    const VOUCHER_PAYMENT = 'payment';
    const VOUCHER_JOURNAL = 'journal';

    /**
     * Get the chart of account associated with this entity management.
     */
    public function chartOfAccount()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Get the ledger associated with this entity management.
     */
    public function ledger()
    {
        return $this->belongsTo(Ledger::class);
    }

    /**
     * Get all available head names.
     */
    public static function getHeadNames(): array
    {
        return [
            self::HEAD_CUSTOMERS => 'Customers',
            self::HEAD_EMPLOYEES => 'Employees',
            self::HEAD_PARTNERS => 'Partners',
            self::HEAD_VENDORS => 'Vendors',
            self::HEAD_SUPPLIERS => 'Suppliers',
        ];
    }

    /**
     * Get all available voucher types.
     */
    public static function getVoucherTypes(): array
    {
        return [
            self::VOUCHER_SALE => 'Sale',
            self::VOUCHER_EXPENSE => 'Expense',
            self::VOUCHER_PURCHASE => 'Purchase',
            self::VOUCHER_RECEIPT => 'Receipt',
            self::VOUCHER_PAYMENT => 'Payment',
            self::VOUCHER_JOURNAL => 'Journal',
        ];
    }

    /**
     * Get the head name display.
     */
    public function getHeadNameDisplayAttribute(): string
    {
        return self::getHeadNames()[$this->head_name] ?? ucfirst($this->head_name);
    }

    /**
     * Get the voucher type display.
     */
    public function getVoucherTypeDisplayAttribute(): string
    {
        return self::getVoucherTypes()[$this->voucher_type] ?? ucfirst($this->voucher_type);
    }

    /**
     * Scope a query to only include active entity management records.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by head name.
     */
    public function scopeByHeadName(Builder $query, string $headName): Builder
    {
        return $query->where('head_name', $headName);
    }

    /**
     * Scope a query to filter by voucher type.
     */
    public function scopeByVoucherType(Builder $query, string $voucherType): Builder
    {
        return $query->where('voucher_type', $voucherType);
    }

    /**
     * Get the accounting head for entity creation.
     * This determines under which chart of account the entity's ledger should be created.
     */
    public static function getEntityCreationHead(string $headName): ?self
    {
        return self::active()
            ->byHeadName($headName)
            ->first();
    }

    /**
     * Get the ledger for voucher posting.
     * This determines which ledger should be used when posting vouchers.
     */
    public static function getVoucherLedger(string $headName, string $voucherType): ?self
    {
        return self::active()
            ->byHeadName($headName)
            ->byVoucherType($voucherType)
            ->first();
    }

    /**
     * Get all entity management records grouped by head name.
     */
    public static function getRecordsByHead(): array
    {
        return self::active()
            ->with(['chartOfAccount', 'ledger'])
            ->get()
            ->groupBy('head_name')
            ->toArray();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure head_name is lowercase
        static::saving(function ($entityManagement) {
            $entityManagement->head_name = strtolower($entityManagement->head_name);
            $entityManagement->voucher_type = strtolower($entityManagement->voucher_type);
        });
    }
}
