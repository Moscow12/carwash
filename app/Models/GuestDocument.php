<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'guest_id',
        'doc_type',
        'doc_number',
        'issued_by',
        'issue_date',
        'expiry_date',
        'scan_url',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Relationships
    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    // Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
