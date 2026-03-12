<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MaintenanceRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'carwash_id',
        'branch_id',
        'room_id',
        'category',
        'description',
        'priority',
        'status',
        'assigned_to',
        'estimated_cost',
        'actual_cost',
        'resolved_at',
    ];

    protected $casts = [
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'resolved_at' => 'datetime',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(HotelBranch::class, 'branch_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(staff::class, 'assigned_to');
    }

    // Scopes
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', 'urgent');
    }
}
