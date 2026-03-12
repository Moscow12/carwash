<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class PosOutlet extends Model
{
    use HasUuids;

    protected $fillable = [
        'carwash_id',
        'branch_id',
        'name',
        'type',
        'open_time',
        'close_time',
        'status',
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

    public function tables(): HasMany
    {
        return $this->hasMany(PosTable::class, 'outlet_id');
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class, 'outlet_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'outlet_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(PosSession::class, 'outlet_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'outlet_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
