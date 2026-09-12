<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessSubscription extends Model
{
    use HasUuids;

    protected $table = 'business_subscriptions';

    protected $fillable = [
        'business_id',
        'plan_id',
        'status',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SubscriptionTransaction::class, 'business_subscription_id');
    }

    public static function getForBusiness(string $businessId): self
    {
        return self::firstOrCreate(
            ['business_id' => $businessId],
            ['status' => 'pending']
        );
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }

    /** True when active but set to expire within the given number of days. */
    public function isExpiringSoon(int $withinDays = 7): bool
    {
        return $this->isActive() && $this->daysUntilExpiry() <= $withinDays;
    }

    /** Calendar days remaining until expiry (0 = expires today), ignoring time-of-day. */
    public function daysUntilExpiry(): ?int
    {
        return $this->expires_at
            ? max(0, now()->startOfDay()->diffInDays($this->expires_at->copy()->startOfDay()))
            : null;
    }

    public function activate(SubscriptionPlan $plan): void
    {
        $starts = now();

        $this->update([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => $starts,
            'expires_at' => self::addInterval($starts, $plan->billing_interval),
        ]);
    }

    /**
     * Admin override: assign a plan and activate immediately, with no payment
     * involved. The subscription's own updated_at is the audit trail for this —
     * unlike a real or manual payment, nothing is written to subscription_transactions.
     */
    public function assignPlan(SubscriptionPlan $plan): void
    {
        $this->activate($plan);
    }

    /**
     * Extend the current period by one billing cycle of the given plan (or the
     * subscription's existing plan if none given). Extends from the current
     * expires_at when still active/future, otherwise from today.
     */
    public function extend(?SubscriptionPlan $plan = null): void
    {
        $plan ??= $this->plan;

        $base = ($this->expires_at && $this->expires_at->isFuture()) ? $this->expires_at->copy() : now();

        $this->update([
            'status' => 'active',
            'starts_at' => $this->starts_at ?? now(),
            'expires_at' => self::addInterval($base, $plan->billing_interval),
        ]);
    }

    private static function addInterval(\Illuminate\Support\Carbon $from, string $billingInterval): \Illuminate\Support\Carbon
    {
        return match ($billingInterval) {
            'quarterly' => $from->copy()->addMonths(3),
            'biannual' => $from->copy()->addMonths(6),
            'yearly' => $from->copy()->addYear(),
            default => $from->copy()->addMonth(),
        };
    }
}
