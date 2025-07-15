<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'name',
        'designation',
        'email',
        'phone',
        'mobile',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the vehicle that owns the contact.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one primary contact per vehicle
        static::saving(function ($contact) {
            if ($contact->is_primary) {
                static::where('vehicle_id', $contact->vehicle_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Get the contact's display information.
     */
    public function getDisplayContactAttribute()
    {
        $contacts = [];

        if ($this->email) {
            $contacts[] = "Email: {$this->email}";
        }

        if ($this->phone) {
            $contacts[] = "Phone: {$this->phone}";
        }

        if ($this->mobile) {
            $contacts[] = "Mobile: {$this->mobile}";
        }

        return implode(' | ', $contacts);
    }

    /**
     * Get primary contact method.
     */
    public function getPrimaryContactMethodAttribute()
    {
        if ($this->mobile) return $this->mobile;
        if ($this->phone) return $this->phone;
        if ($this->email) return $this->email;
        return 'No contact info';
    }

    /**
     * Get the contact's full information.
     */
    public function getFullContactInfoAttribute()
    {
        $info = $this->name;

        if ($this->designation) {
            $info .= " ({$this->designation})";
        }

        if ($this->display_contact) {
            $info .= " - {$this->display_contact}";
        }

        return $info;
    }
}
