<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Landlord extends Model
{
    use HasUuids;

    protected $table = 'landlords';

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'address',
        'country_id',
        'region_id',
        'district_id',
        'ward_id',
        'street_id',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(countries::class, 'country_id');
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

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    public function tenancyAgreements(): HasMany
    {
        return $this->hasMany(TenancyAgreement::class, 'landlord_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }
}
