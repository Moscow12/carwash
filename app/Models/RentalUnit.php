<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class RentalUnit extends Model
{
    use HasUuids;

    protected $table = 'rental_units';

    protected $fillable = [
        'property_id',
        'unit_number',
        'unit_type',
        'floor_no',
        'bedrooms',
        'bathrooms',
        'has_electricity',
        'has_water',
        'has_furniture',
        'monthly_rent',
        'deposit_amount',
        'status',
        'description',
    ];

    protected $casts = [
        'has_electricity' => 'boolean',
        'has_water' => 'boolean',
        'has_furniture' => 'boolean',
        'monthly_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'floor_no' => 'integer',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(UnitImage::class, 'rental_unit_id');
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(
            UnitFeature::class,
            'rental_unit_features',
            'rental_unit_id',
            'unit_feature_id'
        )->withTimestamps();
    }

    public function tenancyAgreements(): HasMany
    {
        return $this->hasMany(TenancyAgreement::class, 'rental_unit_id');
    }

    public function activeAgreement()
    {
        return $this->hasOne(TenancyAgreement::class, 'rental_unit_id')
            ->where('agreement_status', 'active')
            ->latestOfMany('start_date');
    }

    public function scopeVacant(Builder $query): Builder
    {
        return $query->where('status', 'vacant');
    }

    public function scopeOccupied(Builder $query): Builder
    {
        return $query->where('status', 'occupied');
    }
}
