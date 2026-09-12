<?php

namespace App\Services\Pesapal;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use Illuminate\Support\Str;

/**
 * Single source of truth for "what does a completed subscription payment do."
 * Used by both the public callback and IPN endpoints. Never trusts the status
 * carried on the incoming request — Pesapal's own docs require re-fetching
 * status server-side via GetTransactionStatus.
 */
class SubscriptionPaymentProcessor
{
    public function __construct(private readonly PesapalClient $client)
    {
    }

    /**
     * Admin-recorded offline payment (cash, bank transfer, etc). Creates a
     * completed transaction row with environment='manual' — distinct from real
     * Pesapal test/live transactions in the payments report — and activates the
     * subscription immediately since there is no gateway confirmation to await.
     */
    public function recordManualPayment(Business $business, SubscriptionPlan $plan): SubscriptionTransaction
    {
        $subscription = BusinessSubscription::getForBusiness($business->id);

        $transaction = SubscriptionTransaction::create([
            'business_id' => $business->id,
            'business_subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'merchant_reference' => 'MAN-' . Str::upper(Str::random(12)),
            'amount' => $plan->fee_amount,
            'currency' => $plan->currency,
            'environment' => 'manual',
            'status' => 'completed',
        ]);

        $subscription->activate($plan);

        return $transaction;
    }

    public function confirm(string $orderTrackingId): SubscriptionTransaction
    {
        $transaction = SubscriptionTransaction::where('order_tracking_id', $orderTrackingId)->firstOrFail();

        $status = $this->client->getTransactionStatus($orderTrackingId);
        $statusCode = (int) ($status['status_code'] ?? -1);

        $transaction->update([
            'status' => $this->mapStatus($statusCode),
            'pesapal_status_code' => $statusCode,
            'payment_method' => $status['payment_method'] ?? null,
            'confirmation_code' => $status['confirmation_code'] ?? null,
            'raw_response' => $status,
        ]);

        if ($statusCode === 1) {
            $subscription = BusinessSubscription::getForBusiness($transaction->business_id);
            $subscription->activate($transaction->plan);

            if ($transaction->business_subscription_id !== $subscription->id) {
                $transaction->update(['business_subscription_id' => $subscription->id]);
            }
        }

        return $transaction->refresh();
    }

    private function mapStatus(int $code): string
    {
        return match ($code) {
            1 => 'completed',
            2 => 'failed',
            3 => 'reversed',
            default => 'invalid',
        };
    }
}
