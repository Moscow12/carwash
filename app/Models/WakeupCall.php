<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WakeupCall extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'reservation_id',
        'room_id',
        'guest_id',
        'scheduled_at',
        'repeat_daily',
        'status',
        'delivered_at',
        'delivered_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'repeat_daily' => 'boolean',
        'delivered_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }
}
