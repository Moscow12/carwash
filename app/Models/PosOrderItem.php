<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosOrderItem extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'menu_item_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total',
        'modifiers',
        'subtotal',
        'status',
        'kitchen_notes',
        'is_voided',
        'voided_at',
        'voided_by',
        'voided_reason',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'modifiers' => 'array',
        'subtotal' => 'decimal:2',
        'quantity' => 'decimal:3',
        'is_voided' => 'boolean',
        'voided_at' => 'datetime',
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

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function voidLogs(): MorphMany
    {
        return $this->morphMany(VoidLog::class, 'voidable');
    }
}
