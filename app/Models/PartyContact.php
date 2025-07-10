<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_id',
        'contact_type',
        'contact_value',
        'designation',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the party that owns the contact.
     */
    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Ensure only one primary contact per party
        static::saving(function ($contact) {
            if ($contact->is_primary) {
                static::where('party_id', $contact->party_id)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_primary' => false]);
            }
        });
    }
}
