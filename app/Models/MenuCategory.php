<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MenuCategory extends Model
{
    use HasUuids;

    protected $fillable = [
        'outlet_id',
        'name',
        'description',
        'image',
        'sort_order',
        'status',
    ];

    // Relationships
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'category_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')->orderBy('sort_order');
    }
}
