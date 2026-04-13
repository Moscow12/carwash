<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomRateOverride extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'room_type_id',
        'rate_plan_id',
        'name',
        'override_price',
        'date_from',
        'date_to',
        'min_nights',
        'status',
    ];

    protected $casts = [
        'override_price' => 'decimal:2',
        'date_from' => 'date',
        'date_to' => 'date',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function ratePlan(): BelongsTo
    {
        return $this->belongsTo(RatePlan::class);
    }
}
