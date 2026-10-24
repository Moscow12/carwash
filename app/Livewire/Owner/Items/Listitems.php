<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Traits\WithPrintMode;
use Illuminate\Support\Facades\Auth;
use App\Models\items;
use App\Models\category;
use App\Models\unit;
use App\Models\item_balance;

#[Layout('components.layouts.app-owner')]
class Listitems extends Component
{
    use WithPagination, WithPrintMode;

    /** Products below this balance are flagged as low stock. */
    public const LOW_STOCK_THRESHOLD = 10;

    // Filters
    public $selectedBusiness = '';
    public $typeFilter = '';
    public $categoryFilter = '';
    public $unitFilter = '';
    public $statusFilter = '';
    public $stockFilter = ''; // '', 'low' (below threshold, incl. out of stock) or 'out'
    public $search = '';
    public $perPage = 25;

    // Sorting
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    /** @var array<string, string> sortable field => default direction on first click */
    protected array $sortableFields = [
        'name' => 'asc',
        'cost_price' => 'desc',
        'selling_price' => 'desc',
        'profit_margin' => 'desc',
        'stock_value_cost' => 'desc',
        'stock_value_sell' => 'desc',
    ];

    /** @var array<string, string> computed sort fields => select alias; NULLs (services, no price) always sort last */
    protected array $computedSortColumns = [
        'profit_margin' => 'selling_margin',
        'stock_value_cost' => 'stock_value_cost',
        'stock_value_sell' => 'stock_value_sell',
    ];

    // Active tab
    public $activeTab = 'products';

    // Modal states
    public $showViewModal = false;
    public $showStockModal = false;
    public $viewingItem = null;
    public $stockItem = null;
    public $stockQuantity = 0;
    public $stockType = 'in';
    public $stockNote = '';

    // Stats
    public $totalItems = 0;
    public $totalServices = 0;
    public $totalProducts = 0;
    public $lowStockCount = 0;

    public function mount()
    {
        $firstBusiness = Auth::user()->assignedBusinesses()->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updating($property): void
    {
        if (in_array($property, ['typeFilter', 'categoryFilter', 'unitFilter', 'statusFilter', 'stockFilter', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'typeFilter', 'categoryFilter', 'unitFilter', 'statusFilter', 'stockFilter']);
        $this->resetPage();
    }

    /**
     * Quick filter from the summary cards: '' (all), 'product', 'Service' or 'low' stock.
     */
    public function quickFilter(string $filter): void
    {
        $this->typeFilter = in_array($filter, ['product', 'Service'], true) ? $filter : '';
        $this->stockFilter = $filter === 'low' ? 'low' : '';
        $this->resetPage();
    }

    /**
     * Sort by a whitelisted column; clicking the active column again flips the direction.
     */
    public function sortTable(string $field): void
    {
        if (! array_key_exists($field, $this->sortableFields)) {
            return;
        }

        $this->sortDirection = $this->sortBy === $field
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : $this->sortableFields[$field];
        $this->sortBy = $field;

        $this->resetPage();
    }

    public function updatedSelectedBusiness()
    {
        $this->resetPage();
        $this->categoryFilter = '';
        $this->loadStats();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function loadStats()
    {
        if (!$this->selectedBusiness) {
            $this->totalItems = 0;
            $this->totalServices = 0;
            $this->totalProducts = 0;
            $this->lowStockCount = 0;
            return;
        }

        $baseQuery = items::where('business_id', $this->selectedBusiness);
        $this->totalItems = (clone $baseQuery)->count();
        $this->totalServices = (clone $baseQuery)->services()->count();
        $this->totalProducts = (clone $baseQuery)->products()->count();

        [$balanceSql, $balanceBindings] = $this->latestBalanceSql();
        $this->lowStockCount = (clone $baseQuery)->products()
            ->whereRaw("COALESCE(({$balanceSql}), 0) < ?", [...$balanceBindings, self::LOW_STOCK_THRESHOLD])
            ->count();
    }

    /**
     * SQL for an item's latest ledger balance in the selected business, for use inside an items query.
     *
     * @return array{0: string, 1: array<int, mixed>}
     */
    protected function latestBalanceSql(): array
    {
        $latestBalance = item_balance::select('current_balance')
            ->whereColumn('item_balances.item_id', 'items.id')
            ->where('item_balances.business_id', $this->selectedBusiness)
            ->latest()
            ->limit(1);

        return [$latestBalance->toSql(), $latestBalance->getBindings()];
    }

    /**
     * Find an item that belongs to one of the user's businesses.
     */
    protected function findOwnedItem($id): ?items
    {
        return items::whereKey($id)
            ->whereIn('business_id', Auth::user()->assignedBusinesses()->pluck('id'))
            ->first();
    }

    public function viewItem($id)
    {
        $this->viewingItem = $this->findOwnedItem($id)?->load(['category', 'unit', 'business']);
        $this->showViewModal = $this->viewingItem !== null;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingItem = null;
    }

    public function openStockModal($id)
    {
        $this->stockItem = $this->findOwnedItem($id);

        if (! $this->stockItem) {
            return;
        }

        $this->stockQuantity = 0;
        $this->stockType = 'in';
        $this->stockNote = '';
        $this->showStockModal = true;
    }

    public function closeStockModal()
    {
        $this->showStockModal = false;
        $this->stockItem = null;
        $this->stockQuantity = 0;
        $this->stockType = 'in';
        $this->stockNote = '';
    }

    public function saveStock()
    {
        $this->validate([
            'stockQuantity' => 'required|numeric|min:0.01',
            'stockType' => 'required|in:in,out',
        ]);

        if (!$this->stockItem) {
            session()->flash('error', 'Item not found.');
            return;
        }

        try {
            // Get current balance
            $lastBalance = item_balance::where('item_id', $this->stockItem->id)
                ->where('business_id', $this->selectedBusiness)
                ->latest()
                ->first();

            $previousBalance = $lastBalance ? $lastBalance->current_balance : 0;

            if ($this->stockType === 'in') {
                $newBalance = $previousBalance + $this->stockQuantity;
            } else {
                $newBalance = $previousBalance - $this->stockQuantity;
                if ($newBalance < 0) {
                    session()->flash('error', 'Insufficient stock. Current balance: ' . $previousBalance);
                    return;
                }
            }

            // Create balance record
            $quantityChanged = $this->stockType === 'in' ? $this->stockQuantity : -$this->stockQuantity;

            item_balance::create([
                'item_id' => $this->stockItem->id,
                'user_id' => Auth::id(),
                'business_id' => $this->selectedBusiness,
                'previous_balance' => $previousBalance,
                'current_balance' => $newBalance,
                'quantity_changed' => $quantityChanged,
                'stock_type' => $this->stockType,
                'stransaction_type' => $this->stockType === 'in' ? 'restock' : 'adjustment',
                'invoice_number' => item_balance::generateInvoiceNumber(),
            ]);

            session()->flash('message', 'Stock updated successfully.');
            $this->closeStockModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error updating stock: ' . $e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $item = $this->findOwnedItem($id);
            if ($item) {
                // Check if item has sales
                if ($item->salesItems && $item->salesItems()->count() > 0) {
                    session()->flash('error', 'Cannot delete item with sales history.');
                    return;
                }
                $item->delete();
                $this->loadStats();
                session()->flash('message', 'Item deleted successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting item: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $item = $this->findOwnedItem($id);
        if ($item) {
            $item->update([
                'status' => $item->status === 'active' ? 'inactive' : 'active',
            ]);
            session()->flash('message', 'Item status updated.');
        }
    }

    public function getCategories()
    {
        if (!$this->selectedBusiness) {
            return collect();
        }

        return category::where('business_id', $this->selectedBusiness)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function getUnits()
    {
        return unit::where('status', 'active')->orderBy('name')->get();
    }

    public function getCurrentStock($itemId)
    {
        $lastBalance = item_balance::where('item_id', $itemId)
            ->where('business_id', $this->selectedBusiness)
            ->latest()
            ->first();

        return $lastBalance ? $lastBalance->current_balance : 0;
    }

    public function render()
    {
        $this->loadStats();

        $businesses = Auth::user()->assignedBusinesses()->orderBy('name')->get();

        $items = collect();

        if ($this->selectedBusiness) {
            $query = items::where('business_id', $this->selectedBusiness)
                ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
                ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
                ->when($this->unitFilter, fn($q) => $q->where('unit_id', $this->unitFilter))
                ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('name', 'like', "%{$this->search}%")
                            ->orWhere('description', 'like', "%{$this->search}%")
                            ->orWhere('barcode', 'like', "%{$this->search}%");

                        // "12" or "#12" finds SKU 12
                        $sku = ltrim(trim($this->search), '#');
                        if (ctype_digit($sku)) {
                            $query->orWhere('sku', (int) $sku);
                        }
                    });
                })
                ->with(['category', 'unit', 'business'])
                ->select('items.*')
                // Margin on selling price, aliased so it doesn't clash with the model's markup-based profit_margin accessor.
                // NULL when there is no selling price, so those items sort last.
                ->selectRaw('CASE WHEN selling_price > 0 THEN (selling_price - cost_price) * 100.0 / selling_price END as selling_margin');

            // Stock values from the latest ledger balance, so they can be sorted across pages; NULL for services
            [$balanceSql, $balanceBindings] = $this->latestBalanceSql();

            if ($this->stockFilter === 'low' || $this->stockFilter === 'out') {
                $query->where('type', 'product')->whereRaw(
                    $this->stockFilter === 'out' ? "COALESCE(({$balanceSql}), 0) <= 0" : "COALESCE(({$balanceSql}), 0) < ?",
                    $this->stockFilter === 'out' ? $balanceBindings : [...$balanceBindings, self::LOW_STOCK_THRESHOLD]
                );
            }

            foreach (['stock_value_cost' => 'cost_price', 'stock_value_sell' => 'selling_price'] as $alias => $priceColumn) {
                $query->selectRaw(
                    "CASE WHEN items.type = 'Service' THEN NULL ELSE COALESCE(({$balanceSql}), 0) * items.{$priceColumn} END as {$alias}",
                    $balanceBindings
                );
            }

            $sortBy = array_key_exists($this->sortBy, $this->sortableFields) ? $this->sortBy : 'name';
            $direction = $this->sortDirection === 'desc' ? 'desc' : 'asc';

            if (isset($this->computedSortColumns[$sortBy])) {
                $column = $this->computedSortColumns[$sortBy];
                $query->orderByRaw("{$column} IS NULL")->orderBy($column, $direction);
            } else {
                $query->orderBy($sortBy, $direction);
            }

            $query->orderBy('name');

            $items = $this->paginateForPrint($query);
        }

        // Get stock data for all items
        $stockData = [];
        if ($items->count() > 0) {
            $itemIds = $items->pluck('id')->toArray();
            $latestBalances = item_balance::whereIn('item_id', $itemIds)
                ->where('business_id', $this->selectedBusiness)
                ->orderBy('created_at', 'desc')
                ->get()
                ->unique('item_id')
                ->keyBy('item_id');

            foreach ($itemIds as $itemId) {
                $stockData[$itemId] = $latestBalances->get($itemId)?->current_balance ?? 0;
            }
        }

        return view('livewire.owner.items.listitems', [
            'items' => $items,
            'businesses' => $businesses,
            'categories' => $this->getCategories(),
            'units' => $this->getUnits(),
            'stockData' => $stockData,
        ]);
    }
}
