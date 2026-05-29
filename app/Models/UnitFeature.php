<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class UnitFeature extends Model
{
    use HasUuids;

    protected $table = 'unit_features';

    protected $fillable = [
        'business_id',
        'feature_name',
        'feature_description',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(
            RentalUnit::class,
            'rental_unit_features',
            'unit_feature_id',
            'rental_unit_id'
        )->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
