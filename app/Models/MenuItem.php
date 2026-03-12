<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MenuItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'outlet_id',
        'category_id',
        'item_id',
        'name',
        'description',
        'sku',
        'price',
        'cost_price',
        'image',
        'allergens',
        'is_vegetarian',
        'is_vegan',
        'is_available',
        'prep_time_mins',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'allergens' => 'array',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_available' => 'boolean',
    ];

    // Relationships
    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class, 'outlet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(items::class, 'item_id');
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(ItemModifier::class, 'menu_item_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(PosOrderItem::class, 'menu_item_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeVegetarian(Builder $query): Builder
    {
        return $query->where('is_vegetarian', true);
    }
}
