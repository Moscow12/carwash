<?php

namespace App\Livewire\Owner\Inventory;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Business;
use App\Models\PosOutlet;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\suplier;
use App\Models\items;
use App\Models\unit;
use App\Models\TaxRate;
use App\Models\item_balance;

#[Layout('components.layouts.app-owner')]
class CreatePurchase extends Component
{
    public $selectedBusiness = null;
    public $selectedOutlet = null;

    // Create Purchase Order Properties
    public $supplierId = '';
    public $poNumber = '';
    public $orderDate;
    public $expectedDate;
    public $status = 'draft';
    public $notes = '';

    // Purchase Items (array of items)
    public $purchaseItems = [];

    // Global item search
    public $itemSearch = '';
    public $showItemSearchDropdown = false;

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

        $this->orderDate = now()->format('Y-m-d');
        $this->expectedDate = now()->addDays(7)->format('Y-m-d'); // Default 7 days from now
    }

    // Purchase Item Management
    public function addItemToList($itemId)
    {
        try {
            $item = items::with('unit')->find($itemId);

            if (!$item) {
                $this->dispatch('alert', [
                    'type' => 'error',
                    'message' => 'Item not found.'
                ]);
                return;
            }

            // Check if item already exists in the list
            foreach ($this->purchaseItems as $existingItem) {
                if ($existingItem['item_id'] == $itemId) {
                    $this->dispatch('alert', [
                        'type' => 'warning',
                        'message' => 'Item "' . $item->name . '" is already in the list.'
                    ]);
                    return;
                }
            }

            $this->purchaseItems[] = [
                'item_id' => $item->id,
                'item_name' => $item->name,
                'unit_id' => $item->unit_id ?? '',
                'quantity_ordered' => 1,
                'quantity_received' => 0,
                'unit_cost' => $item->cost_price ?? $item->selling_price ?? 0,
                'tax_rate_id' => '',
                'tax_amount' => 0,
                'subtotal' => 0,
                'notes' => '',
            ];

            $index = count($this->purchaseItems) - 1;
            $this->calculateItemSubtotal($index);

            // Clear search
            $this->itemSearch = '';
            $this->showItemSearchDropdown = false;

            $this->dispatch('alert', [
                'type' => 'success',
                'message' => 'Item "' . $item->name . '" added successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding item to purchase list: ' . $e->getMessage());
            $this->dispatch('alert', [
                'type' => 'error',
                'message' => 'Error adding item: ' . $e->getMessage()
            ]);
        }
    }

    public function removePurchaseItemRow($index)
    {
        unset($this->purchaseItems[$index]);
        $this->purchaseItems = array_values($this->purchaseItems);
    }

    public function updatedItemSearch()
    {
        // Show dropdown when user types
        if (!empty($this->itemSearch)) {
            $this->showItemSearchDropdown = true;
        } else {
            $this->showItemSearchDropdown = false;
        }
    }

    public function focusItemSearch()
    {
        // Show dropdown on focus if there's a search term
        if (!empty($this->itemSearch)) {
            $this->showItemSearchDropdown = true;
        }
    }

    public function hideItemSearchDropdown()
    {
        $this->showItemSearchDropdown = false;
    }

    public function getSearchedItemsProperty()
    {
        if (!$this->selectedBusiness || empty($this->itemSearch)) {
            return collect();
        }

        $query = items::where('business_id', $this->selectedBusiness)
                     ->where('status', 'active')
                     ->where('type', 'product')
                     ->where('product_stock', 'yes');

        $query->where(function($q) {
            $q->where('name', 'like', '%' . $this->itemSearch . '%')
              ->orWhere('barcode', 'like', '%' . $this->itemSearch . '%');
        });

        return $query->select('id', 'name', 'barcode', 'selling_price', 'cost_price', 'unit_id')
                    ->orderBy('name')
                    ->limit(20)
                    ->get();
    }

    public function calculateItemSubtotal($index)
    {
        if (!isset($this->purchaseItems[$index])) return;

        $item = &$this->purchaseItems[$index];
        $quantity = floatval($item['quantity_ordered'] ?? 0);
        $unitCost = floatval($item['unit_cost'] ?? 0);

        $itemTotal = $quantity * $unitCost;

        // Calculate tax if tax rate selected
        $taxAmount = 0;
        if (!empty($item['tax_rate_id'])) {
            $taxRate = TaxRate::find($item['tax_rate_id']);
            if ($taxRate) {
                $taxAmount = $itemTotal * ($taxRate->rate / 100);
            }
        }

        $item['tax_amount'] = round($taxAmount, 2);
        $item['subtotal'] = round($itemTotal + $taxAmount, 2);
    }

    public function getTotalAmountProperty()
    {
        return collect($this->purchaseItems)->sum('subtotal');
    }

    public function getSubtotalProperty()
    {
        return collect($this->purchaseItems)->sum(function($item) {
            return ($item['quantity_ordered'] ?? 0) * ($item['unit_cost'] ?? 0);
        });
    }

    public function getTotalTaxProperty()
    {
        return collect($this->purchaseItems)->sum('tax_amount');
    }

    public function createPurchase()
    {
        if (!$this->selectedBusiness || !$this->selectedOutlet) {
            session()->flash('error', 'Please select a business and outlet first.');
            return;
        }

        if (empty($this->purchaseItems)) {
            session()->flash('error', 'Please add at least one item to the purchase order.');
            return;
        }

        $this->validate([
            'supplierId' => 'required|exists:supliers,id',
            'orderDate' => 'required|date',
            'expectedDate' => 'nullable|date|after_or_equal:orderDate',
            'status' => 'required|in:draft,submitted,approved,partially_received,received,cancelled',
            'purchaseItems' => 'required|array|min:1',
            'purchaseItems.*.item_id' => 'required|exists:items,id',
            'purchaseItems.*.quantity_ordered' => 'required|numeric|min:0.001',
            'purchaseItems.*.unit_cost' => 'required|numeric|min:0',
        ], [
            'supplierId.required' => 'Please select a supplier.',
            'orderDate.required' => 'Order date is required.',
            'expectedDate.after_or_equal' => 'Expected date must be on or after the order date.',
            'purchaseItems.required' => 'Please add at least one item.',
            'purchaseItems.*.item_id.required' => 'Please select an item for each row.',
            'purchaseItems.*.quantity_ordered.required' => 'Quantity is required.',
            'purchaseItems.*.unit_cost.required' => 'Unit cost is required.',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $taxTotal = 0;

            foreach ($this->purchaseItems as $item) {
                $itemSubtotal = ($item['quantity_ordered'] * $item['unit_cost']);
                $subtotal += $itemSubtotal;
                $taxTotal += $item['tax_amount'] ?? 0;
            }

            $totalAmount = $subtotal + $taxTotal;

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create([
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'supplier_id' => $this->supplierId,
                'po_number' => $this->poNumber ?: 'PO-' . now()->format('YmdHis'),
                'order_date' => $this->orderDate,
                'expected_date' => $this->expectedDate,
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $totalAmount,
                'status' => $this->status,
                'notes' => $this->notes,
                'requested_by' => Auth::id(),
            ]);

            // Create purchase order items
            foreach ($this->purchaseItems as $item) {
                if (empty($item['item_id'])) continue;

                // Determine quantity_received based on status
                $quantityReceived = ($this->status === 'received') ? $item['quantity_ordered'] : 0;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'] ?: null,
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => $quantityReceived,
                    'unit_cost' => $item['unit_cost'],
                    'tax_rate_id' => $item['tax_rate_id'] ?: null,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'notes' => $item['notes'] ?? '',
                ]);

                // Update stock if status is received
                if ($this->status === 'received') {
                    try {
                        // Get current stock balance
                        $lastBalance = item_balance::where('item_id', $item['item_id'])
                            ->where('business_id', $this->selectedBusiness)
                            ->latest()
                            ->first();

                        $previousBalance = $lastBalance ? (float) $lastBalance->current_balance : 0;
                        $newBalance = $previousBalance + $item['quantity_ordered'];

                        // Create item balance record
                        item_balance::create([
                            'item_id' => $item['item_id'],
                            'user_id' => Auth::id(),
                            'business_id' => $this->selectedBusiness,
                            'outlet_id' => $this->selectedOutlet,
                            'order_id' => null,
                            'previous_balance' => $previousBalance,
                            'current_balance' => $newBalance,
                            'quantity_changed' => $item['quantity_ordered'],
                            'quantity_ml' => 0,
                            'stock_type' => 'in',
                            'stransaction_type' => 'purchase',
                            'movement_reason' => 'normal',
                            'invoice_number' => $purchaseOrder->po_number,
                        ]);

                        Log::info('Item balance created for item: ' . $item['item_id'] . ', New balance: ' . $newBalance);
                    } catch (\Exception $e) {
                        Log::error('Error creating item balance: ' . $e->getMessage());
                        throw $e; // Re-throw to trigger rollback
                    }
                }
            }

            DB::commit();

            session()->flash('message', 'Purchase order created successfully.');
            return redirect()->route('owner.inventory.purchases');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error creating purchase order: ' . $e->getMessage());
            Log::error('Purchase order creation error: ' . $e->getMessage());
        }
    }

    public function cancel()
    {
        return redirect()->route('owner.inventory.purchases');
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())->get();

        $outlets = collect();
        $suppliers = collect();
        $units = collect();
        $taxRates = collect();

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)->get();
            $suppliers = suplier::where('business_id', $this->selectedBusiness)->orderBy('name')->get();
            $taxRates = TaxRate::where('business_id', $this->selectedBusiness)->where('status', 'active')->get();
            $units = unit::orderBy('name')->get();
        }

        return view('livewire.owner.inventory.create-purchase', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'suppliers' => $suppliers,
            'units' => $units,
            'taxRates' => $taxRates,
        ]);
    }
}
