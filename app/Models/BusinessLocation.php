<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class BusinessLocation extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'name',
        'code',
        'type',
        'description',
        'address',
        'phone',
        'email',
        'region_id',
        'district_id',
        'ward_id',
        'street_id',
        'operating_hours',
        'is_main',
        'manager_name',
        'manager_phone',
        'status',
    ];

    protected $casts = [
        'operating_hours' => 'array',
        'is_main' => 'boolean',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(districts::class, 'district_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(wards::class, 'ward_id');
    }

    public function street(): BelongsTo
    {
        return $this->belongsTo(street::class, 'street_id');
    }

    // Hotel-specific relationships (when type = 'hotel')
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'branch_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'branch_id');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'branch_id');
    }

    public function nightAuditSnapshots(): HasMany
    {
        return $this->hasMany(NightAuditSnapshot::class, 'branch_id');
    }

    // Restaurant/Bar-specific relationships (when type = 'restaurant', 'bar', 'cafe')
    public function posOutlets(): HasMany
    {
        return $this->hasMany(PosOutlet::class, 'branch_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeCarWashes(Builder $query): Builder
    {
        return $query->where('type', 'car_wash');
    }

    public function scopeHotels(Builder $query): Builder
    {
        return $query->where('type', 'hotel');
    }

    public function scopeRestaurants(Builder $query): Builder
    {
        return $query->whereIn('type', ['restaurant', 'bar', 'cafe']);
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

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->ward?->name,
            $this->district?->name,
            $this->region?->name,
        ]);

        return implode(', ', $parts) ?: '-';
    }

    public function getTypeNameAttribute(): string
    {
        return match($this->type) {
            'car_wash' => 'Car Wash',
            'hotel' => 'Hotel',
            'restaurant' => 'Restaurant',
            'bar' => 'Bar',
            'cafe' => 'Café',
            'shop' => 'Shop',
            'salon' => 'Salon',
            'spa' => 'Spa',
            'gym' => 'Gym',
            default => ucfirst($this->type),
        };
    }
}
