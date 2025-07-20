<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consignee extends Model
{
    use HasFactory;

    protected $fillable = [
        'consignee_name',
        'gstin',
        'address',
        'address2',
        'city',
        'state',
        'zip',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Scope a query to only include active consignees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive consignees.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the full address.
     */
    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->address2) {
            $address .= ', ' . $this->address2;
        }
        $address .= ', ' . $this->city . ', ' . $this->state . ' - ' . $this->zip;

        return $address;
    }

    /**
     * Get the status badge.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>'
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    /**
     * Get formatted GSTIN.
     */
    public function getFormattedGstinAttribute()
    {
        return $this->gstin ? strtoupper($this->gstin) : 'Not Provided';
    }

    /**
     * Get consignee display name.
     */
    public function getDisplayNameAttribute()
    {
        return $this->consignee_name . ' (' . $this->city . ')';
    }

    /**
     * Relationship with sales.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'consignee_id');
    }

    /**
     * Get sales count.
     */
    public function getSalesCountAttribute()
    {
        return $this->sales()->count();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($consignee) {
            // Uppercase GSTIN
            if ($consignee->gstin) {
                $consignee->gstin = strtoupper($consignee->gstin);
            }

            // Capitalize names and addresses
            $consignee->consignee_name = ucwords(strtolower($consignee->consignee_name));
            $consignee->city = ucwords(strtolower($consignee->city));
            $consignee->state = ucwords(strtolower($consignee->state));
        });
    }
}
