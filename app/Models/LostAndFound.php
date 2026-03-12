<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class LostAndFound extends Model
{
    use HasUuids;

    protected $table = 'lost_and_found';

    protected $fillable = [
        'business_id',
        'branch_id',
        'room_id',
        'item_description',
        'found_date',
        'status',
        'found_by',
        'claimed_by_guest',
        'notes',
    ];

    protected $casts = [
        'found_date' => 'date',
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

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function foundBy(): BelongsTo
    {
        return $this->belongsTo(staff::class, 'found_by');
    }

    public function claimedByGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'claimed_by_guest');
    }

    // Scopes
    public function scopeUnclaimed(Builder $query): Builder
    {
        return $query->where('status', 'found');
    }
}
