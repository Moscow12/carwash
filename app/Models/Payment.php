<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'payable_type',
        'payable_id',
        'payment_method_id',
        'amount',
        'currency',
        'exchange_rate',
        'amount_local',
        'reference_no',
        'gateway_ref',
        'charged_to_folio',
        'charged_to_tab',
        'status',
        'received_by',
        'paid_at',
        'receipt_channel',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'amount_local' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(payment_method::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class, 'charged_to_folio');
    }
}
