<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'date',
        'consignee_id',
        'ref_party_id',
        'vehicle_no',
        'tare_wt',
        'gross_wt',
        'net_wt',
        'wt_ton',
        'subtotal',
        'discount_amount',
        'driver_commission',
        'tp_no',
        'invoice_rate',
        'tp_wt',
        'tax_amount',
        'total_amount',
        'rec_no',
        'royalty_book_no',
        'royalty_receipt_no',
        'royalty_wt',
        'status',
        'consignee_name',
        'consignee_address',
        'notes'
    ];

    protected $casts = [
        'date' => 'datetime',
        'tare_wt' => 'integer',
        'gross_wt' => 'integer',
        'net_wt' => 'integer',
        'wt_ton' => 'decimal:3',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'driver_commission' => 'decimal:2',
        'invoice_rate' => 'decimal:2',
        'tp_wt' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'status' => 'string'
    ];

    // Relationships
    public function consignee()
    {
        return $this->belongsTo(Consignee::class);
    }

    public function refParty()
    {
        return $this->belongsTo(Party::class, 'ref_party_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class)->orderBy('sort_order');
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    // Boot method for auto-calculations
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($sale) {
            $sale->updateTotalsFromItems();
        });
        static::updated(function ($sale) {
            if($sale->status == 'confirmed' && $sale->tp_no && !$sale->invoice_number) {
                $sale->invoice_number = self::generateInvoiceNumber();
                $sale->saveQuietly();
            }
        });
    }

    // Update sale totals from sale items
    public function updateTotalsFromItems()
    {
        $items = $this->items;

        if ($items->count() > 0) {
            // Get the latest item's gross weight as sale gross weight
            $latestItem = $items->sortByDesc('sort_order')->first();
            $this->gross_wt = $latestItem->gross_wt;

            // Calculate net weight and subtotal
            $this->net_wt = $this->gross_wt ? $this->gross_wt - $this->tare_wt : null;
            $this->wt_ton = $this->net_wt ? $this->net_wt / 1000 : null;
            $this->subtotal = $items->sum('amount');
            if($this->tp_wt && $this->invoice_rate) {
                $this->tax_amount = $this->tp_wt * $this->invoice_rate * 5 / 100; // Assuming 5% GST
            } else {
                $this->tax_amount = 0 ;
            }

            $this->total_amount = $this->subtotal + $this->tax_amount - $this->discount_amount;

            // Save without triggering events
            $this->saveQuietly();
        }
    }

    // Generate invoice number
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "JBBSC/25-26/";

        $lastInvoice = self::where('invoice_number', 'like', $prefix.'%')
                          ->orderBy('invoice_number', 'desc')
                          ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $newNumber;
    }

    public function lastItemName(){
        return $this->items()->orderBy('sort_order', 'desc')->first()->product->name;
    }

    // Get next tare weight for new product
    public function getNextTareWeight()
    {
        $lastItem = $this->items()->orderBy('sort_order', 'desc')->first();
        return $lastItem && $lastItem->gross_wt ? $lastItem->gross_wt : $this->tare_wt;
    }

    // Check if sale can add more products
    public function canAddProducts()
    {
        if($this->status == 'pending'){
             $canAdd = true;
            foreach($this->items as $item){
                if ($canAdd && !$item->gross_wt) {
                    $canAdd = false;
                }
            }
            return $canAdd;
        }
        return false;
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

    // Update payment status
    public function updatePaymentStatus()
    {
        if ($this->isFullyPaid()) {
            $this->status = 'paid';
        } elseif ($this->isPartiallyPaid()) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'confirmed';
        }
        $this->saveQuietly();
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByConsignee($query, $consigneeId)
    {
        return $query->where('consignee_id', $consigneeId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute()
    {
        return '₹' . number_format($this->total_amount, 2);
    }

    public function getFormattedWeightAttribute()
    {
        return $this->wt_ton ? number_format($this->wt_ton, 3) . ' tons' : 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        $classes = [
            'draft' => 'badge-warning',
            'confirmed' => 'badge-info',
            'paid' => 'badge-success',
            'partially_paid' => 'badge-primary',
            'cancelled' => 'badge-danger'
        ];

        $class = $classes[$this->status] ?? 'badge-secondary';
        return "<span class=\"badge {$class}\">" . ucfirst(str_replace('_', ' ', $this->status)) . "</span>";
    }
}
