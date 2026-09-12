<?php

namespace App\Livewire\Dashboard\SubscriptionPayments;

use App\Models\SubscriptionTransaction;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public string $statusFilter = '';
    public string $environmentFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEnvironmentFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $transactions = SubscriptionTransaction::with(['business', 'plan'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->whereHas('business', fn ($bq) => $bq->where('name', 'like', '%' . $this->search . '%'))
                        ->orWhere('merchant_reference', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->environmentFilter, fn ($query) => $query->where('environment', $this->environmentFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.dashboard.subscriptionpayments.index', [
            'transactions' => $transactions,
            'totals' => [
                'completed' => SubscriptionTransaction::where('status', 'completed')->sum('amount'),
                'count' => SubscriptionTransaction::count(),
            ],
        ]);
    }
}
