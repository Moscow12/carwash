<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PosOrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'modifiers',
        'subtotal',
        'status',
        'kitchen_notes',
        'voided_reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'modifiers' => 'array',
        'subtotal' => 'decimal:2',
    ];

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function kitchenTickets(): HasMany
    {
        return $this->hasMany(KitchenTicket::class, 'order_item_id');
    }
}
