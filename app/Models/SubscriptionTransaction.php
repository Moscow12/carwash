<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionTransaction extends Model
{
    use HasUuids;

    protected $table = 'subscription_transactions';

    protected $fillable = [
        'business_id',
        'business_subscription_id',
        'plan_id',
        'merchant_reference',
        'order_tracking_id',
        'amount',
        'currency',
        'environment',
        'status',
        'pesapal_status_code',
        'payment_method',
        'confirmation_code',
        'raw_response',
        'redirect_url',
        'callback_received_at',
        'ipn_received_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'raw_response' => 'array',
        'callback_received_at' => 'datetime',
        'ipn_received_at' => 'datetime',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function businessSubscription(): BelongsTo
    {
        return $this->belongsTo(BusinessSubscription::class, 'business_subscription_id');
    }
}
