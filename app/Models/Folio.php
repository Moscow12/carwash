<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Folio extends Model
{
    use HasUuids;

    protected $fillable = [
        'folio_no',
        'carwash_id',
        'reservation_id',
        'guest_id',
        'status',
        'total_charges',
        'total_payments',
        'balance',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'total_charges' => 'decimal:2',
        'total_payments' => 'decimal:2',
        'balance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'guest_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(FolioCharge::class, 'folio_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(HotelInvoice::class, 'folio_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(HotelPayment::class, 'folio_id');
    }

    // Scopes
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    // Accessors
    public function getIsOpenAttribute(): bool
    {
        return $this->status === 'open';
    }
}
