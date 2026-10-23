<?php

namespace App\Livewire\Owner\Stocktaking;

use App\Models\Stock;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public $selectedBusiness = '';

    public $statusFilter = '';

    public $ownerBusinesses = [];

    public function mount(): void
    {
        $this->ownerBusinesses = Auth::user()->assignedBusinesses()->orderBy('name')->get();
        $this->selectedBusiness = $this->ownerBusinesses->first()?->id ?? '';
    }

    public function updatingSelectedBusiness(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Open the business's current draft, or a fresh count sheet when there is none.
     */
    public function startCount(): void
    {
        if (! $this->ownerBusinesses->pluck('id')->contains($this->selectedBusiness)) {
            session()->flash('error', 'Please select a business first.');

            return;
        }

        $draft = Stock::draft()->where('business_id', $this->selectedBusiness)->latest()->first();

        $draft
            ? $this->redirectRoute('owner.stocktaking.sheet', ['stock' => $draft->id])
            : $this->redirectRoute('owner.stocktaking.sheet', ['business' => $this->selectedBusiness]);
    }

    public function deleteDraft(string $id): void
    {
        $stock = Stock::draft()
            ->whereKey($id)
            ->whereIn('business_id', $this->ownerBusinesses->pluck('id'))
            ->first();

        if (! $stock) {
            session()->flash('error', 'Only draft stock counts can be deleted.');

            return;
        }

        $stock->delete();
        session()->flash('message', 'Draft stock count deleted.');
    }

    public function render()
    {
        $businessIds = $this->ownerBusinesses->pluck('id');

        $baseQuery = Stock::query()
            ->whereIn('business_id', $businessIds)
            ->when($this->selectedBusiness, fn ($query) => $query->where('business_id', $this->selectedBusiness));

        $stocks = (clone $baseQuery)
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search, fn ($query) => $query->where('reference_no', 'like', "%{$this->search}%"))
            ->with(['business:id,name', 'createdBy:id,name', 'approvedBy:id,name'])
            ->withCount('items')
            ->withSum('items', 'variance')
            ->latest()
            ->paginate(10);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'drafts' => (clone $baseQuery)->draft()->count(),
            'approved' => (clone $baseQuery)->approved()->count(),
            'last_approved_at' => (clone $baseQuery)->approved()->max('approved_at'),
        ];

        return view('livewire.owner.stocktaking.index', [
            'stocks' => $stocks,
            'stats' => $stats,
        ]);
    }
}
