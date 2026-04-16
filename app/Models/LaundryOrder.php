<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaundryOrder extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'reservation_id',
        'room_id',
        'guest_id',
        'folio_id',
        'order_no',
        'collected_at',
        'collected_by',
        'ready_at',
        'delivered_at',
        'delivered_by',
        'total_amount',
        'status',
        'is_express',
        'notes',
        'received_at',
        'completed_at',
        'charge_amount',
        'item_type',
        'quantity',
        'service_type',
        'expected_completion',
        'special_instructions',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'ready_at' => 'datetime',
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'expected_completion' => 'datetime',
        'total_amount' => 'decimal:2',
        'charge_amount' => 'decimal:2',
        'is_express' => 'boolean',
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

    public function items(): HasMany
    {
        return $this->hasMany(LaundryOrderItem::class);
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'delivered_by');
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'collected_by');
    }
}
