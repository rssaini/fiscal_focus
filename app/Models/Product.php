<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'default_rate',
        'status'
    ];

    protected $casts = [
        'default_rate' => 'decimal:2',
        'status' => 'string'
    ];

    // Relationships
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Static method to get product options
    public static function getProductOptions()
    {
        return self::active()->pluck('name', 'id')->toArray();
    }
}
