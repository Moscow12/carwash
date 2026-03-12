<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Reservation extends Model
{
    use HasUuids;

    protected $fillable = [
        'reservation_no',
        'carwash_id',
        'branch_id',
        'guest_id',
        'room_type_id',
        'rate_plan_id',
        'source_id',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'total_nights',
        'room_rate',
        'total_amount',
        'deposit_amount',
        'status',
        'special_requests',
        'internal_notes',
        'channel_ref',
        'created_by',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'room_rate' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(HotelBranch::class, 'branch_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class, 'rate_plan_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(BookingSource::class, 'source_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function roomAllocation(): HasOne
    {
        return $this->hasOne(RoomAllocation::class, 'reservation_id');
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class, 'reservation_id');
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'reservation_id');
    }

    // Scopes
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeCheckingInToday(Builder $query): Builder
    {
        return $query->whereDate('check_in_date', today());
    }

    public function scopeCheckingOutToday(Builder $query): Builder
    {
        return $query->whereDate('check_out_date', today());
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
    }

    // Accessors
    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->deposit_amount;
    }

    public function getTotalGuestsAttribute(): int
    {
        return $this->adults + $this->children;
    }
}
