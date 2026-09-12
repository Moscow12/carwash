<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPlan extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'business_type',
        'fee_amount',
        'currency',
        'billing_interval',
        'description',
        'is_active',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public const BILLING_INTERVAL_LABELS = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'biannual' => 'Bi-Annual',
        'yearly' => 'Annually',
    ];

    public function getBillingIntervalLabelAttribute(): string
    {
        return self::BILLING_INTERVAL_LABELS[$this->billing_interval] ?? ucfirst($this->billing_interval);
    }

    public function businessSubscriptions(): HasMany
    {
        return $this->hasMany(BusinessSubscription::class, 'plan_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Plans usable by a business of this type: exact-type matches plus "any type" (null) plans. */
    public function scopeForBusinessType(Builder $query, ?string $type): Builder
    {
        return $query->where(function (Builder $q) use ($type) {
            $q->whereNull('business_type')->orWhere('business_type', $type);
        });
    }
}
