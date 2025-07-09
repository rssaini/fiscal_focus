<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CustomerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'document_type',
        'document_name',
        'file_path',
        'file_size',
        'mime_type',
        'expiry_date',
        'description'
    ];

    protected $casts = [
        'expiry_date' => 'date'
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Methods
    public function getFileSizeHumanAttribute()
    {
        if (!$this->file_size) return 'Unknown';

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && $this->expiry_date->isBefore(now()->addDays($days));
    }
}
