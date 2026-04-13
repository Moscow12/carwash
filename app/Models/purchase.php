<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class purchase extends Model
{
    use HasUuids;

    protected $fillable = [
        'item_id',
        'user_id',
        'supplier_id',
        'business_id',
        'quantity',
        'price',
        'discount',
        'payment_status',
        'purchase_status',
        'notes',
        'paid_amount',
        'balance',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    /**
     * Get the item for this purchase
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    /**
     * Get the user who made the purchase
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(suplier::class, 'supplier_id');
    }

    /**
     * Get the business
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    /**
     * Scope: Get only received purchases
     */
    public function scopeReceived($query)
    {
        return $query->where('purchase_status', 'received');
    }

    /**
     * Scope: Get only unpaid purchases
     */
    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    /**
     * Scope: Get only paid purchases
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope: Get only pending purchases
     */
    public function scopePending($query)
    {
        return $query->where('purchase_status', 'pending');
    }

    /**
     * Scope: Get only canceled purchases
     */
    public function scopeCanceled($query)
    {
        return $query->where('purchase_status', 'canceled');
    }

    /**
     * Calculate total amount
     */
    public function getTotalAttribute(): float
    {
        $subtotal = $this->quantity * $this->price;
        $discount = $this->discount ?? 0;
        return $subtotal - $discount;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->purchase_status) {
            'received' => 'success',
            'pending' => 'warning',
            'canceled' => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'success',
            'unpaid' => 'danger',
            'pending' => 'warning',
            'refunded' => 'info',
            'canceled' => 'secondary',
            default => 'secondary'
        };
    }
}
