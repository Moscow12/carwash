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
        'tax_rate_id',
        'printer_station',
        'image',
        'allergens',
        'is_vegetarian',
        'is_vegan',
        'is_available',
        'prep_time_mins',
        'status',
        'is_published',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'allergens' => 'array',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_available' => 'boolean',
        'is_published' => 'boolean',
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

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(MenuItemRecipe::class);
    }

    public function happyHourPrices(): HasMany
    {
        return $this->hasMany(BarHappyHourPrice::class);
    }

    public function bottleServices(): HasMany
    {
        return $this->hasMany(BarBottleService::class);
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

    /** The business this menu item belongs to, via its outlet. */
    public function resolveBusiness(): ?Business
    {
        return $this->outlet?->business;
    }

    /**
     * Normalized card for the public marketplace.
     * @return array<string,mixed>
     */
    public function toListingCard(): array
    {
        $price = $this->price !== null ? (float) $this->price : null;
        $business = $this->resolveBusiness();

        return [
            'id' => $this->id,
            'type' => 'menu',
            'type_label' => 'Menu',
            'title' => $this->name,
            'price' => $price,
            'price_label' => $price !== null ? 'TZS ' . number_format($price, 0) : null,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'description' => $this->description,
            'business' => $business,
            'shop_url' => $business ? route('site.shop', $business->id, false) : null,
            'created_at' => $this->created_at,
        ];
    }
}
