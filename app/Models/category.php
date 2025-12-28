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
        'carwash_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
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

    public function scopeByCarwash(Builder $query, string $carwashId): Builder
    {
        return $query->where('carwash_id', $carwashId);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
