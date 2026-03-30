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
        'business_id',
        'branch_id',
        'name',
        'type',
        'open_time',
        'close_time',
        'status',
    ];

    // Relationships
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
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

    public function items(): HasMany
    {
        return $this->hasMany(items::class, 'outlet_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(sales::class, 'outlet_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(purchase::class, 'outlet_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(expenses::class, 'outlet_id');
    }

    public function stocktakings(): HasMany
    {
        return $this->hasMany(stocktaking::class, 'outlet_id');
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'outlet_id');
    }

    public function itemBalances(): HasMany
    {
        return $this->hasMany(item_balance::class, 'outlet_id');
    }

    public function printerStations(): HasMany
    {
        return $this->hasMany(OutletPrinterStation::class, 'outlet_id');
    }

    public function barProfile(): HasMany
    {
        return $this->hasMany(BarProfile::class, 'outlet_id');
    }

    public function tableReservations(): HasMany
    {
        return $this->hasMany(TableReservation::class, 'outlet_id');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
