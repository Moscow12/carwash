<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class stocktaking extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'stocktakings';

    protected $fillable = [
        'item_id',
        'user_id',
        'business_id',
        'outlet_id',
        'reference_no',
        'stocktake_date',
        'expected_quantity',
        'actual_quantity',
        'variance',
        'quantity',
        'price',
        'stocktaking_status',
        'adjustment_reason',
        'notes',
    ];

    protected $casts = [
        'stocktake_date' => 'date',
        'expected_quantity' => 'decimal:3',
        'actual_quantity' => 'decimal:3',
        'variance' => 'decimal:3',
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
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

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    // Scopes
    public function scopeReceived($query)
    {
        return $query->where('stocktaking_status', 'received');
    }

    public function scopePending($query)
    {
        return $query->where('stocktaking_status', 'pending');
    }

    public function scopeCanceled($query)
    {
        return $query->where('stocktaking_status', 'canceled');
    }

    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }

    // Helpers
    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->stocktaking_status) {
            'received' => 'success',
            'pending' => 'warning',
            'canceled' => 'danger',
            default => 'secondary',
        };
    }
}
