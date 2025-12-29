<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class purchase extends Model
{
    use HasUuids;

    protected $table = 'purchases';

    protected $fillable = [
        'item_id',
        'user_id',
        'supplier_id',
        'carwash_id',
        'quantity',
        'price',
        'discount',
        'payment_status',
        'purchase_status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    // Relationships
    public function item()
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(suplier::class, 'supplier_id');
    }

    public function carwash()
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    // Scopes - Purchase Status
    public function scopeReceived($query)
    {
        return $query->where('purchase_status', 'received');
    }

    public function scopePending($query)
    {
        return $query->where('purchase_status', 'pending');
    }

    public function scopeCanceled($query)
    {
        return $query->where('purchase_status', 'canceled');
    }

    // Scopes - Payment Status
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopePaymentPending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeForCarwash($query, $carwashId)
    {
        return $query->where('carwash_id', $carwashId);
    }

    // Computed Attributes
    public function getTotalAttribute()
    {
        $subtotal = $this->quantity * $this->price;
        return $subtotal - ($this->discount ?? 0);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getPurchaseStatusBadgeClassAttribute()
    {
        return match($this->purchase_status) {
            'received' => 'success',
            'pending' => 'warning',
            'canceled' => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentStatusBadgeClassAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'unpaid' => 'danger',
            'pending' => 'warning',
            'refunded' => 'info',
            'canceled' => 'secondary',
            default => 'secondary',
        };
    }

    public function getPaymentStatusLabelAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'pending' => 'Pending',
            'refunded' => 'Refunded',
            'canceled' => 'Canceled',
            default => ucfirst($this->payment_status),
        };
    }

    public function getPurchaseStatusLabelAttribute()
    {
        return match($this->purchase_status) {
            'received' => 'Received',
            'pending' => 'Pending',
            'canceled' => 'Canceled',
            default => ucfirst($this->purchase_status),
        };
    }
}
