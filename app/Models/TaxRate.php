<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxRate extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'rate',
        'is_inclusive',
        'applies_to',
        'is_default',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'is_inclusive' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Calculate tax amount from base price
     */
    public function calculateTax(float $amount): float
    {
        if ($this->is_inclusive) {
            // Tax is already included, extract it
            return round($amount - ($amount / (1 + $this->rate)), 2);
        }

        // Tax is exclusive, add it
        return round($amount * $this->rate, 2);
    }

    /**
     * Get price with tax
     */
    public function getPriceWithTax(float $basePrice): float
    {
        if ($this->is_inclusive) {
            return $basePrice;
        }

        return round($basePrice * (1 + $this->rate), 2);
    }
}
