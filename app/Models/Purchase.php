<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'datetime',
        'mines_id',
        'rec_no',
        'token_no',
        'vehicle_id',
        'gross_wt',
        'tare_wt',
        'net_wt',
        'wt_ton',
        'driver',
        'commission',
        'use_at',
        'notes'
    ];

    protected $casts = [
        'datetime' => 'datetime',
        'gross_wt' => 'integer',
        'tare_wt' => 'integer',
        'net_wt' => 'integer',
        'wt_ton' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($purchase) {
            // Auto calculate net weight
            if ($purchase->gross_wt && $purchase->tare_wt) {
                $purchase->net_wt = $purchase->gross_wt - $purchase->tare_wt;
                // Auto calculate weight in tons
                $purchase->wt_ton = round($purchase->net_wt / 1000, 2);
            }
        });
    }

    /**
     * Get the mines associated with the purchase.
     */
    public function mines()
    {
        return $this->belongsTo(Mines::class);
    }

    /**
     * Get the vehicle associated with the purchase.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get formatted gross weight.
     */
    public function getFormattedGrossWtAttribute()
    {
        return number_format($this->gross_wt) . ' kg';
    }

    /**
     * Get formatted tare weight.
     */
    public function getFormattedTareWtAttribute()
    {
        return number_format($this->tare_wt) . ' kg';
    }

    /**
     * Get formatted net weight.
     */
    public function getFormattedNetWtAttribute()
    {
        return number_format($this->net_wt) . ' kg';
    }

    /**
     * Get formatted weight in tons.
     */
    public function getFormattedWtTonAttribute()
    {
        return number_format($this->wt_ton, 2) . ' Tons';
    }

    /**
     * Get formatted commission.
     */
    public function getFormattedCommissionAttribute()
    {
        return $this->commission ? '₹ ' . number_format($this->commission, 2) : 'Not specified';
    }

    /**
     * Get use_at badge.
     */
    public function getUseAtBadgeAttribute()
    {
        $badges = [
            'stock' => '<span class="badge bg-info">Stock</span>',
            'manufacturing' => '<span class="badge bg-primary">Manufacturing</span>'
        ];

        return $badges[$this->use_at] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get purchase display name.
     */
    public function getDisplayNameAttribute()
    {
        return "Purchase #{$this->rec_no}";
    }

    /**
     * Scope for filtering by date range.
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('datetime', [$start, $end]);
    }

    /**
     * Scope for filtering by mines.
     */
    public function scopeByMines($query, $minesId)
    {
        return $query->where('mines_id', $minesId);
    }

    /**
     * Scope for filtering by vehicle.
     */
    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    /**
     * Scope for filtering by use_at.
     */
    public function scopeByUseAt($query, $useAt)
    {
        return $query->where('use_at', $useAt);
    }

    /**
     * Scope for search.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('rec_no', 'like', "%{$search}%")
              ->orWhere('token_no', 'like', "%{$search}%")
              ->orWhere('driver', 'like', "%{$search}%");
        });
    }
}
