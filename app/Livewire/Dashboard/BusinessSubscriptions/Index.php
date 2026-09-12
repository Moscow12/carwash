<?php

namespace App\Livewire\Dashboard\BusinessSubscriptions;

use App\Models\Business;
use App\Models\BusinessSubscription;
use App\Models\PaymentGatewaySetting;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionTransaction;
use App\Services\Pesapal\PesapalClient;
use App\Services\Pesapal\PesapalException;
use App\Services\Pesapal\SubscriptionPaymentProcessor;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    // Manage-subscription modal state
    public bool $showManageModal = false;
    public ?string $manageBusinessId = null;
    public ?string $manageBusinessName = null;
    /** assign | extend | manual | pesapal */
    public string $manageAction = 'assign';

    public string $selectedPlanId = '';
    public int $extendCycles = 1;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openManage(string $businessId): void
    {
        $business = Business::findOrFail($businessId);

        $this->manageBusinessId = $business->id;
        $this->manageBusinessName = $business->name;
        $this->manageAction = 'assign';
        $this->selectedPlanId = BusinessSubscription::getForBusiness($business->id)->plan_id ?? '';
        $this->extendCycles = 1;

        $this->showManageModal = true;
    }

    public function closeManage(): void
    {
        $this->showManageModal = false;
        $this->manageBusinessId = null;
        $this->manageBusinessName = null;
        $this->selectedPlanId = '';
        $this->extendCycles = 1;
        $this->resetErrorBag();
    }

    /** Admin override: assign/change plan and activate immediately, no payment involved. */
    public function assignPlan(): void
    {
        $this->validate(['selectedPlanId' => 'required|exists:subscription_plans,id']);

        $business = Business::findOrFail($this->manageBusinessId);
        $plan = SubscriptionPlan::findOrFail($this->selectedPlanId);

        BusinessSubscription::getForBusiness($business->id)->assignPlan($plan);

        session()->flash('success', "Plan assigned to {$business->name}.");
        $this->closeManage();
    }

    /** Extend the current plan's period by N billing cycles from the current expiry (or today if lapsed). */
    public function extendSubscription(): void
    {
        $this->validate(['extendCycles' => 'required|integer|min:1|max:24']);

        $business = Business::findOrFail($this->manageBusinessId);
        $subscription = BusinessSubscription::getForBusiness($business->id);

        if (!$subscription->plan) {
            session()->flash('error', 'This business has no plan assigned yet — assign one first.');
            return;
        }

        for ($i = 0; $i < $this->extendCycles; $i++) {
            $subscription->extend();
        }

        session()->flash('success', "Subscription extended for {$business->name}. New expiry: " . $subscription->expires_at->format('Y-m-d') . '.');
        $this->closeManage();
    }

    /** Admin-recorded offline payment (cash, bank transfer, etc). */
    public function recordManualPayment(): void
    {
        $this->validate(['selectedPlanId' => 'required|exists:subscription_plans,id']);

        $business = Business::findOrFail($this->manageBusinessId);
        $plan = SubscriptionPlan::findOrFail($this->selectedPlanId);

        $processor = new SubscriptionPaymentProcessor(PesapalClient::forActiveEnvironment());
        $processor->recordManualPayment($business, $plan);

        session()->flash('success', "Manual payment recorded and subscription activated for {$business->name}.");
        $this->closeManage();
    }

    /** Admin-initiated real Pesapal payment: redirects the admin's browser to Pesapal's hosted page. */
    public function payViaPesapal()
    {
        $this->validate(['selectedPlanId' => 'required|exists:subscription_plans,id']);

        $business = Business::findOrFail($this->manageBusinessId);
        $plan = SubscriptionPlan::findOrFail($this->selectedPlanId);
        $settings = PaymentGatewaySetting::current();

        $transaction = SubscriptionTransaction::create([
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'merchant_reference' => 'SUB-' . Str::upper(Str::random(12)),
            'amount' => $plan->fee_amount,
            'currency' => $plan->currency,
            'environment' => $settings->active_environment,
            'status' => 'pending',
        ]);

        try {
            $client = PesapalClient::forActiveEnvironment();
            $owner = $business->owner;
            $nameParts = explode(' ', trim((string) $owner?->name), 2);

            $response = $client->submitOrderRequest([
                'id' => $transaction->merchant_reference,
                'currency' => $plan->currency,
                'amount' => (float) $plan->fee_amount,
                'description' => "Subscription payment: {$plan->name} ({$business->name})",
                'callback_url' => route('subscriptions.callback'),
                'notification_id' => $settings->activeIpnId(),
                'billing_address' => [
                    'email_address' => $owner?->email,
                    'phone_number' => $owner?->phone,
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
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
            session()->flash('error', 'Could not start Pesapal payment: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['subscription.plan'])
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.dashboard.businesssubscriptions.index', [
            'businesses' => $businesses,
            'plans' => SubscriptionPlan::active()->orderBy('name')->get(),
        ]);
    }
}
