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
        'duration_months',
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
        'duration_months' => 'integer',
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

    /** Number of months between billing cycles for each payment_frequency. */
    public function frequencyIntervalMonths(): int
    {
        return match ($this->payment_frequency) {
            'quarterly' => 3,
            'semi_annual' => 6,
            'annual' => 12,
            default => 1, // monthly
        };
    }

    /**
     * The last month (1st of month) for which rent can fall due, or null if open-ended.
     *
     * - end_date set → its month caps the term (any status).
     * - expired with no end_date → cap at the last billed month (latest receipt's
     *   payment_for_month), falling back to the start month so it shows once and no
     *   further. This stops an open-ended expired agreement billing indefinitely.
     */
    public function lastDueMonth(): ?\Carbon\Carbon
    {
        if ($this->end_date) {
            return $this->end_date->copy()->startOfMonth();
        }

        if ($this->agreement_status === 'expired') {
            $lastBilled = $this->rentPayments()->max('payment_for_month');
            $cap = $lastBilled
                ? \Carbon\Carbon::parse($lastBilled)
                : $this->start_date;

            return $cap?->copy()->startOfMonth();
        }

        return null; // active / draft with no end_date → open-ended
    }

    /**
     * Whether rent falls due in the given month for this agreement's billing cycle.
     * A month is "due" when it is on or after start month, within the term (capped
     * by lastDueMonth), and an exact number of billing cycles from the start month.
     *
     * @param  \Carbon\Carbon  $month  Any date within the target month.
     */
    public function isDueInMonth(\Carbon\Carbon $month): bool
    {
        if (!$this->start_date) {
            return false;
        }

        $start = $this->start_date->copy()->startOfMonth();
        $target = $month->copy()->startOfMonth();

        // Before the tenancy begins → nothing due
        if ($target->lt($start)) {
            return false;
        }

        // After the term ends (end_date, or the expired cap) → nothing due
        $lastDue = $this->lastDueMonth();
        if ($lastDue && $target->gt($lastDue)) {
            return false;
        }

        $monthsElapsed = $start->diffInMonths($target);

        return $monthsElapsed % $this->frequencyIntervalMonths() === 0;
    }

    /**
     * Total number of months the contract runs.
     * Prefers the stored duration_months; otherwise derives from start → end.
     */
    public function durationInMonths(): ?int
    {
        if ($this->duration_months) {
            return (int) $this->duration_months;
        }

        if ($this->start_date && $this->end_date) {
            // +1 so an agreement Jan 1 → Dec 31 counts as 12 months, not 11.
            return $this->start_date->copy()->startOfMonth()
                ->diffInMonths($this->end_date->copy()->startOfMonth()) + 1;
        }

        return null;
    }

    /**
     * The amount charged on a given due month — rent_amount (a monthly rate) times the
     * number of months that period covers. A full period is frequencyIntervalMonths(),
     * but the final period is truncated to whatever months remain before the term ends.
     *
     * @param  \Carbon\Carbon  $month
     */
    public function periodChargeForMonth(\Carbon\Carbon $month): float
    {
        if (!$this->isDueInMonth($month)) {
            return 0.0;
        }

        $interval = $this->frequencyIntervalMonths();
        $monthsThisPeriod = $interval;

        // Truncate the last period to the months remaining within the term.
        $lastDue = $this->lastDueMonth();
        if ($lastDue) {
            $target = $month->copy()->startOfMonth();
            $remaining = $target->diffInMonths($lastDue->copy()->startOfMonth()) + 1; // inclusive
            $monthsThisPeriod = min($interval, max(1, $remaining));
        }

        return round((float) $this->rent_amount * $monthsThisPeriod, 2);
    }

    /**
     * Amount owed for the given month: the period charge on a due month, otherwise 0.
     * Alias kept for backward compatibility with existing callers.
     *
     * @param  \Carbon\Carbon  $month
     */
    public function dueAmountForMonth(\Carbon\Carbon $month): float
    {
        return $this->periodChargeForMonth($month);
    }

    /**
     * The total value of the contract over its full term: monthly rent × duration.
     * Null when the duration is unknown (no duration_months and no end_date).
     */
    public function totalContractAmount(): ?float
    {
        $months = $this->durationInMonths();

        return $months !== null ? round((float) $this->rent_amount * $months, 2) : null;
    }
}
