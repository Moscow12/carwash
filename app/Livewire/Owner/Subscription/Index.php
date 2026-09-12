<?php

namespace App\Livewire\Owner\Subscription;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\PaymentGatewaySetting;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    public ?Business $business = null;
    public ?BusinessSubscription $subscription = null;

    public function mount(): void
    {
        $this->business = Auth::user()->assignedBusinesses()->first();

        if ($this->business) {
            $this->subscription = BusinessSubscription::getForBusiness($this->business->id)->load('plan');
        }
    }

    public function payNow(string $planId)
    {
        $plan = SubscriptionPlan::active()->findOrFail($planId);

        $ownsBusiness = $this->business
            && Auth::user()->assignedBusinesses()->where('businesses.id', $this->business->id)->exists();

        if (!$ownsBusiness) {
            session()->flash('error', 'You do not have access to this business.');
            return;
        }

        $settings = PaymentGatewaySetting::current();

        $transaction = SubscriptionTransaction::create([
            'business_id' => $this->business->id,
            'plan_id' => $plan->id,
            'merchant_reference' => 'SUB-' . Str::upper(Str::random(12)),
            'amount' => $plan->fee_amount,
            'currency' => $plan->currency,
            'environment' => $settings->active_environment,
            'status' => 'pending',
        ]);

        try {
            $client = PesapalClient::forActiveEnvironment();

            $owner = Auth::user();
            [$firstName, $lastName] = $this->splitName($owner->name);

            $response = $client->submitOrderRequest([
                'id' => $transaction->merchant_reference,
                'currency' => $plan->currency,
                'amount' => (float) $plan->fee_amount,
                'description' => "Subscription payment: {$plan->name}",
                'callback_url' => route('subscriptions.callback'),
                'notification_id' => $settings->activeIpnId(),
                'billing_address' => [
                    'email_address' => $owner->email,
                    'phone_number' => $owner->phone,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ],
            ]);

            $transaction->update([
                'order_tracking_id' => $response['order_tracking_id'] ?? null,
                'redirect_url' => $response['redirect_url'] ?? null,
                'status' => 'submitted',
            ]);

            return $this->redirect($response['redirect_url'], navigate: false);
        } catch (PesapalException $e) {
            $transaction->update(['status' => 'failed']);
            session()->flash('error', 'Could not start payment: ' . $e->getMessage());
        }
    }

    private function splitName(?string $name): array
    {
        $parts = explode(' ', trim((string) $name), 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }

    public function render()
    {
        $plans = $this->business
            ? SubscriptionPlan::active()->forBusinessType($this->business->type)->orderBy('fee_amount')->get()
            : collect();

        return view('livewire.owner.subscription.index', [
            'plans' => $plans,
        ]);
    }
}
