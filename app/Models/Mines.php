<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mines extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ml_number',
        'mines_name',
        'owner_name',
        'email',
        'phone',
        'mobile',
        'status',
        'notes'
    ];

    /**
     * Get the mines's display name.
     */
    public function getDisplayNameAttribute()
    {
        if ($this->mines_name) {
            return "{$this->ml_number} - {$this->mines_name}";
        }
        return $this->ml_number;
    }

    /**
     * Get the mines's status badge.
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
     * Get the mines's contact information.
     */
    public function getContactInfoAttribute()
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

        return implode(' | ', $contacts) ?: 'No contact information';
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
     * Get the mines information with owner.
     */
    public function getMinesWithOwnerAttribute()
    {
        $info = [];

        if ($this->mines_name) {
            $info[] = $this->mines_name;
        }

        if ($this->owner_name) {
            $info[] = "Owner: {$this->owner_name}";
        }

        return implode(' - ', $info) ?: 'No mines information';
    }

    /**
     * Scope for active mines.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for mines by status.
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
            $q->where('ml_number', 'like', "%{$search}%")
              ->orWhere('owner_name', 'like', "%{$search}%")
              ->orWhere('mines_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%");
        });
    }
}
