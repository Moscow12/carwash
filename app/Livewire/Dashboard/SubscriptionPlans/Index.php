<?php

namespace App\Livewire\Dashboard\SubscriptionPlans;

use App\Models\SubscriptionPlan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';

    public bool $showPlanModal = false;
    public ?string $editingPlanId = null;

    public $name = '';
    public $business_type = '';
    public $fee_amount = '';
    public $currency = 'TZS';
    public $billing_interval = 'monthly';
    public $description = '';
    public $is_active = true;

    /** Same business-type set Module::keyForBusinessType() understands. */
    public array $businessTypes = [
        'car_wash', 'carwash', 'hotel', 'restaurant', 'bar', 'cafe',
        'shop', 'pos', 'salon', 'spa', 'gym', 'rental', 'other',
    ];

    public array $billingIntervals = SubscriptionPlan::BILLING_INTERVAL_LABELS;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showPlanModal = true;
    }

    public function openEdit(string $planId): void
    {
        $plan = SubscriptionPlan::findOrFail($planId);

        $this->editingPlanId = $plan->id;
        $this->name = $plan->name;
        $this->business_type = $plan->business_type ?? '';
        $this->fee_amount = $plan->fee_amount;
        $this->currency = $plan->currency;
        $this->billing_interval = $plan->billing_interval;
        $this->description = $plan->description;
        $this->is_active = $plan->is_active;

        $this->showPlanModal = true;
    }

    public function savePlan(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'business_type' => 'nullable|in:' . implode(',', $this->businessTypes),
            'fee_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'billing_interval' => 'required|in:' . implode(',', array_keys($this->billingIntervals)),
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['business_type'] = $data['business_type'] ?: null;

        if ($this->editingPlanId) {
            SubscriptionPlan::findOrFail($this->editingPlanId)->update($data);
            session()->flash('success', 'Subscription plan updated.');
        } else {
            SubscriptionPlan::create($data);
            session()->flash('success', 'Subscription plan created.');
        }

        $this->closeModal();
    }

    public function deletePlan(string $planId): void
    {
        SubscriptionPlan::findOrFail($planId)->delete();
        session()->flash('success', 'Subscription plan deleted.');
    }

    public function closeModal(): void
    {
        $this->showPlanModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['editingPlanId', 'name', 'business_type', 'fee_amount', 'description']);
        $this->currency = 'TZS';
        $this->billing_interval = 'monthly';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $plans = SubscriptionPlan::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.dashboard.subscriptionplans.index', [
            'plans' => $plans,
        ]);
    }
}
