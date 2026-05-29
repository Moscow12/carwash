<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Builder;

class Property extends Model
{
    use HasUuids;

    protected $table = 'properties';

    protected $fillable = [
        'landlord_id',
        'property_name',
        'property_type',
        'country_id',
        'region_id',
        'district_id',
        'ward_id',
        'street_id',
        'postal_address',
        'description',
        'status',
    ];

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class, 'landlord_id');
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

    public function units(): HasMany
    {
        return $this->hasMany(RentalUnit::class, 'property_id');
    }

    public function tenancyAgreements(): HasMany
    {
        return $this->hasMany(TenancyAgreement::class, 'property_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
