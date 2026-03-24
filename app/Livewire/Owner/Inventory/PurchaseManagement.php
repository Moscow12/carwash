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
use App\Models\purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\suplier;
use App\Models\items;
use App\Models\unit;
use App\Models\payment_method;
use App\Models\TaxRate;

#[Layout('components.layouts.app-owner')]
class PurchaseManagement extends Component
{
    use WithPagination;

    public $selectedBusiness = null;
    public $selectedOutlet = null;
    public $search = '';
    public $statusFilter = 'all'; // all, pending, received, canceled
    public $paymentStatusFilter = 'all'; // all, unpaid, partial, paid

    // Modals
    public $showCreateModal = false;
    public $showViewModal = false;
    public $showPaymentModal = false;

    // Create Purchase Properties
    public $supplierId = '';
    public $referenceNo = '';
    public $receivedDate;
    public $purchaseStatus = 'pending';
    public $notes = '';

    // Purchase Items (array of items)
    public $purchaseItems = [];

    // Item search per row
    public $itemSearchTerms = [];
    public $showItemDropdown = [];

    // Payment Properties
    public $viewingPurchase = null;
    public $paymentAmount = 0;
    public $paymentMethodId = '';
    public $paymentReference = '';
    public $paymentNotes = '';
    public $paymentDate;

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

        $this->receivedDate = now()->format('Y-m-d');
        $this->paymentDate = now()->format('Y-m-d');
        $this->addPurchaseItemRow();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingPaymentStatusFilter()
    {
        $this->resetPage();
    }

    // Purchase Item Management
    public function addPurchaseItemRow()
    {
        $index = count($this->purchaseItems);
        $this->purchaseItems[] = [
            'item_id' => '',
            'item_name' => '',
            'unit_id' => '',
            'quantity' => 1,
            'unit_cost' => 0,
            'tax_rate_id' => '',
            'tax_amount' => 0,
            'discount' => 0,
            'subtotal' => 0,
            'expiry_date' => '',
            'batch_no' => '',
        ];
        $this->itemSearchTerms[$index] = '';
        $this->showItemDropdown[$index] = false;
    }

    public function removePurchaseItemRow($index)
    {
        if (count($this->purchaseItems) > 1) {
            unset($this->purchaseItems[$index]);
            unset($this->itemSearchTerms[$index]);
            unset($this->showItemDropdown[$index]);
            $this->purchaseItems = array_values($this->purchaseItems);
            $this->itemSearchTerms = array_values($this->itemSearchTerms);
            $this->showItemDropdown = array_values($this->showItemDropdown);
        }
    }

    public function selectItem($index, $itemId)
    {
        $item = items::find($itemId);
        if ($item) {
            $this->purchaseItems[$index]['item_id'] = $item->id;
            $this->purchaseItems[$index]['item_name'] = $item->name;
            $this->purchaseItems[$index]['unit_cost'] = $item->selling_price ?? 0;
            $this->itemSearchTerms[$index] = $item->name;
            $this->showItemDropdown[$index] = false;
            $this->calculateItemSubtotal($index);
        }
    }

    public function focusItemSearch($index)
    {
        $this->showItemDropdown[$index] = true;
    }

    public function blurItemSearch($index)
    {
        // Delay to allow click on dropdown
        $this->showItemDropdown[$index] = false;
    }

    public function getSearchedItemsProperty()
    {
        $results = [];

        foreach ($this->itemSearchTerms as $index => $searchTerm) {
            if (!$this->selectedBusiness) {
                $results[$index] = collect();
                continue;
            }

            $query = items::where('business_id', $this->selectedBusiness);

            if (!empty($searchTerm)) {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('code', 'like', '%' . $searchTerm . '%');
                });
            }

            $results[$index] = $query->orderBy('name')->limit(20)->get();
        }

        return $results;
    }

    public function calculateItemSubtotal($index)
    {
        if (!isset($this->purchaseItems[$index])) return;

        $item = &$this->purchaseItems[$index];
        $quantity = floatval($item['quantity'] ?? 0);
        $unitCost = floatval($item['unit_cost'] ?? 0);
        $discount = floatval($item['discount'] ?? 0);

        $itemTotal = $quantity * $unitCost;
        $afterDiscount = $itemTotal - $discount;

        // Calculate tax if tax rate selected
        $taxAmount = 0;
        if (!empty($item['tax_rate_id'])) {
            $taxRate = TaxRate::find($item['tax_rate_id']);
            if ($taxRate) {
                $taxAmount = $afterDiscount * ($taxRate->rate / 100);
            }
        }

        $item['tax_amount'] = round($taxAmount, 2);
        $item['subtotal'] = round($afterDiscount + $taxAmount, 2);
    }

    public function getTotalAmountProperty()
    {
        return collect($this->purchaseItems)->sum('subtotal');
    }

    // Create Purchase
    public function openCreateModal()
    {
        if (!$this->selectedBusiness || !$this->selectedOutlet) {
            session()->flash('error', 'Please select a business and outlet first.');
            return;
        }

        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    private function resetCreateForm()
    {
        $this->supplierId = '';
        $this->referenceNo = '';
        $this->receivedDate = now()->format('Y-m-d');
        $this->purchaseStatus = 'pending';
        $this->notes = '';
        $this->purchaseItems = [];
        $this->itemSearchTerms = [];
        $this->showItemDropdown = [];
        $this->addPurchaseItemRow();
    }

    public function createPurchase()
    {
        $this->validate([
            'supplierId' => 'required|exists:supliers,id',
            'receivedDate' => 'required|date',
            'purchaseStatus' => 'required|in:pending,received,canceled',
            'purchaseItems.*.item_id' => 'required|exists:items,id',
            'purchaseItems.*.quantity' => 'required|numeric|min:0.001',
            'purchaseItems.*.unit_cost' => 'required|numeric|min:0',
        ], [
            'supplierId.required' => 'Please select a supplier.',
            'purchaseItems.*.item_id.required' => 'Please select an item for each row.',
            'purchaseItems.*.quantity.required' => 'Quantity is required.',
            'purchaseItems.*.unit_cost.required' => 'Unit cost is required.',
        ]);

        DB::beginTransaction();
        try {
            // Calculate totals
            $subtotal = 0;
            $taxTotal = 0;
            $discountTotal = 0;

            foreach ($this->purchaseItems as $item) {
                $itemSubtotal = ($item['quantity'] * $item['unit_cost']);
                $subtotal += $itemSubtotal;
                $discountTotal += $item['discount'] ?? 0;
                $taxTotal += $item['tax_amount'] ?? 0;
            }

            $totalAmount = $subtotal - $discountTotal + $taxTotal;

            // Create purchase header
            $purchase = purchase::create([
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'supplier_id' => $this->supplierId,
                'user_id' => Auth::id(),
                'reference_no' => $this->referenceNo ?: 'PO-' . now()->format('YmdHis'),
                'received_date' => $this->receivedDate,
                'purchase_status' => $this->purchaseStatus,
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal,
                'discount' => $discountTotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance' => $totalAmount,
                'notes' => $this->notes,
            ]);

            // Create purchase items
            foreach ($this->purchaseItems as $item) {
                if (empty($item['item_id'])) continue;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'] ?: null,
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'tax_rate_id' => $item['tax_rate_id'] ?: null,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                    'subtotal' => $item['subtotal'],
                    'expiry_date' => $item['expiry_date'] ?: null,
                    'batch_no' => $item['batch_no'] ?: null,
                ]);

                // Update stock if received
                if ($this->purchaseStatus === 'received') {
                    $stockItem = items::find($item['item_id']);
                    if ($stockItem) {
                        $stockItem->increment('qty', $item['quantity']);
                    }
                }
            }

            DB::commit();

            session()->flash('message', 'Purchase created successfully.');
            $this->closeCreateModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error creating purchase: ' . $e->getMessage());
            Log::error('Purchase creation error: ' . $e->getMessage());
        }
    }

    // View Purchase
    public function viewPurchase($purchaseId)
    {
        $this->viewingPurchase = purchase::with([
            'purchaseItems.item',
            'purchaseItems.unit',
            'purchaseItems.taxRate',
            'supplier',
            'payments.paymentMethod',
            'payments.user'
        ])->find($purchaseId);

        if (!$this->viewingPurchase) {
            session()->flash('error', 'Purchase not found.');
            return;
        }

        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewingPurchase = null;
    }

    // Payment Management
    public function openPaymentModal($purchaseId)
    {
        $purchase = purchase::find($purchaseId);

        if (!$purchase) {
            session()->flash('error', 'Purchase not found.');
            return;
        }

        if ($purchase->isFullyPaid()) {
            session()->flash('error', 'This purchase is already fully paid.');
            return;
        }

        $this->viewingPurchase = $purchase;
        $this->paymentAmount = $purchase->remaining_balance;
        $this->paymentMethodId = '';
        $this->paymentReference = '';
        $this->paymentNotes = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->viewingPurchase = null;
        $this->paymentAmount = 0;
        $this->paymentMethodId = '';
        $this->paymentReference = '';
        $this->paymentNotes = '';
    }

    public function recordPayment()
    {
        if (!$this->viewingPurchase) {
            session()->flash('error', 'No purchase selected.');
            return;
        }

        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01|max:' . $this->viewingPurchase->remaining_balance,
            'paymentMethodId' => 'required|exists:payment_methods,id',
            'paymentDate' => 'required|date',
        ], [
            'paymentAmount.required' => 'Payment amount is required.',
            'paymentAmount.max' => 'Payment amount cannot exceed remaining balance.',
            'paymentMethodId.required' => 'Please select a payment method.',
        ]);

        DB::beginTransaction();
        try {
            // Create payment record
            PurchasePayment::create([
                'purchase_id' => $this->viewingPurchase->id,
                'payment_method_id' => $this->paymentMethodId,
                'business_id' => $this->selectedBusiness,
                'user_id' => Auth::id(),
                'amount' => $this->paymentAmount,
                'reference_no' => $this->paymentReference ?: null,
                'notes' => $this->paymentNotes ?: null,
                'payment_date' => $this->paymentDate,
            ]);

            // Recalculate purchase balance
            $this->viewingPurchase->recalculateBalance();

            DB::commit();

            session()->flash('message', 'Payment recorded successfully.');
            $this->closePaymentModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error recording payment: ' . $e->getMessage());
            Log::error('Purchase payment error: ' . $e->getMessage());
        }
    }

    // Update Purchase Status
    public function updatePurchaseStatus($purchaseId, $status)
    {
        $purchase = purchase::with('purchaseItems')->find($purchaseId);

        if (!$purchase) {
            session()->flash('error', 'Purchase not found.');
            return;
        }

        DB::beginTransaction();
        try {
            $oldStatus = $purchase->purchase_status;
            $purchase->purchase_status = $status;
            $purchase->save();

            // Update stock if status changed to received
            if ($status === 'received' && $oldStatus !== 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    $stockItem = items::find($item->item_id);
                    if ($stockItem) {
                        $stockItem->increment('qty', $item->quantity);
                    }
                }
            }

            // Reverse stock if status changed from received to something else
            if ($oldStatus === 'received' && $status !== 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    $stockItem = items::find($item->item_id);
                    if ($stockItem) {
                        $stockItem->decrement('qty', $item->quantity);
                    }
                }
            }

            DB::commit();

            session()->flash('message', 'Purchase status updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())->get();

        $outlets = collect();
        $suppliers = collect();
        $stockItems = collect();
        $units = collect();
        $paymentMethods = collect();
        $taxRates = collect();

        if ($this->selectedBusiness) {
            $outlets = PosOutlet::where('business_id', $this->selectedBusiness)->get();
            $suppliers = suplier::where('business_id', $this->selectedBusiness)->orderBy('name')->get();
            $stockItems = items::where('business_id', $this->selectedBusiness)->orderBy('name')->get();
            $paymentMethods = payment_method::where('status', 'active')->orderBy('name')->get();
            $taxRates = TaxRate::where('business_id', $this->selectedBusiness)->where('status', 'active')->get();
            $units = unit::orderBy('name')->get();
        }

        // Initialize purchases query
        if ($this->selectedOutlet) {
            $query = purchase::where('business_id', $this->selectedBusiness)
                ->where('outlet_id', $this->selectedOutlet)
                ->with(['supplier', 'purchaseItems', 'payments']);

            if ($this->search) {
                $query->where(function($q) {
                    $q->where('reference_no', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function($subQ) {
                          $subQ->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            }

            if ($this->statusFilter !== 'all') {
                $query->where('purchase_status', $this->statusFilter);
            }

            if ($this->paymentStatusFilter !== 'all') {
                $query->where('payment_status', $this->paymentStatusFilter);
            }

            $purchases = $query->orderByDesc('created_at')->paginate(20);
        } else {
            // Return empty paginator when no outlet selected
            $purchases = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('livewire.owner.inventory.purchase-management', [
            'businesses' => $businesses,
            'outlets' => $outlets,
            'purchases' => $purchases,
            'suppliers' => $suppliers,
            'stockItems' => $stockItems,
            'units' => $units,
            'paymentMethods' => $paymentMethods,
            'taxRates' => $taxRates,
        ]);
    }
}
