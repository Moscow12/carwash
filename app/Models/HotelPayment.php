<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class HotelPayment extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'invoice_id',
        'folio_id',
        'pos_order_id',
        'payment_method_id',
        'amount',
        'currency',
        'exchange_rate',
        'reference_no',
        'gateway_ref',
        'status',
        'received_by',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(HotelInvoice::class, 'invoice_id');
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class, 'folio_id');
    }

    public function posOrder(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'pos_order_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(payment_method::class, 'payment_method_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Scopes
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
