<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class carwashes extends Model
{
    use HasUuids;

    protected $table = 'carwashes';

    protected $fillable = [
        'name',
        'address',
        'status',
        'description',
        'logo',
        'whatsapp',
        'instagram',
        'facebook',
        'tiktok',
        'email',
        'website',
        'operating_hours',
        'resentative_name',
        'resentative_phone',
        'region_id',
        'district_id',
        'ward_id',
        'street_id',
        'owner_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function regions(): BelongsTo
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function districts(): BelongsTo
    {
        return $this->belongsTo(districts::class, 'district_id');
    }

    public function wards(): BelongsTo
    {
        return $this->belongsTo(wards::class, 'ward_id');
    }

    public function streets(): BelongsTo
    {
        return $this->belongsTo(street::class, 'street_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(items::class, 'carwash_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(staffs::class, 'carwash_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(customers::class, 'carwash_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(sales::class, 'carwash_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'carwash_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByOwner(Builder $query, string $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    // Accessors
    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->regions?->name,
            $this->districts?->name,
            $this->wards?->name,
        ]);

        return implode(', ', $parts) ?: '-';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
