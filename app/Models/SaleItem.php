<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'tare_wt',
        'gross_wt',
        'net_wt',
        'rate',
        'amount',
        'sort_order'
    ];

    protected $casts = [
        'tare_wt' => 'integer',
        'gross_wt' => 'integer',
        'net_wt' => 'decimal:3',
        'rate' => 'decimal:2',
        'amount' => 'decimal:2',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Boot method for auto-calculations
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($saleItem) {
            $saleItem->calculateNetWeight();
            $saleItem->calculateAmount();
        });

        static::saved(function ($saleItem) {
            // Update sale totals when item is saved
            $saleItem->sale->updateTotalsFromItems();
        });

        static::deleted(function ($saleItem) {
            // Update sale totals when item is deleted
            $saleItem->sale->updateTotalsFromItems();
        });
    }

    // Calculate net weight in tons
    public function calculateNetWeight()
    {
        if ($this->gross_wt && $this->tare_wt) {
            // Net weight in tons = (gross - tare) / 1000
            $this->net_wt = ($this->gross_wt - $this->tare_wt) / 1000;
        } else {
            $this->net_wt = 0;
        }
        return $this->net_wt;
    }

    // Calculate amount
    public function calculateAmount()
    {
        if ($this->net_wt && $this->rate) {
            $this->amount = $this->net_wt * $this->rate;
        } else {
            $this->amount = 0;
        }
        return $this->amount;
    }

    // Get the next sort order for this sale
    public static function getNextSortOrder($saleId)
    {
        $lastItem = self::where('sale_id', $saleId)
                       ->orderBy('sort_order', 'desc')
                       ->first();

        return $lastItem ? $lastItem->sort_order + 1 : 1;
    }

    // Get the tare weight for this item (previous item's gross weight or sale tare weight)
    public static function calculateTareWeight($saleId, $sortOrder = null)
    {
        $sale = Sale::find($saleId);
        if (!$sale) return 0;

        if (!$sortOrder) {
            $sortOrder = self::getNextSortOrder($saleId);
        }

        if ($sortOrder == 1) {
            // First item uses sale's tare weight
            return $sale->tare_wt;
        } else {
            // Subsequent items use previous item's gross weight
            $previousItem = self::where('sale_id', $saleId)
                               ->where('sort_order', $sortOrder - 1)
                               ->first();

            return $previousItem && $previousItem->gross_wt ? $previousItem->gross_wt : $sale->tare_wt;
        }
    }

    // Check if this item can be weighed (has tare weight and rate)
    public function canBeWeighed()
    {
        return $this->tare_wt > 0 && $this->rate > 0;
    }

    // Check if this item is completely filled
    public function isComplete()
    {
        return $this->tare_wt > 0 && $this->gross_wt > 0 && $this->rate > 0;
    }

    // Get the net weight in KG
    public function getNetWeightKgAttribute()
    {
        return $this->gross_wt && $this->tare_wt ? $this->gross_wt - $this->tare_wt : 0;
    }

    // Get formatted weight display
    public function getFormattedWeightAttribute()
    {
        if (!$this->isComplete()) {
            return 'Pending weighing';
        }
        return number_format($this->net_wt, 3) . ' tons (' . number_format($this->net_weight_kg) . ' kg)';
    }

    // Get formatted amount display
    public function getFormattedAmountAttribute()
    {
        return '₹' . number_format($this->amount, 2);
    }

    // Get status for display
    public function getStatusAttribute()
    {
        if (!$this->rate || $this->rate <= 0) {
            return 'Rate pending';
        }
        if (!$this->gross_wt || $this->gross_wt <= 0) {
            return 'Weighing pending';
        }
        return 'Complete';
    }

    // Get status badge
    public function getStatusBadgeAttribute()
    {
        $status = $this->status;
        $classes = [
            'Rate pending' => 'badge-warning',
            'Weighing pending' => 'badge-info',
            'Complete' => 'badge-success'
        ];

        $class = $classes[$status] ?? 'badge-secondary';
        return "<span class=\"badge {$class}\">{$status}</span>";
    }

    // Scope for items that need weighing
    public function scopeNeedsWeighing($query)
    {
        return $query->where(function($q) {
            $q->whereNull('gross_wt')->orWhere('gross_wt', 0);
        })->where('rate', '>', 0);
    }

    // Scope for complete items
    public function scopeComplete($query)
    {
        return $query->where('gross_wt', '>', 0)
                    ->where('tare_wt', '>', 0)
                    ->where('rate', '>', 0);
    }
}
