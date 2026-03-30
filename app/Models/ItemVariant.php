<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVariant extends Model
{
    use HasUuids;

    protected $fillable = [
        'item_id',
        'name',
        'sku',
        'barcode',
        'cost_price',
        'selling_price',
        'stock_qty',
        'status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_qty' => 'decimal:3',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class);
    }
}
