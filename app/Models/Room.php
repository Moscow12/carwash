<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Room extends Model
{
    use HasUuids;

    protected $fillable = [
        'carwash_id',
        'branch_id',
        'room_type_id',
        'number',
        'floor',
        'status',
        'is_smoking',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_smoking' => 'boolean',
        'is_active' => 'boolean',
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

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function housekeepingTasks(): HasMany
    {
        return $this->hasMany(HousekeepingTask::class, 'room_id');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'room_id');
    }

    public function lostAndFoundItems(): HasMany
    {
        return $this->hasMany(LostAndFound::class, 'room_id');
    }

    public function roomAllocations(): HasMany
    {
        return $this->hasMany(RoomAllocation::class, 'room_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('status', 'occupied');
    }

    public function scopeByBranch(Builder $query, string $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    // Accessors
    public function getIsAvailableAttribute(): bool
    {
        return $this->status === 'available' && $this->is_active;
    }

    public function getFullNumberAttribute(): string
    {
        return $this->floor ? "{$this->floor}{$this->number}" : $this->number;
    }
}
