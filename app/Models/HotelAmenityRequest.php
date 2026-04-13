<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelAmenityRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'reservation_id',
        'room_id',
        'guest_id',
        'folio_id',
        'amenity',
        'quantity',
        'charge_amount',
        'requested_at',
        'delivered_at',
        'delivered_by',
        'status',
        'notes',
    ];

    protected $casts = [
        'charge_amount' => 'decimal:2',
        'requested_at' => 'datetime',
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

    public function folio(): BelongsTo
    {
        return $this->belongsTo(Folio::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'delivered_by');
    }
}
