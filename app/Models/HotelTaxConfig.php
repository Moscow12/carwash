<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class HotelTaxConfig extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'type',
        'rate',
        'applies_to',
        'is_inclusive',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_inclusive' => 'boolean',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForRooms(Builder $query): Builder
    {
        return $query->whereIn('applies_to', ['rooms', 'all']);
    }

    public function scopeForFood(Builder $query): Builder
    {
        return $query->whereIn('applies_to', ['food', 'all']);
    }
}
