<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarHappyHourPrice extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'menu_item_id',
        'discount_type',
        'discount_value',
        'override_days',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'override_days' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    /**
     * Calculate the happy hour price
     */
    public function calculatePrice(float $regularPrice): float
    {
        return match($this->discount_type) {
            'fixed_price' => $this->discount_value,
            'fixed_discount' => max(0, $regularPrice - $this->discount_value),
            'percentage' => $regularPrice * (1 - ($this->discount_value / 100)),
            default => $regularPrice,
        };
    }
}
