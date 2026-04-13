<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class category extends Model
{
    use HasUuids;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'business_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(items::class, 'category_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
