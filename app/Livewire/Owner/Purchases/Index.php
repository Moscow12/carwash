<?php

namespace App\Livewire\Owner\Purchases;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\purchase;
use App\Models\items;
use App\Models\suplier;
use App\Models\item_balance;

#[Layout('components.layouts.app-owner')]
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Filters
    public $search = '';
    public $selectedCarwash = '';
    public $purchaseStatusFilter = '';
    public $paymentStatusFilter = '';

    // Modal states
    public $showModal = false;
    public $editMode = false;
    public $purchaseId = null;

    // Form fields
    #[Rule('required|exists:items,id')]
    public $item_id = '';

    #[Rule('required|exists:supliers,id')]
    public $supplier_id = '';

    #[Rule('required|numeric|min:0.01')]
    public $quantity = '';

    #[Rule('required|numeric|min:0')]
    public $price = '';

    public $discount = '';

    #[Rule('required|in:paid,unpaid,pending,refunded,canceled')]
    public $payment_status = 'unpaid';

    #[Rule('required|in:received,pending,canceled')]
    public $purchase_status = 'pending';

    public $notes = '';

    // Data
    public $ownerCarwashes = [];
    public $availableItems = [];
    public $availableSuppliers = [];

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();
        $this->availableSuppliers = suplier::orderBy('name')->get();

        $firstCarwash = $this->ownerCarwashes->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
            $this->loadItems();
        }
    }

    public function updatedSelectedCarwash()
    {
        $this->loadItems();
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPurchaseStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter()
    {
        $this->resetPage();
    }

    public function loadItems()
    {
        if ($this->selectedCarwash) {
            $this->availableItems = items::where('carwash_id', $this->selectedCarwash)
                ->where('type', 'product')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        } else {
            $this->availableItems = [];
        }
    }

    // Modal actions
    public function openAddModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $purchase = purchase::find($id);
        if (!$purchase) return;

        $this->purchaseId = $id;
        $this->item_id = $purchase->item_id;
        $this->supplier_id = $purchase->supplier_id;
        $this->quantity = $purchase->quantity;
        $this->price = $purchase->price;
        $this->discount = $purchase->discount;
        $this->payment_status = $purchase->payment_status;
        $this->purchase_status = $purchase->purchase_status;
        $this->notes = $purchase->notes;
        $this->editMode = true;
        $this->showModal = true;
    }

    public function savePurchase()
    {
        $this->validate([
            'item_id' => 'required|exists:items,id',
            'supplier_id' => 'required|exists:supliers,id',
            'quantity' => 'required|numeric|min:0.01',
            'price' => 'required|numeric|min:0',
            'payment_status' => 'required|in:paid,unpaid,pending,refunded,canceled',
            'purchase_status' => 'required|in:received,pending,canceled',
        ]);

        // Verify item belongs to selected carwash
        $item = items::where('id', $this->item_id)
            ->where('carwash_id', $this->selectedCarwash)
            ->first();

        if (!$item) {
            session()->flash('error', 'Invalid item selected.');
            return;
        }

        DB::beginTransaction();
        try {
            if ($this->editMode && $this->purchaseId) {
                $purchase = purchase::find($this->purchaseId);
                $previousStatus = $purchase->purchase_status;
                $previousQuantity = $purchase->quantity;

                $purchase->update([
                    'item_id' => $this->item_id,
                    'supplier_id' => $this->supplier_id,
                    'quantity' => $this->quantity,
                    'price' => $this->price,
                    'discount' => $this->discount ?: null,
                    'payment_status' => $this->payment_status,
                    'purchase_status' => $this->purchase_status,
                    'notes' => $this->notes,
                ]);

                // Handle item balance updates
                if ($previousStatus !== 'received' && $this->purchase_status === 'received') {
                    $this->updateItemBalance($this->item_id, $this->quantity, 'purchase');
                } elseif ($previousStatus === 'received' && $this->purchase_status !== 'received') {
                    $this->updateItemBalance($this->item_id, -$previousQuantity, 'adjustment');
                } elseif ($previousStatus === 'received' && $this->purchase_status === 'received' && $previousQuantity != $this->quantity) {
                    $difference = $this->quantity - $previousQuantity;
                    $this->updateItemBalance($this->item_id, $difference, 'adjustment');
                }

                session()->flash('message', 'Purchase updated successfully.');
            } else {
                $purchase = purchase::create([
                    'item_id' => $this->item_id,
                    'user_id' => Auth::id(),
                    'supplier_id' => $this->supplier_id,
                    'carwash_id' => $this->selectedCarwash,
                    'quantity' => $this->quantity,
                    'price' => $this->price,
                    'discount' => $this->discount ?: null,
                    'payment_status' => $this->payment_status,
                    'purchase_status' => $this->purchase_status,
                    'notes' => $this->notes,
                ]);

                // Update item balance if received
                if ($this->purchase_status === 'received') {
                    $this->updateItemBalance($this->item_id, $this->quantity, 'purchase');
                }

                session()->flash('message', 'Purchase created successfully.');
            }

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving purchase: ' . $e->getMessage());
        }
    }

    private function updateItemBalance($itemId, $quantity, $transactionType)
    {
        $lastBalance = item_balance::where('item_id', $itemId)
            ->where('carwash_id', $this->selectedCarwash)
            ->latest()
            ->first();

        $previousBalance = $lastBalance ? $lastBalance->current_balance : 0;
        $newBalance = $previousBalance + $quantity;

        item_balance::create([
            'item_id' => $itemId,
            'user_id' => Auth::id(),
            'carwash_id' => $this->selectedCarwash,
            'previous_balance' => $previousBalance,
            'current_balance' => $newBalance,
            'stock_type' => $quantity >= 0 ? 'in' : 'out',
            'stransaction_type' => $transactionType,
            'invoice_number' => item_balance::generateInvoiceNumber(),
        ]);
    }

    public function deletePurchase($id)
    {
        $purchase = purchase::find($id);
        if (!$purchase) return;

        DB::beginTransaction();
        try {
            // Reverse balance if was received
            if ($purchase->purchase_status === 'received') {
                $this->selectedCarwash = $purchase->carwash_id;
                $this->updateItemBalance($purchase->item_id, -$purchase->quantity, 'adjustment');
            }

            $purchase->delete();
            DB::commit();
            session()->flash('message', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error deleting purchase.');
        }
    }

    public function updatePurchaseStatus($id, $status)
    {
        $purchase = purchase::find($id);
        if (!$purchase) return;

        $previousStatus = $purchase->purchase_status;

        DB::beginTransaction();
        try {
            $purchase->update(['purchase_status' => $status]);

            // Handle balance updates
            if ($previousStatus !== 'received' && $status === 'received') {
                $this->selectedCarwash = $purchase->carwash_id;
                $this->updateItemBalance($purchase->item_id, $purchase->quantity, 'purchase');
            } elseif ($previousStatus === 'received' && $status !== 'received') {
                $this->selectedCarwash = $purchase->carwash_id;
                $this->updateItemBalance($purchase->item_id, -$purchase->quantity, 'adjustment');
            }

            DB::commit();
            session()->flash('message', 'Purchase status updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating status.');
        }
    }

    public function updatePaymentStatus($id, $status)
    {
        $purchase = purchase::find($id);
        if (!$purchase) return;

        $purchase->update(['payment_status' => $status]);
        session()->flash('message', 'Payment status updated.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['item_id', 'supplier_id', 'quantity', 'price', 'discount', 'notes', 'purchaseId', 'editMode']);
        $this->payment_status = 'unpaid';
        $this->purchase_status = 'pending';
        $this->resetValidation();
    }

    public function getItemBalance($itemId)
    {
        $lastBalance = item_balance::where('item_id', $itemId)
            ->where('carwash_id', $this->selectedCarwash)
            ->latest()
            ->first();

        return $lastBalance ? $lastBalance->current_balance : 0;
    }

    public function render()
    {
        $carwashIds = Auth::user()->ownedCarwashes()->pluck('id');

        $purchases = purchase::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash))
            ->when($this->purchaseStatusFilter, fn($q) => $q->where('purchase_status', $this->purchaseStatusFilter))
            ->when($this->paymentStatusFilter, fn($q) => $q->where('payment_status', $this->paymentStatusFilter))
            ->when($this->search, function ($q) {
                $q->where(function($query) {
                    $query->whereHas('item', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
                          ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->with(['carwash', 'item', 'supplier', 'user'])
            ->latest()
            ->paginate(10);

        // Summary stats
        $baseQuery = purchase::whereIn('carwash_id', $carwashIds)
            ->when($this->selectedCarwash, fn($q) => $q->where('carwash_id', $this->selectedCarwash));

        $stats = [
            // Purchase Status
            'total' => (clone $baseQuery)->count(),
            'received' => (clone $baseQuery)->received()->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'canceled' => (clone $baseQuery)->canceled()->count(),
            // Payment Status
            'paid' => (clone $baseQuery)->paid()->count(),
            'unpaid' => (clone $baseQuery)->unpaid()->count(),
            'payment_pending' => (clone $baseQuery)->paymentPending()->count(),
            // Values
            'total_value' => (clone $baseQuery)->received()
                ->selectRaw('SUM(quantity * price - COALESCE(discount, 0)) as total')
                ->value('total') ?? 0,
            'unpaid_value' => (clone $baseQuery)->received()->unpaid()
                ->selectRaw('SUM(quantity * price - COALESCE(discount, 0)) as total')
                ->value('total') ?? 0,
            'paid_value' => (clone $baseQuery)->received()->paid()
                ->selectRaw('SUM(quantity * price - COALESCE(discount, 0)) as total')
                ->value('total') ?? 0,
        ];

        return view('livewire.owner.purchases.index', [
            'purchases' => $purchases,
            'stats' => $stats,
        ]);
    }
}
