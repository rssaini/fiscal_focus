<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'payment_reference',
        'payment_date',
        'payment_method',
        'amount',
        'transaction_id',
        'cheque_number',
        'cheque_date',
        'bank_name',
        'notes',
        'status'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'amount' => 'decimal:2',
        'payment_method' => 'string',
        'status' => 'string'
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    // Scopes
    public function scopeCleared($query)
    {
        return $query->where('status', 'cleared');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // Generate payment reference
    public static function generatePaymentReference()
    {
        $date = date('Ymd');
        $prefix = "PAY-{$date}-";

        $lastPayment = self::where('payment_reference', 'like', $prefix.'%')
                          ->orderBy('payment_reference', 'desc')
                          ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_reference, strlen($prefix));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    // Update sale payment status after payment is saved
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($payment) {
            $payment->sale->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            $payment->sale->updatePaymentStatus();
        });
    }

    // Payment method options
    public static function getPaymentMethodOptions()
    {
        return [
            'cash' => 'Cash',
            'upi' => 'UPI',
            'rtgs' => 'RTGS',
            'neft' => 'NEFT',
            'cheque' => 'Cheque',
            'card' => 'Card',
            'discount' => 'Discount',
            'adjustment' => 'Adjustment'
        ];
    }
}
