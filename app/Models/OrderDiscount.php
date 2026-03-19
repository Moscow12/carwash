<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderDiscount extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'discountable_type',
        'discountable_id',
        'discount_type',
        'value',
        'amount_deducted',
        'voucher_code',
        'reason',
        'approved_by',
        'applied_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'amount_deducted' => 'decimal:2',
    ];

    public function discountable(): MorphTo
    {
        return $this->morphTo();
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
