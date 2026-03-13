<?php

namespace App\Livewire\Owner\Items;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\items;
use App\Models\item_balance;
use App\Models\sales;
use App\Models\purchase;

#[Layout('components.layouts.app-owner')]
class History extends Component
{
    use WithPagination;

    public $itemId = '';
    public $selectedBusiness = '';
    public $perPage = 25;
    public $typeFilter = '';

    // Stats
    public $totalPurchase = 0;
    public $openingStock = 0;
    public $totalSellReturn = 0;
    public $stockTransfersIn = 0;
    public $totalSold = 0;
    public $totalAdjustment = 0;
    public $totalPurchaseReturn = 0;
    public $stockTransfersOut = 0;
    public $currentStock = 0;

    public function mount($itemId = null)
    {
        $firstBusiness = Auth::user()->ownedBusinesses()->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
        }

        if ($itemId) {
            $this->itemId = $itemId;
        }
    }

    public function updatedItemId()
    {
        $this->resetPage();
        $this->loadStats();
    }

    public function updatedSelectedCarwash()
    {
        $this->resetPage();
        $this->itemId = '';
        $this->loadStats();

        // Dispatch event to update Choices.js items dropdown
        $items = $this->getItems();
        $this->dispatch('itemsUpdated', [
            'items' => $items->map(fn($item) => ['id' => $item->id, 'name' => $item->name])->toArray(),
            'selectedItemId' => $this->itemId,
        ]);
    }

    #[On('setCarwash')]
    public function setCarwash($businessId)
    {
        $this->selectedBusiness = $businessId;
        $this->updatedSelectedCarwash();
    }

    #[On('setItem')]
    public function setItem($itemId)
    {
        $this->itemId = $itemId;
        $this->updatedItemId();
    }

    public function loadStats()
    {
        // Reset stats
        $this->totalPurchase = 0;
        $this->openingStock = 0;
        $this->totalSellReturn = 0;
        $this->stockTransfersIn = 0;
        $this->totalSold = 0;
        $this->totalAdjustment = 0;
        $this->totalPurchaseReturn = 0;
        $this->stockTransfersOut = 0;
        $this->currentStock = 0;

        if (!$this->itemId || !$this->selectedBusiness) {
            return;
        }

        $balances = item_balance::where('item_id', $this->itemId)
            ->where('business_id', $this->selectedBusiness)
            ->get();

        foreach ($balances as $balance) {
            $qty = abs($balance->quantity_changed ?? $balance->change_amount);

            switch ($balance->stransaction_type) {
                case 'purchase':
                case 'restock':
                    $this->totalPurchase += $qty;
                    break;
                case 'initial_stock':
                    $this->openingStock += $qty;
                    break;
                case 'return':
                    if ($balance->stock_type === 'in') {
                        $this->totalSellReturn += $qty;
                    } else {
                        $this->totalPurchaseReturn += $qty;
                    }
                    break;
                case 'sale':
                    $this->totalSold += $qty;
                    break;
                case 'adjustment':
                    if ($balance->stock_type === 'out') {
                        $this->totalAdjustment += $qty;
                    }
                    break;
                case 'refund':
                    if ($balance->stock_type === 'in') {
                        $this->totalSellReturn += $qty;
                    }
                    break;
            }
        }

        // Get current stock from latest balance
        $lastBalance = item_balance::where('item_id', $this->itemId)
            ->where('business_id', $this->selectedBusiness)
            ->latest()
            ->first();

        $this->currentStock = $lastBalance ? $lastBalance->current_balance : 0;
    }

    public function getItem()
    {
        if (!$this->itemId) {
            return null;
        }
        return items::with(['unit', 'category'])->find($this->itemId);
    }

    public function getItems()
    {
        if (!$this->selectedBusiness) {
            return collect();
        }

        return items::where('business_id', $this->selectedBusiness)
            ->orderBy('name')
            ->get();
    }

    public function getTransactionInfo($balance)
    {
        $info = [
            'customer_supplier' => '-',
            'reference' => $balance->invoice_number,
        ];

        // Try to find related sale or purchase based on invoice number
        if (in_array($balance->stransaction_type, ['sale', 'refund', 'return'])) {
            // Try to find sale by matching date/time
            $sale = sales::where('business_id', $this->selectedBusiness)
                ->whereDate('sale_date', $balance->created_at->toDateString())
                ->with('customer')
                ->first();

            if ($sale) {
                $info['customer_supplier'] = $sale->customer?->name ?? 'Walk-In Customer';
                $info['reference'] = substr($sale->id, 0, 4);
            } else {
                $info['customer_supplier'] = 'Walk-In Customer';
            }
        } elseif (in_array($balance->stransaction_type, ['purchase', 'restock'])) {
            $purchase = purchase::where('item_id', $this->itemId)
                ->where('business_id', $this->selectedBusiness)
                ->whereDate('created_at', $balance->created_at->toDateString())
                ->with('supplier')
                ->first();

            if ($purchase) {
                $info['customer_supplier'] = $purchase->supplier?->name ?? '-';
                $info['reference'] = 'PO' . date('Y', strtotime($balance->created_at)) . '/' . substr($purchase->id, 0, 4);
            }
        }

        return $info;
    }

    public function render()
    {
        $this->loadStats();

        $businesses = Auth::user()->ownedBusinesses()->orderBy('name')->get();
        $items = $this->getItems();
        $item = $this->getItem();

        $transactions = collect();

        if ($this->itemId && $this->selectedBusiness) {
            $query = item_balance::where('item_id', $this->itemId)
                ->where('business_id', $this->selectedBusiness)
                ->when($this->typeFilter, fn($q) => $q->where('stransaction_type', $this->typeFilter))
                ->with(['user'])
                ->orderBy('created_at', 'desc');

            $transactions = $query->paginate($this->perPage);
        }

        return view('livewire.owner.items.history', [
            'transactions' => $transactions,
            'businesses' => $businesses,
            'itemsList' => $items,
            'item' => $item,
        ]);
    }
}
