<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAuditSnapshot extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'branch_id',
        'audit_date',
        'total_rooms',
        'occupied_rooms',
        'occupancy_pct',
        'adr',
        'revpar',
        'room_revenue',
        'fb_revenue',
        'total_revenue',
        'new_arrivals',
        'departures',
        'no_shows',
        'run_by',
        'run_at',
    ];

    protected $casts = [
        'audit_date' => 'date',
        'occupancy_pct' => 'decimal:2',
        'adr' => 'decimal:2',
        'revpar' => 'decimal:2',
        'room_revenue' => 'decimal:2',
        'fb_revenue' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'run_at' => 'datetime',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(HotelBranch::class, 'branch_id');
    }

    public function runBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'run_by');
    }
}
