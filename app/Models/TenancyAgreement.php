<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TenancyAgreement extends Model
{
    use HasUuids;

    protected $table = 'tenancy_agreements';

    protected $fillable = [
        'customer_id',
        'landlord_id',
        'property_id',
        'rental_unit_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_paid',
        'payment_frequency',
        'agreement_status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'rent_amount' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(customers::class, 'customer_id');
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(Landlord::class, 'landlord_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(RentalUnit::class, 'rental_unit_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class, 'tenancy_agreement_id');
    }

    public function utilityBills(): HasMany
    {
        return $this->hasMany(UtilityBill::class, 'tenancy_agreement_id');
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(RentalMaintenanceRequest::class, 'tenancy_agreement_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('agreement_status', 'active');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->agreement_status === 'active';
    }
}
