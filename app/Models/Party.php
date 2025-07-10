<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credit_limit',
        'credit_days',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'credit_days' => 'integer',
    ];

    /**
     * Get the contacts for the party.
     */
    public function contacts()
    {
        return $this->hasMany(PartyContact::class);
    }

    /**
     * Get the primary contact for the party.
     */
    public function primaryContact()
    {
        return $this->hasOne(PartyContact::class)->where('is_primary', true);
    }

    /**
     * Get contacts by type.
     */
    public function getContactsByType($type)
    {
        return $this->contacts()->where('contact_type', $type)->get();
    }

    /**
     * Get phone contacts.
     */
    public function phoneContacts()
    {
        return $this->contacts()->where('contact_type', 'phone');
    }

    /**
     * Get email contacts.
     */
    public function emailContacts()
    {
        return $this->contacts()->where('contact_type', 'email');
    }
}
