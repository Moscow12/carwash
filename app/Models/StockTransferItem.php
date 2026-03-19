<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'transfer_id',
        'item_id',
        'quantity_sent',
        'quantity_received',
        'unit_id',
        'notes',
    ];

    protected $casts = [
        'quantity_sent' => 'decimal:3',
        'quantity_received' => 'decimal:3',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(unit::class);
    }
}
