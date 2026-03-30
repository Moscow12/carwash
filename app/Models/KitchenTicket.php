<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class KitchenTicket extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_item_id',
        'order_id',
        'outlet_id',
        'station',
        'status',
        'received_at',
        'started_at',
        'ready_at',
        'served_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'started_at' => 'datetime',
        'ready_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    // Relationships
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(PosOrderItem::class, 'order_item_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    // Scopes
    public function scopeQueued(Builder $query): Builder
    {
        return $query->where('status', 'queued');
    }

    public function scopePreparing(Builder $query): Builder
    {
        return $query->where('status', 'preparing');
    }
}
