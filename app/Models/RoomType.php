<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class RoomType extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'max_adults',
        'max_children',
        'base_price',
        'weekend_price',
        'amenities',
        'images',
        'status',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'amenities' => 'array',
        'images' => 'array',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'room_type_id');
    }

    public function ratePlans(): HasMany
    {
        return $this->hasMany(RatePlan::class, 'room_type_id');
    }

    public function channelMappings(): HasMany
    {
        return $this->hasMany(ChannelMapping::class, 'room_type_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getTotalCapacityAttribute(): int
    {
        return $this->max_adults + $this->max_children;
    }
}
