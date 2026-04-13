<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Promotion extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'name',
        'code',
        'type',
        'value',
        'applies_to',
        'category_id',
        'item_id',
        'min_order_amount',
        'max_uses',
        'uses_count',
        'valid_from',
        'valid_to',
        'active_days',
        'active_start_time',
        'active_end_time',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'active_days' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(category::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class);
    }

    /**
     * Check if promotion is currently active
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->isBefore($this->valid_from)) {
            return false;
        }

        if ($this->valid_to && $now->isAfter($this->valid_to)) {
            return false;
        }

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }
}
