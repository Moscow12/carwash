<?php

namespace App\Livewire\Owner\Reports;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\items;
use App\Models\category;
use App\Models\unit;
use App\Models\sales_item;
use App\Models\item_balance;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Stock extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    #[Url]
    public $carwash_id = '';

    #[Url]
    public $category_id = '';

    #[Url]
    public $unit_id = '';

    #[Url]
    public $search = '';

    public $perPage = 25;
    public $showFilters = true;

    public $carwashes = [];
    public $categories = [];
    public $units = [];

    // Summary data
    public $closingStockPurchase = 0;
    public $closingStockSale = 0;
    public $potentialProfit = 0;
    public $profitMarginPercent = 0;

    public function mount()
    {
        $owner = Auth::user();
        $carwashCollection = $owner->ownedCarwashes()->get();

        $this->carwashes = $carwashCollection->map(function ($carwash) {
            return [
                'id' => $carwash->id,
                'name' => $carwash->name,
            ];
        })->toArray();

        if (count($this->carwashes) > 0 && empty($this->carwash_id)) {
            $this->carwash_id = $this->carwashes[0]['id'];
        }

        $this->loadFilterData();
        $this->calculateSummary();
    }

    public function updatedCarwashId()
    {
        $this->loadFilterData();
        $this->calculateSummary();
        $this->resetPage();
    }

    public function updatedCategoryId()
    {
        $this->calculateSummary();
        $this->resetPage();
    }

    public function updatedUnitId()
    {
        $this->calculateSummary();
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function resetFilters()
    {
        $this->category_id = '';
        $this->unit_id = '';
        $this->search = '';
        $this->calculateSummary();
        $this->resetPage();
    }

    protected function loadFilterData()
    {
        if (empty($this->carwash_id)) {
            return;
        }

        $this->categories = category::where('carwash_id', $this->carwash_id)
            ->where('status', 'active')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'name' => $c->name])
            ->toArray();

        $this->units = unit::all()
            ->map(fn($u) => ['id' => $u->id, 'name' => $u->name])
            ->toArray();
    }

    protected function getBaseQuery()
    {
        $query = items::query()
            ->where('carwash_id', $this->carwash_id)
            ->where('type', '!=', 'Service') // Exclude services
            ->where('status', 'active');

        if (!empty($this->category_id)) {
            $query->where('category_id', $this->category_id);
        }

        if (!empty($this->unit_id)) {
            $query->where('unit_id', $this->unit_id);
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        return $query;
    }

    public function calculateSummary()
    {
        if (empty($this->carwash_id)) {
            return;
        }

        $items = $this->getBaseQuery()->get();

        $this->closingStockPurchase = 0;
        $this->closingStockSale = 0;

        // Get all item IDs
        $itemIds = $items->pluck('id')->toArray();

        // Bulk load latest balances for all items
        $latestBalances = item_balance::whereIn('item_id', $itemIds)
            ->where('carwash_id', $this->carwash_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('item_id')
            ->keyBy('item_id');

        foreach ($items as $item) {
            $stock = isset($latestBalances[$item->id])
                ? (float) $latestBalances[$item->id]->current_balance
                : 0;
            $costPrice = (float) ($item->cost_price ?? 0);
            $sellingPrice = (float) ($item->selling_price ?? 0);

            $this->closingStockPurchase += $stock * $costPrice;
            $this->closingStockSale += $stock * $sellingPrice;
        }

        $this->potentialProfit = $this->closingStockSale - $this->closingStockPurchase;

        if ($this->closingStockPurchase > 0) {
            $this->profitMarginPercent = ($this->potentialProfit / $this->closingStockPurchase) * 100;
        } else {
            $this->profitMarginPercent = 0;
        }
    }

    public function getStockDataProperty()
    {
        return $this->getBaseQuery()
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function getCurrentStock($itemId)
    {
        $lastBalance = item_balance::where('item_id', $itemId)
            ->where('carwash_id', $this->carwash_id)
            ->latest()
            ->first();

        return $lastBalance ? (float) $lastBalance->current_balance : 0;
    }

    public function getTotalUnitsSold($itemId)
    {
        return (float) sales_item::whereHas('sale', function ($query) {
            $query->where('carwash_id', $this->carwash_id)
                ->where('sale_status', 'completed');
        })->where('item_id', $itemId)->sum('quantity');
    }

    public function render()
    {
        $stockData = $this->stockData;

        // Bulk load stock balances for all items in current page
        $itemIds = $stockData->pluck('id')->toArray();
        $latestBalances = item_balance::whereIn('item_id', $itemIds)
            ->where('carwash_id', $this->carwash_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('item_id')
            ->keyBy('item_id');

        return view('livewire.owner.reports.stock', [
            'stockData' => $stockData,
            'stockBalances' => $latestBalances,
        ]);
    }
}
