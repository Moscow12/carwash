<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'business_id',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'nationality',
        'country',
        'coming_from',
        'going_to',
        'id_type',
        'id_number',
        'date_of_birth',
        'gender',
        'address',
        'vip_level',
        'loyalty_points',
        'preferences',
        'blacklisted',
        'blacklist_reason',
        'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'preferences' => 'array',
        'blacklisted' => 'boolean',
    ];

    protected $appends = [
        'full_name',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(customers::class, 'customer_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GuestDocument::class, 'guest_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'guest_id');
    }

    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class, 'guest_id');
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'guest_id');
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(CommunicationLog::class, 'guest_id');
    }

    public function reservationGuests(): HasMany
    {
        return $this->hasMany(ReservationGuest::class);
    }

    public function amenityRequests(): HasMany
    {
        return $this->hasMany(HotelAmenityRequest::class);
    }

    public function wakeupCalls(): HasMany
    {
        return $this->hasMany(WakeupCall::class);
    }

    public function laundryOrders(): HasMany
    {
        return $this->hasMany(LaundryOrder::class);
    }

    public function barTabs(): HasMany
    {
        return $this->hasMany(BarTab::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeVip(Builder $query, string $level = null): Builder
    {
        if ($level) {
            return $query->where('vip_level', $level);
        }
        return $query->whereIn('vip_level', ['silver', 'gold', 'platinum']);
    }

    public function scopeNotBlacklisted(Builder $query): Builder
    {
        return $query->where('blacklisted', false);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsVipAttribute(): bool
    {
        return in_array($this->vip_level, ['silver', 'gold', 'platinum']);
    }
}
