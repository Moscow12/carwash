<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemModifier extends Model
{
    use HasUuids;

    protected $fillable = [
        'menu_item_id',
        'group_name',
        'option_name',
        'price_adjustment',
        'is_required',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'is_required' => 'boolean',
    ];

    // Relationships
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }
}
