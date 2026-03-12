<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class RatePlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'carwash_id',
        'room_type_id',
        'name',
        'meal_plan',
        'price',
        'valid_from',
        'valid_to',
        'min_nights',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'rate_plan_id');
    }

    public function channelMappings(): HasMany
    {
        return $this->hasMany(ChannelMapping::class, 'rate_plan_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeValid(Builder $query, $date = null): Builder
    {
        $checkDate = $date ?? now()->toDateString();
        return $query->where(function ($q) use ($checkDate) {
            $q->where(function ($subQ) use ($checkDate) {
                $subQ->where('valid_from', '<=', $checkDate)
                    ->where('valid_to', '>=', $checkDate);
            })->orWhere(function ($subQ) {
                $subQ->whereNull('valid_from')
                    ->whereNull('valid_to');
            });
        });
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getMealPlanNameAttribute(): string
    {
        $plans = [
            'RO' => 'Room Only',
            'BB' => 'Bed & Breakfast',
            'HB' => 'Half Board',
            'FB' => 'Full Board',
            'AI' => 'All Inclusive',
        ];
        return $plans[$this->meal_plan] ?? $this->meal_plan;
    }
}
