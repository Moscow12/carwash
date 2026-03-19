<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'unit_id',
        'quantity',
        'unit_cost',
        'tax_rate_id',
        'tax_amount',
        'discount',
        'subtotal',
        'expiry_date',
        'batch_no',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(purchase::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(unit::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }
}
