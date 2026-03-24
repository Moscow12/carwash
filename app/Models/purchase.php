<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class purchase extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'purchases';

    protected $fillable = [
        'item_id',
        'user_id',
        'supplier_id',
        'business_id',
        'reference_no',
        'received_date',
        'quantity',
        'price',
        'discount',
        'subtotal',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'balance',
        'outlet_id',
        'payment_status',
        'purchase_status',
        'notes',
    ];

    protected $casts = [
        'received_date' => 'date',
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(suplier::class, 'supplier_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
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

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
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

    // Payment tracking helpers
    public function updatePaymentStatus()
    {
        $totalAmount = $this->total_amount ?? 0;
        $paidAmount = $this->paid_amount ?? 0;

        if ($paidAmount <= 0) {
            $this->payment_status = 'unpaid';
        } elseif ($paidAmount >= $totalAmount) {
            $this->payment_status = 'paid';
        } else {
            $this->payment_status = 'partial';
        }

        $this->balance = $totalAmount - $paidAmount;
        $this->save();
    }

    public function recalculateBalance()
    {
        $this->paid_amount = $this->payments()->sum('amount');
        $this->balance = $this->total_amount - $this->paid_amount;
        $this->updatePaymentStatus();
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function hasPartialPayment(): bool
    {
        return $this->paid_amount > 0 && $this->paid_amount < $this->total_amount;
    }

    public function getRemainingBalanceAttribute()
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }
}
