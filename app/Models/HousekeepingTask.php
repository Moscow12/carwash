<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class HousekeepingTask extends Model
{
    use HasUuids;

    protected $fillable = [
        'business_id',
        'room_id',
        'assigned_to',
        'task_type',
        'priority',
        'status',
        'scheduled_date',
        'started_at',
        'completed_at',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(staffs::class, 'assigned_to');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Scopes
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('priority', 'urgent');
    }
}
