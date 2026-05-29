<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalUnitFeature extends Model
{
    use HasUuids;

    protected $table = 'rental_unit_features';

    protected $fillable = [
        'rental_unit_id',
        'unit_feature_id',
    ];

    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class, 'rental_unit_id');
    }

    public function feature(): BelongsTo
    {
        return $this->belongsTo(UnitFeature::class, 'unit_feature_id');
    }
}
