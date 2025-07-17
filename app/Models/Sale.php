<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'date',
        'customer_id',
        'ref_party_id',
        'vehicle_no',
        'tare_wt',
        'gross_wt',
        'product_id',
        'product_rate',
        'net_wt',
        'wt_ton',
        'amount',
        'tp_no',
        'invoice_rate',
        'tp_wt',
        'cgst',
        'sgst',
        'total_gst',
        'total_amount',
        'rec_no',
        'royalty_book_no',
        'royalty_receipt_no',
        'status',
        'consignee_name',
        'consignee_address',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
        'tare_wt' => 'integer',
        'gross_wt' => 'integer',
        'product_rate' => 'decimal:2',
        'net_wt' => 'integer',
        'wt_ton' => 'decimal:3',
        'amount' => 'decimal:2',
        'invoice_rate' => 'decimal:2',
        'tp_wt' => 'decimal:2',
        'cgst' => 'decimal:2',
        'sgst' => 'decimal:2',
        'total_gst' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'status' => 'string'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function refParty()
    {
        return $this->belongsTo(Party::class, 'ref_party_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    // Calculated Fields & Mutators
    public function calculateNetWeight()
    {
        if ($this->gross_wt && $this->tare_wt) {
            $this->net_wt = $this->gross_wt - $this->tare_wt;
            return $this->net_wt;
        }
        return 0;
    }

    public function calculateWeightInTons()
    {
        if ($this->net_wt) {
            $this->wt_ton = $this->net_wt / 1000;
            return $this->wt_ton;
        }
        return 0;
    }

    public function calculateAmount()
    {
        if ($this->wt_ton && $this->product_rate) {
            $this->amount = $this->wt_ton * $this->product_rate;
            return $this->amount;
        }
        return 0;
    }

    public function calculateGST()
    {
        if ($this->invoice_rate && $this->tp_wt) {
            $gstBase = $this->invoice_rate * $this->tp_wt;
            $this->cgst = $gstBase * 0.025; // 2.5%
            $this->sgst = $gstBase * 0.025; // 2.5%
            $this->total_gst = $this->cgst + $this->sgst;
            return $this->total_gst;
        }
        return 0;
    }

    public function calculateTotalAmount()
    {
        if ($this->amount && $this->total_gst !== null) {
            $this->total_amount = $this->amount + $this->total_gst;
            return $this->total_amount;
        }
        return $this->amount ?: 0;
    }

    // Auto-calculate fields before saving
    public static function boot()
    {
        parent::boot();

        static::saving(function ($sale) {
            $sale->calculateNetWeight();
            $sale->calculateWeightInTons();
            $sale->calculateAmount();
            $sale->calculateGST();
            $sale->calculateTotalAmount();
        });
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['confirmed', 'partially_paid']);
    }

    // Payment related methods
    public function getTotalPaidAmount()
    {
        return $this->payments()->where('status', 'cleared')->sum('amount');
    }

    public function getRemainingAmount()
    {
        return $this->total_amount - $this->getTotalPaidAmount();
    }

    public function isFullyPaid()
    {
        return $this->getRemainingAmount() <= 0;
    }

    public function isPartiallyPaid()
    {
        $paid = $this->getTotalPaidAmount();
        return $paid > 0 && $paid < $this->total_amount;
    }

    // Update payment status based on payments
    public function updatePaymentStatus()
    {
        if ($this->isFullyPaid()) {
            $this->status = 'paid';
        } elseif ($this->isPartiallyPaid()) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'confirmed';
        }
        $this->save();
    }

    // Generate invoice number
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "INV-{$year}{$month}-";

        $lastInvoice = self::where('invoice_number', 'like', $prefix.'%')
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
