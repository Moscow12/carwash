<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItemRecipe extends Model
{
    use HasUuids;

    protected $fillable = [
        'menu_item_id',
        'item_id',
        'quantity',
        'unit_id',
        'is_optional',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'is_optional' => 'boolean',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
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
