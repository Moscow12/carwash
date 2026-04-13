<?php

namespace App\Livewire\Owner\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\PosOutlet;
use App\Models\items;
use App\Models\item_balance;
use App\Models\unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockTransferManagement extends Component
{
    use WithPagination;

    // UI State
    public $showModal = false;
    public $viewMode = 'list'; // list, create, view, approve, receive
    public $selectedTransfer = null;

    // Form Data
    public $from_outlet_id;
    public $to_outlet_id;
    public $transfer_date;
    public $notes;
    public $transferItems = [];
    public $searchItem = '';
    public $filteredItems = [];

    // Filters
    public $statusFilter = 'all';
    public $fromOutletFilter = 'all';
    public $toOutletFilter = 'all';
    public $searchTerm = '';

    // Receive quantities
    public $receiveQuantities = [];

    protected $rules = [
        'from_outlet_id' => 'required|uuid|different:to_outlet_id',
        'to_outlet_id' => 'required|uuid',
        'transfer_date' => 'required|date',
        'notes' => 'nullable|string',
        'transferItems.*.item_id' => 'required|uuid',
        'transferItems.*.quantity_sent' => 'required|numeric|min:0.001',
        'transferItems.*.unit_id' => 'nullable|uuid',
        'transferItems.*.notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->transfer_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $outlets = PosOutlet::where('business_id', Auth::user()->business_id)
            ->where('status', 'active')
            ->get();

        $query = StockTransfer::with(['fromOutlet', 'toOutlet', 'requestedBy', 'approvedBy', 'items.item'])
            ->where('business_id', Auth::user()->business_id);

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->fromOutletFilter !== 'all') {
            $query->where('from_outlet_id', $this->fromOutletFilter);
        }

        if ($this->toOutletFilter !== 'all') {
            $query->where('to_outlet_id', $this->toOutletFilter);
        }

        if ($this->searchTerm) {
            $query->where('reference_no', 'like', '%' . $this->searchTerm . '%');
        }

        $transfers = $query->latest()->paginate(15);

        return view('livewire.owner.inventory.stock-transfer-management', [
            'transfers' => $transfers,
            'outlets' => $outlets,
            'items' => items::where('business_id', Auth::user()->business_id)
                ->where('product_stock', 'yes')
                ->where('status', 'active')
                ->get(),
            'units' => unit::all(),
        ]);
    }

    public function createTransfer()
    {
        $this->reset(['from_outlet_id', 'to_outlet_id', 'transfer_date', 'notes', 'transferItems']);
        $this->transfer_date = now()->format('Y-m-d');
        $this->viewMode = 'create';
        $this->showModal = true;
    }

    public function addItem()
    {
        $this->transferItems[] = [
            'item_id' => '',
            'quantity_sent' => 0,
            'unit_id' => null,
            'notes' => '',
        ];
    }

    public function removeItem($index)
    {
        unset($this->transferItems[$index]);
        $this->transferItems = array_values($this->transferItems);
    }

    public function saveTransfer()
    {
        $this->validate();

        if (empty($this->transferItems)) {
            session()->flash('error', 'Please add at least one item to transfer');
            return;
        }

        DB::beginTransaction();
        try {
            // Generate reference number
            $lastTransfer = StockTransfer::latest()->first();
            $lastNumber = $lastTransfer ? intval(substr($lastTransfer->reference_no, 3)) : 0;
            $referenceNo = 'ST-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);

            // Create transfer
            $transfer = StockTransfer::create([
                'business_id' => Auth::user()->business_id,
                'reference_no' => $referenceNo,
                'from_outlet_id' => $this->from_outlet_id,
                'to_outlet_id' => $this->to_outlet_id,
                'status' => 'draft',
                'requested_by' => Auth::id(),
                'notes' => $this->notes,
            ]);

            // Create transfer items
            foreach ($this->transferItems as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->id,
                    'item_id' => $item['item_id'],
                    'quantity_sent' => $item['quantity_sent'],
                    'quantity_received' => 0,
                    'unit_id' => $item['unit_id'],
                    'notes' => $item['notes'],
                ]);
            }

            DB::commit();

            session()->flash('success', 'Stock transfer created successfully');
            $this->closeModal();
            $this->reset(['transferItems', 'from_outlet_id', 'to_outlet_id', 'notes']);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error creating transfer: ' . $e->getMessage());
        }
    }

    public function viewTransfer($id)
    {
        $this->selectedTransfer = StockTransfer::with(['fromOutlet', 'toOutlet', 'requestedBy', 'approvedBy', 'items.item', 'items.unit'])
            ->findOrFail($id);
        $this->viewMode = 'view';
        $this->showModal = true;
    }

    public function approveTransfer($id)
    {
        try {
            $transfer = StockTransfer::findOrFail($id);

            if ($transfer->status !== 'draft' && $transfer->status !== 'requested') {
                session()->flash('error', 'Only draft or requested transfers can be approved');
                return;
            }

            DB::beginTransaction();

            // Update transfer status
            $transfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]);

            // Deduct from source outlet item_balances
            foreach ($transfer->items as $transferItem) {
                $this->updateItemBalance(
                    $transfer->from_outlet_id,
                    $transferItem->item_id,
                    -$transferItem->quantity_sent,
                    'transfer_out',
                    $transfer->reference_no
                );
            }

            DB::commit();
            session()->flash('success', 'Transfer approved successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error approving transfer: ' . $e->getMessage());
        }
    }

    public function dispatchTransfer($id)
    {
        try {
            $transfer = StockTransfer::findOrFail($id);

            if ($transfer->status !== 'approved') {
                session()->flash('error', 'Only approved transfers can be dispatched');
                return;
            }

            $transfer->update([
                'status' => 'dispatched',
                'dispatched_at' => now(),
            ]);

            session()->flash('success', 'Transfer dispatched successfully');

        } catch (\Exception $e) {
            session()->flash('error', 'Error dispatching transfer: ' . $e->getMessage());
        }
    }

    public function showReceiveModal($id)
    {
        $this->selectedTransfer = StockTransfer::with(['fromOutlet', 'toOutlet', 'items.item', 'items.unit'])
            ->findOrFail($id);

        // Initialize receive quantities with sent quantities
        $this->receiveQuantities = [];
        foreach ($this->selectedTransfer->items as $item) {
            $this->receiveQuantities[$item->id] = $item->quantity_sent;
        }

        $this->viewMode = 'receive';
        $this->showModal = true;
    }

    public function receiveTransfer()
    {
        try {
            if (!$this->selectedTransfer) {
                session()->flash('error', 'No transfer selected');
                return;
            }

            if ($this->selectedTransfer->status !== 'dispatched') {
                session()->flash('error', 'Only dispatched transfers can be received');
                return;
            }

            DB::beginTransaction();

            // Update transfer items with received quantities and add to destination outlet
            foreach ($this->selectedTransfer->items as $transferItem) {
                $receivedQty = $this->receiveQuantities[$transferItem->id] ?? 0;

                // Update transfer item
                $transferItem->update([
                    'quantity_received' => $receivedQty,
                ]);

                // Add to destination outlet item_balances
                $this->updateItemBalance(
                    $this->selectedTransfer->to_outlet_id,
                    $transferItem->item_id,
                    $receivedQty,
                    'transfer_in',
                    $this->selectedTransfer->reference_no
                );
            }

            // Update transfer status
            $this->selectedTransfer->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            DB::commit();
            session()->flash('success', 'Transfer received successfully');
            $this->closeModal();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error receiving transfer: ' . $e->getMessage());
        }
    }

    public function cancelTransfer($id)
    {
        try {
            $transfer = StockTransfer::findOrFail($id);

            if ($transfer->status === 'received') {
                session()->flash('error', 'Received transfers cannot be cancelled');
                return;
            }

            DB::beginTransaction();

            // If transfer was approved, restore item balances
            if ($transfer->status === 'approved' || $transfer->status === 'dispatched') {
                foreach ($transfer->items as $transferItem) {
                    $this->updateItemBalance(
                        $transfer->from_outlet_id,
                        $transferItem->item_id,
                        $transferItem->quantity_sent,
                        'transfer_in',
                        $transfer->reference_no . '-CANCELLED'
                    );
                }
            }

            $transfer->update(['status' => 'cancelled']);

            DB::commit();
            session()->flash('success', 'Transfer cancelled successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error cancelling transfer: ' . $e->getMessage());
        }
    }

    protected function updateItemBalance($outlet_id, $item_id, $quantity, $transaction_type, $invoice_number)
    {
        // Get the latest balance for this item at this outlet
        $lastBalance = item_balance::where('outlet_id', $outlet_id)
            ->where('item_id', $item_id)
            ->latest()
            ->first();

        $previousBalance = $lastBalance ? $lastBalance->current_balance : 0;
        $currentBalance = $previousBalance + $quantity;

        // Create new item_balance record
        item_balance::create([
            'item_id' => $item_id,
            'user_id' => Auth::id(),
            'business_id' => Auth::user()->business_id,
            'outlet_id' => $outlet_id,
            'order_id' => null,
            'order_item_id' => null,
            'previous_balance' => $previousBalance,
            'current_balance' => $currentBalance,
            'quantity_changed' => $quantity,
            'movement_reason' => 'transfer',
            'stock_type' => $quantity > 0 ? 'in' : 'out',
            'stransaction_type' => $transaction_type,
            'invoice_number' => $invoice_number,
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['selectedTransfer', 'viewMode', 'receiveQuantities']);
    }
}
