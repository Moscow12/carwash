<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitImage extends Model
{
    use HasUuids;

    protected $table = 'unit_images';

    protected $fillable = [
        'rental_unit_id',
        'image_url',
        'caption',
        'is_primary',
        'display_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'display_order' => 'integer',
    ];

    public function rentalUnit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class, 'rental_unit_id');
    }
}
