<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'carwash_id',
        'star_rating',
        'check_in_time',
        'check_out_time',
        'late_checkout_fee',
        'total_rooms',
        'tin_number',
        'vrn_number',
        'amenities',
        'policies',
        'status',
    ];

    protected $casts = [
        'late_checkout_fee' => 'decimal:2',
        'amenities' => 'array',
        'policies' => 'array',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
