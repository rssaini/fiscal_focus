<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'designation',
        'email',
        'phone',
        'mobile',
        'is_primary',
        'notes'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Methods
    public function getContactInfoAttribute()
    {
        $info = [];
        if ($this->email) $info[] = $this->email;
        if ($this->mobile) $info[] = $this->mobile;
        if ($this->phone) $info[] = $this->phone;

        return implode(' | ', $info);
    }
}
