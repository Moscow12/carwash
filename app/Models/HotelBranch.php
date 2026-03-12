<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class HotelBranch extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'address',
        'phone',
        'is_main',
        'status',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'branch_id');
    }

    public function posOutlets(): HasMany
    {
        return $this->hasMany(PosOutlet::class, 'branch_id');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'branch_id');
    }

    public function lostAndFoundItems(): HasMany
    {
        return $this->hasMany(LostAndFound::class, 'branch_id');
    }

    public function nightAuditSnapshots(): HasMany
    {
        return $this->hasMany(NightAuditSnapshot::class, 'branch_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeMain(Builder $query): Builder
    {
        return $query->where('is_main', true);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
