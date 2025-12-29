<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\items;
use App\Models\category;
use App\Models\unit;
use App\Models\item_balance;

#[Layout('components.layouts.app-owner')]
class Listitems extends Component
{
    use WithPagination;

    // Filters
    public $selectedCarwash = '';
    public $typeFilter = '';
    public $categoryFilter = '';
    public $unitFilter = '';
    public $statusFilter = '';
    public $search = '';
    public $perPage = 25;

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
        $firstCarwash = Auth::user()->ownedCarwashes()->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedCarwash()
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
        if (!$this->selectedCarwash) {
            $this->totalItems = 0;
            $this->totalServices = 0;
            $this->totalProducts = 0;
            $this->lowStockCount = 0;
            return;
        }

        $baseQuery = items::where('carwash_id', $this->selectedCarwash);
        $this->totalItems = (clone $baseQuery)->count();
        $this->totalServices = (clone $baseQuery)->services()->count();
        $this->totalProducts = (clone $baseQuery)->products()->count();
    }

    public function viewItem($id)
    {
        $this->viewingItem = items::with(['category', 'unit', 'carwash'])->find($id);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingItem = null;
    }

    public function openStockModal($id)
    {
        $this->stockItem = items::find($id);
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
                ->where('carwash_id', $this->selectedCarwash)
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
                'carwash_id' => $this->selectedCarwash,
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
            $item = items::find($id);
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
        $item = items::find($id);
        if ($item) {
            $item->update([
                'status' => $item->status === 'active' ? 'inactive' : 'active',
            ]);
            session()->flash('message', 'Item status updated.');
        }
    }

    public function getCategories()
    {
        if (!$this->selectedCarwash) {
            return collect();
        }

        return category::where('carwash_id', $this->selectedCarwash)
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
            ->where('carwash_id', $this->selectedCarwash)
            ->latest()
            ->first();

        return $lastBalance ? $lastBalance->current_balance : 0;
    }

    public function render()
    {
        $this->loadStats();

        $carwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $items = collect();

        if ($this->selectedCarwash) {
            $query = items::where('carwash_id', $this->selectedCarwash)
                ->when($this->typeFilter, fn($q) => $q->where('type', $this->typeFilter))
                ->when($this->categoryFilter, fn($q) => $q->where('category_id', $this->categoryFilter))
                ->when($this->unitFilter, fn($q) => $q->where('unit_id', $this->unitFilter))
                ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
                ->when($this->search, function ($q) {
                    $q->where(function ($query) {
                        $query->where('name', 'like', "%{$this->search}%")
                            ->orWhere('description', 'like', "%{$this->search}%");
                    });
                })
                ->with(['category', 'unit', 'carwash'])
                ->orderBy('name');

            $items = $query->paginate($this->perPage);
        }

        // Get stock data for all items
        $stockData = [];
        if ($items->count() > 0) {
            $itemIds = $items->pluck('id')->toArray();
            $latestBalances = item_balance::whereIn('item_id', $itemIds)
                ->where('carwash_id', $this->selectedCarwash)
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
            'carwashes' => $carwashes,
            'categories' => $this->getCategories(),
            'units' => $this->getUnits(),
            'stockData' => $stockData,
        ]);
    }
}
