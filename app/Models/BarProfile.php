<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BarProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'outlet_id',
        'enforce_age_check',
        'min_drinking_age',
        'tab_enabled',
        'tab_credit_limit',
        'happy_hour_enabled',
        'happy_hour_start',
        'happy_hour_end',
        'happy_hour_days',
        'happy_hour_discount_pct',
        'bottle_service_enabled',
        'receipt_message',
        'status',
    ];

    protected $casts = [
        'enforce_age_check' => 'boolean',
        'tab_enabled' => 'boolean',
        'tab_credit_limit' => 'decimal:2',
        'happy_hour_enabled' => 'boolean',
        'happy_hour_days' => 'array',
        'happy_hour_discount_pct' => 'decimal:2',
        'bottle_service_enabled' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }
}
