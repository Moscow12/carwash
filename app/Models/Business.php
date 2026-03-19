<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Builder;

class Business extends Model
{
    use HasUuids;

    protected $table = 'businesses';

    protected $fillable = [
        'name',
        'address',
        'status',
        'type',
        'description',
        'logo',
        'whatsapp',
        'instagram',
        'facebook',
        'tiktok',
        'email',
        'website',
        'operating_hours',
        'resentative_name',
        'resentative_phone',
        'region_id',
        'district_id',
        'ward_id',
        'street_id',
        'owner_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // Relationships
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function regions(): BelongsTo
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function districts(): BelongsTo
    {
        return $this->belongsTo(districts::class, 'district_id');
    }

    public function wards(): BelongsTo
    {
        return $this->belongsTo(wards::class, 'ward_id');
    }

    public function streets(): BelongsTo
    {
        return $this->belongsTo(street::class, 'street_id');
    }

    // Business Locations (new multi-type branches)
    public function locations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class, 'business_id');
    }

    // Core business relationships (updated to use business_id)
    public function items(): HasMany
    {
        return $this->hasMany(items::class, 'business_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(staffs::class, 'business_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(customers::class, 'business_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(sales::class, 'business_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'business_id');
    }

    public function settings(): HasOne
    {
        return $this->hasOne(CarwashSetting::class, 'business_id');
    }

    // Hotel module relationships
    public function hotelProfile(): HasOne
    {
        return $this->hasOne(HotelProfile::class, 'business_id');
    }

    public function hotelBranches(): HasMany
    {
        return $this->hasMany(HotelBranch::class, 'business_id');
    }

    public function roomTypes(): HasMany
    {
        return $this->hasMany(RoomType::class, 'business_id');
    }

    public function guests(): HasMany
    {
        return $this->hasMany(Guest::class, 'business_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'business_id');
    }

    // Restaurant/Bar module relationships
    public function posOutlets(): HasMany
    {
        return $this->hasMany(PosOutlet::class, 'business_id');
    }

    public function posOrders(): HasMany
    {
        return $this->hasMany(PosOrder::class, 'business_id');
    }

    // Billing relationships
    public function folios(): HasMany
    {
        return $this->hasMany(Folio::class, 'business_id');
    }

    public function hotelInvoices(): HasMany
    {
        return $this->hasMany(HotelInvoice::class, 'business_id');
    }

    // New table relationships (from Phase 2)
    public function taxRates(): HasMany
    {
        return $this->hasMany(TaxRate::class, 'business_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class, 'business_id');
    }

    public function stockTransfers(): HasMany
    {
        return $this->hasMany(StockTransfer::class, 'business_id');
    }

    public function shiftSchedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class, 'business_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(purchase::class, 'business_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'business_id');
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(suplier::class, 'business_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(expenses::class, 'business_id');
    }

    public function stocktakings(): HasMany
    {
        return $this->hasMany(stocktaking::class, 'business_id');
    }

    public function itemBalances(): HasMany
    {
        return $this->hasMany(item_balance::class, 'business_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'business_id');
    }

    public function voidLogs(): HasMany
    {
        return $this->hasMany(VoidLog::class, 'business_id');
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

    public function scopeByOwner(Builder $query, string $ownerId): Builder
    {
        return $query->where('owner_id', $ownerId);
    }

    // Accessors
    public function getFullLocationAttribute(): string
    {
        $parts = array_filter([
            $this->regions?->name,
            $this->districts?->name,
            $this->wards?->name,
        ]);

        return implode(', ', $parts) ?: '-';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getTotalLocationsAttribute(): int
    {
        return $this->locations()->count();
    }
}
