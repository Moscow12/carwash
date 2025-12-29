<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class sales extends Model
{
    use HasUuids;

    protected $table = 'sales';

    protected $fillable = [
        'carwash_id',
        'sale_status',
        'sale_type',
        'sale_date',
        'payment_date',
        'notes',
        'receipt_type',
        'payment_type',
        'customer_id',
        'user_id',
        'total_amount',
        'payment_status',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime',
        'payment_date' => 'date',
    ];

    // Relationships
    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function customer()
    {
        return $this->belongsTo(customers::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(sales_item::class, 'sale_id');
    }

    public function payments()
    {
        return $this->hasMany(sales_payments::class, 'sale_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('sale_status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('sale_status', 'pending');
    }

    public function scopeCanceled($query)
    {
        return $query->where('sale_status', 'canceled');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeForCarwash($query, $carwashId)
    {
        return $query->where('carwash_id', $carwashId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('sale_date', today());
    }

    // Computed attributes
    public function getInvoiceNumberAttribute()
    {
        return 'INV-' . strtoupper(substr($this->id, 0, 8));
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->sum('amount');
    }

    public function getBalanceDueAttribute()
    {
        return max(0, $this->total_amount - $this->total_paid);
    }

    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function getSaleStatusBadgeClassAttribute()
    {
        return match($this->sale_status) {
            'completed' => 'success',
            'pending' => 'warning',
            'canceled' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'unpaid' => 'danger',
            'partial' => 'warning',
            'pending' => 'warning',
            'refunded' => 'info',
            'canceled' => 'secondary',
            default => 'secondary',
        };
    }

    // Generate unique sale number
    public static function generateSaleNumber()
    {
        $prefix = 'SL';
        $date = now()->format('Ymd');
        $random = strtoupper(Str::random(4));
        return "{$prefix}-{$date}-{$random}";
    }
}
