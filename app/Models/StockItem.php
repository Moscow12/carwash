<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockItem extends Model
{
    /** @use HasFactory<\Database\Factories\StockItemFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'stock_id',
        'item_id',
        'previous_balance',
        'current_balance',
        'variance',
    ];

    protected function casts(): array
    {
        return [
            'previous_balance' => 'decimal:3',
            'current_balance' => 'decimal:3',
            'variance' => 'decimal:3',
        ];
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class, 'item_id');
    }
}
