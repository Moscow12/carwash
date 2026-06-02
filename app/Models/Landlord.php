<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Landlord extends Model
{
    use HasUuids;

    protected $table = 'landlords';

    protected $fillable = [
        'business_id',
        'user_id',
        'name',
        'phone',
        'email',
        'address',
        'country_id',
        'region_id',
        'district_id',
        'ward_id',
        'street_id',
        'status',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(countries::class, 'country_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(districts::class, 'district_id');
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(wards::class, 'ward_id');
    }

    public function street(): BelongsTo
    {
        return $this->belongsTo(street::class, 'street_id');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'landlord_id');
    }

    public function tenancyAgreements(): HasMany
    {
        return $this->hasMany(TenancyAgreement::class, 'landlord_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeForUser(Builder $query, string $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLinkedToUser(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    // ─── Accessors ───────────────────────────────────────────────

    public function getHasLoginAttribute(): bool
    {
        return $this->user_id !== null;
    }
}
