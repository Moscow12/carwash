<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class items extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'items';

    protected $fillable = [
        'name',
        'barcode',
        'description',
        'cost_price',
        'type',
        'product_stock',
        'selling_price',
        'market_price',
        'image',
        'commission',
        'commission_type',
        'require_plate_number',
        'unit_id',
        'status',
        'is_published',
        'category_id',
        'business_id',
        'outlet_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'market_price' => 'decimal:2',
        'commission' => 'decimal:2',
        'is_published' => 'boolean',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(category::class, 'category_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(unit::class, 'unit_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(sales::class, 'item_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'item_id');
    }

    public function stocktaking(): HasMany
    {
        return $this->hasMany(stocktaking::class, 'item_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(purchase::class, 'item_id');
    }

    public function itemBalances(): HasMany
    {
        return $this->hasMany(item_balance::class, 'item_id');
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(PosOutlet::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ItemVariant::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function stockTransferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function menuItemRecipes(): HasMany
    {
        return $this->hasMany(MenuItemRecipe::class);
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

    public function scopeServices(Builder $query): Builder
    {
        return $query->where('type', 'Service');
    }

    public function scopeProducts(Builder $query): Builder
    {
        return $query->where('type', 'product');
    }

    public function scopeByBusiness(Builder $query, string $businessId): Builder
    {
        return $query->where('business_id', $businessId);
    }

    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBarcode(Builder $query, string $barcode, string $businessId): Builder
    {
        return $query->where('barcode', $barcode)->where('business_id', $businessId);
    }

    // Accessors
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsServiceAttribute(): bool
    {
        return $this->type === 'Service';
    }

    public function getProfitMarginAttribute(): float
    {
        if ($this->cost_price > 0) {
            return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
        }
        return 0;
    }

    public function getFormattedSellingPriceAttribute(): string
    {
        return 'TZS ' . number_format($this->selling_price, 0);
    }

    /**
     * Normalized card for the public marketplace.
     * @return array<string,mixed>
     */
    public function toListingCard(): array
    {
        $isService = $this->type === 'Service';
        $price = $this->selling_price !== null ? (float) $this->selling_price : null;

        return [
            'id' => $this->id,
            'type' => $isService ? 'service' : 'item',
            'type_label' => $isService ? 'Service' : 'Shop',
            'title' => $this->name,
            'price' => $price,
            'price_label' => $price !== null ? 'TZS ' . number_format($price, 0) : null,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'description' => $this->description,
            'business' => $this->business,
            'shop_url' => $this->business ? route('site.shop', $this->business->id, false) : null,
            'created_at' => $this->created_at,
        ];
    }
}
