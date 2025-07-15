<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vehicle_number',
        'tare_weight',
        'load_capacity',
        'status',
        'notes'
    ];

    protected $casts = [
        'tare_weight' => 'decimal:2',
        'load_capacity' => 'decimal:2',
    ];

    /**
     * Get all contacts for this vehicle.
     */
    public function contacts()
    {
        return $this->hasMany(VehicleContact::class);
    }

    /**
     * Get the primary contact for this vehicle.
     */
    public function primaryContact()
    {
        return $this->hasOne(VehicleContact::class)->where('is_primary', true);
    }

    /**
     * Get the vehicle's display name.
     */
    public function getDisplayNameAttribute()
    {
        return $this->vehicle_number;
    }

    /**
     * Get the vehicle's status badge.
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
     * Get gross weight (tare + load capacity) in kg.
     */
    public function getGrossWeightAttribute()
    {
        if ($this->tare_weight && $this->load_capacity) {
            return $this->tare_weight + $this->load_capacity;
        }
        return null;
    }

    /**
     * Get formatted tare weight.
     */
    public function getFormattedTareWeightAttribute()
    {
        return $this->tare_weight ? number_format($this->tare_weight, 2) . ' kg' : 'Not specified';
    }

    /**
     * Get formatted load capacity.
     */
    public function getFormattedLoadCapacityAttribute()
    {
        return $this->load_capacity ? number_format($this->load_capacity, 2) . ' kg' : 'Not specified';
    }

    /**
     * Get formatted gross weight.
     */
    public function getFormattedGrossWeightAttribute()
    {
        return $this->gross_weight ? number_format($this->gross_weight, 2) . ' kg' : 'Not calculated';
    }

    /**
     * Get the vehicle's contact information.
     */
    public function getContactInfoAttribute()
    {
        $primary = $this->primaryContact;

        if ($primary) {
            return $primary->display_contact;
        }

        $firstContact = $this->contacts()->first();
        return $firstContact ? $firstContact->display_contact : 'No contact information';
    }

    /**
     * Scope for active vehicles.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for vehicles by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for search.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('vehicle_number', 'like', "%{$search}%");
        });
    }
}
