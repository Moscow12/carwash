<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class carwashes extends Model
{
    use HasUuids;
    protected $table = 'carwashes';
    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function regions(): BelongsTo
    {
        return $this->belongsTo(regions::class);
    }

    public function districts(): BelongsTo
    {
        return $this->belongsTo(districts::class);
    }

    public function wards(): BelongsTo
    {
        return $this->belongsTo(wards::class);
    }

    public function streets(): BelongsTo
    {
        return $this->belongsTo(street::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(items::class, 'carwash_id');
    }

    public function staffs(): HasMany
    {
        return $this->hasMany(staffs::class, 'carwash_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(customers::class, 'carwash_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(sales::class, 'carwash_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'carwash_id');
    }
}
