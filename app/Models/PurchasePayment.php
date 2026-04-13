<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchasePayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_id',
        'payment_method_id',
        'business_id',
        'user_id',
        'amount',
        'reference_no',
        'notes',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationships
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(purchase::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(payment_method::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
