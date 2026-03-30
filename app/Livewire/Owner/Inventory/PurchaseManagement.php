<?php

namespace App\Livewire\Owner\Inventory;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\items;
use App\Models\suplier;
use App\Models\item_balance;

#[Layout('components.layouts.app-owner')]
class PurchaseManagement extends Component
{
    use WithPagination;

    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $search = '';
    public $statusFilter = 'all'; // all, draft, submitted, approved, partially_received, received, cancelled

    // Modals
    public $showViewModal = false;

    // Viewing Purchase Order
    public $viewingPurchaseOrder = null;

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;

            $outlet = PosOutlet::where('business_id', $this->selectedBusiness)->first();
            if ($outlet) {
                $this->selectedOutlet = $outlet->id;
            }
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    // View Purchase Order
    public function viewPurchaseOrder($purchaseOrderId)
    {
        $this->viewingPurchaseOrder = PurchaseOrder::with([
            'items.item',
            'items.unit',
            'items.taxRate',
            'supplier',
            'requestedBy',
            'approvedBy'
        ])->find($purchaseOrderId);

        if (!$this->viewingPurchaseOrder) {
            session()->flash('error', 'Purchase order not found.');
            return;
        }

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingPurchaseOrder = null;
    }

    public function updatePurchaseOrderStatus($purchaseOrderId, $status)
    {
        $purchaseOrder = PurchaseOrder::find($purchaseOrderId);

        if (!$purchaseOrder) {
            session()->flash('error', 'Purchase order not found.');
            return;
        }

        DB::beginTransaction();
        try {
            $oldStatus = $purchaseOrder->status;
            $purchaseOrder->status = $status;

            // Auto-approve if status is changed to approved
            if ($status === 'approved' && $oldStatus !== 'approved') {
                $purchaseOrder->approved_by = Auth::id();
                $purchaseOrder->approved_at = now();
            }

            $purchaseOrder->save();

            // Update stock if status changed to received
            if ($status === 'received' && $oldStatus !== 'received') {
                foreach ($purchaseOrder->items as $item) {
                    // Get current stock balance
                    $lastBalance = item_balance::where('item_id', $item->item_id)
                        ->where('business_id', $purchaseOrder->business_id)
                        ->latest()
                        ->first();

                    $previousBalance = $lastBalance ? (float) $lastBalance->current_balance : 0;
                    $newBalance = $previousBalance + $item->quantity_ordered;

                    // Create item balance record
                    item_balance::create([
                        'item_id' => $item->item_id,
                        'user_id' => Auth::id(),
                        'business_id' => $purchaseOrder->business_id,
                        'outlet_id' => $purchaseOrder->outlet_id,
                        'order_id' => null,
                        'previous_balance' => $previousBalance,
                        'current_balance' => $newBalance,
                        'quantity_changed' => $item->quantity_ordered,
                        'quantity_ml' => 0,
                        'stock_type' => 'in',
                        'stransaction_type' => 'purchase',
                        'movement_reason' => 'normal',
                        'invoice_number' => $purchaseOrder->po_number,
                    ]);

                    // Update quantity_received
                    $item->quantity_received = $item->quantity_ordered;
                    $item->save();
                }
            }

            // Reverse stock if status changed from received to something else
            if ($oldStatus === 'received' && $status !== 'received') {
                foreach ($purchaseOrder->items as $item) {
                    if ($item->quantity_received > 0) {
                        // Get current stock balance
                        $lastBalance = item_balance::where('item_id', $item->item_id)
                            ->where('business_id', $purchaseOrder->business_id)
                            ->latest()
                            ->first();

                        $previousBalance = $lastBalance ? (float) $lastBalance->current_balance : 0;
                        $newBalance = $previousBalance - $item->quantity_received;

                        // Create reversal item balance record
                        item_balance::create([
                            'item_id' => $item->item_id,
                            'user_id' => Auth::id(),
                            'business_id' => $purchaseOrder->business_id,
                            'outlet_id' => $purchaseOrder->outlet_id,
                            'order_id' => null,
                            'previous_balance' => $previousBalance,
                            'current_balance' => $newBalance,
                            'quantity_changed' => -$item->quantity_received,
                            'quantity_ml' => 0,
                            'stock_type' => 'out',
                            'stransaction_type' => 'adjustment',
                            'movement_reason' => 'void',
                            'invoice_number' => $purchaseOrder->po_number,
                        ]);

                        // Reset quantity_received
                        $item->quantity_received = 0;
                        $item->save();
                    }
                }
            }

            DB::commit();

            session()->flash('message', 'Purchase order status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function deletePurchaseOrder($purchaseOrderId)
    {
        $purchaseOrder = PurchaseOrder::find($purchaseOrderId);

        if (!$purchaseOrder) {
            session()->flash('error', 'Purchase order not found.');
            return;
        }

        // Prevent deletion if already received or partially received
        if (in_array($purchaseOrder->status, ['received', 'partially_received'])) {
            session()->flash('error', 'Cannot delete a purchase order that has items received.');
            return;
        }

        DB::beginTransaction();
        try {
            // Delete items first
            PurchaseOrderItem::where('purchase_order_id', $purchaseOrderId)->delete();

            // Delete purchase order
            $purchaseOrder->delete();

            DB::commit();

            session()->flash('message', 'Purchase order deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error deleting purchase order: ' . $e->getMessage());
            Log::error('Purchase order deletion error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())->get();

        $outlets = collect();

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)->get();
        }

        // Initialize purchase orders query
        if ($this->selectedOutlet) {
            $query = PurchaseOrder::where('business_id', $this->selectedBusiness)
                ->where('outlet_id', $this->selectedOutlet)
                ->with(['supplier', 'items', 'requestedBy']);

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('po_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function($subQ) {
                          $subQ->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->statusFilter !== 'all') {
                $query->where('status', $this->statusFilter);
            }

            $purchaseOrders = $query->orderByDesc('created_at')->paginate(20);
        } else {
            // Return empty paginator when no outlet selected
            $purchaseOrders = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('livewire.owner.inventory.purchase-management', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }
}
