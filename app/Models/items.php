<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class items extends Model
{
    use HasUuids;

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
        'category_id',
        'carwash_id',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'market_price' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    // Relationships
    public function carwash(): BelongsTo
    {
        return $this->belongsTo(carwashes::class, 'carwash_id');
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

    public function scopeByCarwash(Builder $query, string $carwashId): Builder
    {
        return $query->where('carwash_id', $carwashId);
    }

    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBarcode(Builder $query, string $barcode, string $carwashId): Builder
    {
        return $query->where('barcode', $barcode)->where('carwash_id', $carwashId);
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
}
