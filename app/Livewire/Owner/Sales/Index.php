<?php

namespace App\Livewire\Owner\Sales;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\sales;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCarwash = '';
    public $saleStatusFilter = '';
    public $paymentStatusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // View sale details
    public $showDetailsModal = false;
    public $selectedSale = null;
    public $selectedSaleItems = [];

    // Summary stats
    public $totalSales = 0;
    public $totalRevenue = 0;
    public $paidSales = 0;
    public $unpaidSales = 0;

    public function mount()
    {
        $firstCarwash = Auth::user()->ownedCarwashes()->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
        }
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCarwash()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedSaleStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPaymentStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function loadStats()
    {
        if (!$this->selectedCarwash) {
            $this->totalSales = 0;
            $this->totalRevenue = 0;
            $this->paidSales = 0;
            $this->unpaidSales = 0;
            return;
        }

        $baseQuery = sales::where('carwash_id', $this->selectedCarwash)
            ->when($this->dateFrom, fn($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('sale_date', '<=', $this->dateTo));

        $this->totalSales = (clone $baseQuery)->count();
        $this->totalRevenue = (clone $baseQuery)->sum('total_amount');
        $this->paidSales = (clone $baseQuery)->where('payment_status', 'paid')->sum('total_amount');
        $this->unpaidSales = (clone $baseQuery)->where('payment_status', 'unpaid')->sum('total_amount');
    }

    public function viewSale($id)
    {
        $sale = sales::with(['customer', 'user', 'items.item', 'payments.paymentMethod'])->find($id);
        if ($sale) {
            $this->selectedSale = $sale->toArray();
            $this->selectedSaleItems = $sale->items->toArray();
            $this->showDetailsModal = true;
        }
    }

    public function closeDetailsModal()
    {
        $this->showDetailsModal = false;
        $this->selectedSale = null;
        $this->selectedSaleItems = [];
    }

    public function markAsPaid($id)
    {
        $sale = sales::find($id);
        if ($sale) {
            $sale->update(['payment_status' => 'paid', 'payment_date' => now()]);
            session()->flash('message', 'Sale marked as paid.');
            $this->loadStats();
        }
    }

    public function cancelSale($id)
    {
        $sale = sales::find($id);
        if ($sale && $sale->sale_status !== 'canceled') {
            $sale->update(['sale_status' => 'canceled']);
            session()->flash('message', 'Sale canceled successfully.');
            $this->loadStats();
        }
    }

    public function render()
    {
        $this->loadStats();

        $carwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $sales = sales::query()
            ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
            ->when($this->saleStatusFilter, fn($q) => $q->where('sale_status', $this->saleStatusFilter))
            ->when($this->paymentStatusFilter, fn($q) => $q->where('payment_status', $this->paymentStatusFilter))
            ->when($this->dateFrom, fn($q) => $q->whereDate('sale_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('sale_date', '<=', $this->dateTo))
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->whereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$this->search}%"))
                        ->orWhereHas('items', fn($iq) => $iq->where('plate_number', 'like', "%{$this->search}%"));
                });
            })
            ->with(['customer', 'user', 'items.item'])
            ->latest('sale_date')
            ->paginate(15);

        return view('livewire.owner.sales.index', [
            'sales' => $sales,
            'carwashes' => $carwashes
        ]);
    }
}
