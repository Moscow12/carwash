<?php

namespace App\Livewire\Owner\Sales;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\sales;
use App\Models\sales_item;
use App\Models\sales_payments;
use App\Models\items;
use App\Models\category;
use App\Models\customers;
use App\Models\staffs;
use App\Models\payment_method;
use App\Models\item_balance;

#[Layout('components.layouts.app-owner')]
class Posscreen extends Component
{
    // Carwash selection
    public $selectedCarwash = '';
    public $ownerCarwashes = [];

    // Product filters
    public $search = '';
    public $selectedCategory = '';

    // Cart
    public $cart = [];
    public $cartTotal = 0;
    public $cartDiscount = 0;
    public $cartItemsCount = 0;

    // Customer
    public $customer_id = '';
    public $customerSearch = '';
    public $showCustomerModal = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';

    // Staff (for services)
    public $selectedStaff = '';

    // Payment - Multiple Payment Support
    public $showPaymentModal = false;
    public $paymentType = 'cash'; // cash, card, credit, multiple
    public $paymentRows = [];
    public $sellNote = '';
    public $staffNote = '';

    // Legacy single payment (kept for backward compatibility)
    public $paymentAmount = 0;
    public $paymentMethodId = '';
    public $paymentNote = '';

    // Plate number for services
    public $plateNumber = '';
    public $showPlateModal = false;
    public $currentItemForPlate = null;

    // Data collections
    public $availableItems = [];
    public $availableCategories = [];
    public $availableCustomers = [];
    public $availableStaffs = [];
    public $availablePaymentMethods = [];

    // Recent sales
    public $showRecentModal = false;
    public $recentSales = [];

    // Receipt
    public $showReceiptModal = false;
    public $lastSale = null;
    public $lastSaleItems = [];
    public $lastSalePayments = [];
    public $carwashInfo = null;
    public $carwashSettings = null;

    public function mount()
    {
        $this->ownerCarwashes = Auth::user()->ownedCarwashes()->orderBy('name')->get();

        $firstCarwash = $this->ownerCarwashes->first();
        if ($firstCarwash) {
            $this->selectedCarwash = $firstCarwash->id;
            $this->loadData();
        }
    }

    public function updatedSelectedCarwash()
    {
        $this->loadData();
        $this->clearCart();
    }

    public function updatedSearch()
    {
        $this->loadItems();
    }

    public function updatedSelectedCategory()
    {
        $this->loadItems();
    }

    public function loadData()
    {
        if (!$this->selectedCarwash) return;

        $this->loadItems();
        $this->loadCategories();
        $this->loadCustomers();
        $this->loadStaffs();
        $this->loadPaymentMethods();
    }

    public function loadItems()
    {
        if (!$this->selectedCarwash) {
            $this->availableItems = [];
            return;
        }

        $this->availableItems = items::where('carwash_id', $this->selectedCarwash)
            ->where('status', 'active')
            ->when($this->selectedCategory, fn($q) => $q->where('category_id', $this->selectedCategory))
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with('category', 'unit')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadCategories()
    {
        $this->availableCategories = category::where('carwash_id', $this->selectedCarwash)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadCustomers()
    {
        $this->availableCustomers = customers::where('carwash_id', $this->selectedCarwash)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadStaffs()
    {
        $this->availableStaffs = staffs::where('carwash_id', $this->selectedCarwash)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadPaymentMethods()
    {
        $this->availablePaymentMethods = payment_method::where('carwash_id', $this->selectedCarwash)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    // Cart management
    public function addToCart($itemId)
    {
        $item = collect($this->availableItems)->firstWhere('id', $itemId);
        if (!$item) return;

        // Check if item requires plate number
        if ($item['require_plate_number'] === 'yes') {
            $this->currentItemForPlate = $item;
            $this->showPlateModal = true;
            return;
        }

        $this->addItemToCart($item);
    }

    public function addItemWithPlate()
    {
        if (!$this->currentItemForPlate || !$this->plateNumber) {
            session()->flash('error', 'Please enter plate number.');
            return;
        }

        $this->addItemToCart($this->currentItemForPlate, $this->plateNumber);
        $this->plateNumber = '';
        $this->currentItemForPlate = null;
        $this->showPlateModal = false;
    }

    public function closePlateModal()
    {
        $this->showPlateModal = false;
        $this->plateNumber = '';
        $this->currentItemForPlate = null;
    }

    private function addItemToCart($item, $plateNumber = null)
    {
        $cartKey = $item['id'] . ($plateNumber ? '-' . $plateNumber : '');

        // Check if item already in cart
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'price' => $item['selling_price'],
                'quantity' => 1,
                'type' => $item['type'],
                'plate_number' => $plateNumber,
                'discount' => 0,
                'commission' => $item['commission'] ?? 0,
                'commission_type' => $item['commission_type'] ?? 'fixed',
                'require_plate_number' => $item['require_plate_number'],
            ];
        }

        $this->calculateTotals();
    }

    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($cartKey);
            return;
        }

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity'] = $quantity;
            $this->calculateTotals();
        }
    }

    public function incrementQuantity($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
            $this->calculateTotals();
        }
    }

    public function decrementQuantity($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            if ($this->cart[$cartKey]['quantity'] > 1) {
                $this->cart[$cartKey]['quantity']--;
            } else {
                unset($this->cart[$cartKey]);
            }
            $this->calculateTotals();
        }
    }

    public function removeFromCart($cartKey)
    {
        if (isset($this->cart[$cartKey])) {
            unset($this->cart[$cartKey]);
            $this->calculateTotals();
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->cartDiscount = 0;
        $this->cartItemsCount = 0;
        $this->customer_id = '';
        $this->selectedStaff = '';
    }

    private function calculateTotals()
    {
        $total = 0;
        $itemsCount = 0;

        foreach ($this->cart as $item) {
            $lineTotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
            $total += $lineTotal;
            $itemsCount += $item['quantity'];
        }

        $this->cartTotal = $total - $this->cartDiscount;
        $this->cartItemsCount = $itemsCount;
    }

    public function applyDiscount($discount)
    {
        $this->cartDiscount = max(0, floatval($discount));
        $this->calculateTotals();
    }

    // Customer management
    public function openCustomerModal()
    {
        $this->resetCustomerForm();
        $this->showCustomerModal = true;
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerForm();
    }

    public function resetCustomerForm()
    {
        $this->newCustomerName = '';
        $this->newCustomerPhone = '';
        $this->newCustomerEmail = '';
    }

    public function saveCustomer()
    {
        $this->validate([
            'newCustomerName' => 'required|min:2',
            'newCustomerPhone' => 'required|min:10',
            'newCustomerEmail' => 'nullable|email',
        ]);

        try {
            $customer = customers::create([
                'name' => $this->newCustomerName,
                'phone' => $this->newCustomerPhone,
                'email' => $this->newCustomerEmail ?: null,
                'carwash_id' => $this->selectedCarwash,
                'user_id' => Auth::id(),
                'status' => 'active',
            ]);

            $this->loadCustomers();
            $this->customer_id = $customer->id;
            $this->closeCustomerModal();
            session()->flash('message', 'Customer added successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding customer.');
        }
    }

    // Payment processing
    public function quickCashOut()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        // Get default cash payment method
        $cashMethod = collect($this->availablePaymentMethods)->firstWhere('name', 'Cash');
        $defaultMethodId = $cashMethod ? $cashMethod['id'] : ($this->availablePaymentMethods[0]['id'] ?? '');

        if (!$defaultMethodId) {
            session()->flash('error', 'No payment method available.');
            return;
        }

        // Set up payment for full amount with cash
        $this->paymentType = 'cash';
        $this->paymentRows = [
            [
                'amount' => $this->cartTotal,
                'payment_method_id' => $defaultMethodId,
                'note' => '',
            ]
        ];
        $this->sellNote = '';
        $this->staffNote = '';

        // Process the sale directly
        $this->processSale();
    }

    public function openPaymentModal($type = 'cash')
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        $this->paymentType = $type;
        $this->showPaymentModal = true;
        $this->sellNote = '';
        $this->staffNote = '';

        // Get default payment method based on type
        $defaultMethodId = '';
        if ($type === 'cash') {
            $cashMethod = collect($this->availablePaymentMethods)->firstWhere('name', 'Cash');
            $defaultMethodId = $cashMethod ? $cashMethod['id'] : ($this->availablePaymentMethods[0]['id'] ?? '');
        } elseif ($type === 'card') {
            $cardMethod = collect($this->availablePaymentMethods)->first(fn($m) => stripos($m['name'], 'card') !== false);
            $defaultMethodId = $cardMethod ? $cardMethod['id'] : ($this->availablePaymentMethods[0]['id'] ?? '');
        } elseif ($type === 'multiple') {
            // For multiple payments, start with an empty row
            $defaultMethodId = $this->availablePaymentMethods[0]['id'] ?? '';
        } else {
            $defaultMethodId = $this->availablePaymentMethods[0]['id'] ?? '';
        }

        // Initialize payment rows
        if ($type === 'multiple') {
            // For multiple payments, start with empty amount to encourage splitting
            $this->paymentRows = [
                [
                    'amount' => $this->cartTotal,
                    'payment_method_id' => $defaultMethodId,
                    'note' => '',
                ]
            ];
        } else {
            $this->paymentRows = [
                [
                    'amount' => $this->cartTotal,
                    'payment_method_id' => $defaultMethodId,
                    'note' => '',
                ]
            ];
        }

        // Legacy support
        $this->paymentAmount = $this->cartTotal;
        $this->paymentMethodId = $defaultMethodId;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentRows = [];
        $this->sellNote = '';
        $this->staffNote = '';
        $this->paymentAmount = 0;
        $this->paymentMethodId = '';
        $this->paymentNote = '';
    }

    // Multiple Payment Row Management
    public function addPaymentRow()
    {
        $defaultMethodId = $this->availablePaymentMethods[0]['id'] ?? '';
        $this->paymentRows[] = [
            'amount' => 0,
            'payment_method_id' => $defaultMethodId,
            'note' => '',
        ];
    }

    public function removePaymentRow($index)
    {
        if (count($this->paymentRows) > 1) {
            unset($this->paymentRows[$index]);
            $this->paymentRows = array_values($this->paymentRows);
        }
    }

    public function getTotalPayingProperty()
    {
        return collect($this->paymentRows)->sum('amount');
    }

    public function getChangeReturnProperty()
    {
        $totalPaying = $this->totalPaying;
        return $totalPaying > $this->cartTotal ? $totalPaying - $this->cartTotal : 0;
    }

    public function getBalanceDueProperty()
    {
        $totalPaying = $this->totalPaying;
        return $this->cartTotal > $totalPaying ? $this->cartTotal - $totalPaying : 0;
    }

    public function processSale()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        // Validate payment rows
        if ($this->paymentType !== 'credit') {
            $hasValidPayment = false;
            foreach ($this->paymentRows as $row) {
                if (!empty($row['payment_method_id']) && (float)$row['amount'] > 0) {
                    $hasValidPayment = true;
                    break;
                }
            }
            if (!$hasValidPayment) {
                session()->flash('error', 'Please add at least one valid payment.');
                return;
            }
        }

        DB::beginTransaction();
        try {
            // Calculate payment status
            $totalPaying = $this->totalPaying;
            $balanceDue = $this->balanceDue;

            $paymentStatus = 'paid';
            if ($this->paymentType === 'credit') {
                $paymentStatus = 'unpaid';
            } elseif ($balanceDue > 0) {
                $paymentStatus = 'partial';
            }

            // Combine notes
            $combinedNotes = '';
            if ($this->sellNote) {
                $combinedNotes .= 'Sale Note: ' . $this->sellNote;
            }
            if ($this->staffNote) {
                $combinedNotes .= ($combinedNotes ? ' | ' : '') . 'Staff Note: ' . $this->staffNote;
            }

            // Create sale
            $sale = sales::create([
                'carwash_id' => $this->selectedCarwash,
                'sale_status' => 'completed',
                'sale_type' => 'in-store',
                'sale_date' => now(),
                'payment_date' => now(),
                'payment_type' => $this->paymentType === 'credit' ? 'credit' : 'cash',
                'customer_id' => $this->customer_id ?: null,
                'user_id' => Auth::id(),
                'total_amount' => $this->cartTotal,
                'payment_status' => $paymentStatus,
                'notes' => $combinedNotes ?: null,
            ]);

            // Create sale items
            foreach ($this->cart as $cartItem) {
                sales_item::create([
                    'sale_id' => $sale->id,
                    'item_id' => $cartItem['id'],
                    'staff_id' => $this->selectedStaff ?: null,
                    'date' => now(),
                    'plate_number' => $cartItem['plate_number'] ?? null,
                    'discount' => $cartItem['discount'] ?? 0,
                    'commission' => $cartItem['commission'] ?? 0,
                    'price' => $cartItem['price'],
                    'quantity' => $cartItem['quantity'],
                ]);

                // Update stock for products
                $item = items::find($cartItem['id']);
                if ($item && $item->type === 'product' && $item->product_stock === 'yes') {
                    $this->updateItemBalance($cartItem['id'], -$cartItem['quantity'], 'sale');
                }
            }

            // Create payment records for each payment row (if not credit)
            if ($this->paymentType !== 'credit') {
                foreach ($this->paymentRows as $paymentRow) {
                    $amount = (float) ($paymentRow['amount'] ?? 0);
                    $methodId = $paymentRow['payment_method_id'] ?? '';

                    if ($amount > 0 && $methodId) {
                        sales_payments::create([
                            'sale_id' => $sale->id,
                            'user_id' => Auth::id(),
                            'amount' => $amount,
                            'payment_date' => now(),
                            'payment_method_id' => $methodId,
                            'notes' => $paymentRow['note'] ?? null,
                        ]);
                    }
                }
            }

            DB::commit();

            // Load sale data for receipt
            $this->showReceipt($sale->id);

            $this->closePaymentModal();
            $this->clearCart();

            $statusMsg = $paymentStatus === 'partial' ? ' (Partial Payment)' : '';
            session()->flash('message', 'Sale completed successfully!' . $statusMsg . ' Invoice: ' . $sale->invoice_number);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing sale: ' . $e->getMessage());
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
            'quantity_changed' => abs($quantity),
            'stock_type' => $quantity >= 0 ? 'in' : 'out',
            'stransaction_type' => $transactionType,
            'invoice_number' => item_balance::generateInvoiceNumber(),
        ]);
    }

    // Recent sales
    public function openRecentModal()
    {
        $this->recentSales = sales::where('carwash_id', $this->selectedCarwash)
            ->with(['customer', 'user', 'items.item'])
            ->latest()
            ->take(10)
            ->get()
            ->toArray();

        $this->showRecentModal = true;
    }

    public function closeRecentModal()
    {
        $this->showRecentModal = false;
        $this->recentSales = [];
    }

    // Receipt methods
    public function showReceipt($saleId)
    {
        $sale = sales::with(['customer', 'user', 'items.item', 'payments.paymentMethod', 'carwash.settings'])
            ->find($saleId);

        if (!$sale) return;

        $this->lastSale = $sale->toArray();
        $this->lastSaleItems = $sale->items->toArray();
        $this->lastSalePayments = $sale->payments->toArray();
        $this->carwashInfo = $sale->carwash ? $sale->carwash->toArray() : null;
        $this->carwashSettings = $sale->carwash && $sale->carwash->settings ? $sale->carwash->settings->toArray() : null;
        $this->showReceiptModal = true;
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->lastSale = null;
        $this->lastSaleItems = [];
        $this->lastSalePayments = [];
        $this->carwashInfo = null;
        $this->carwashSettings = null;
    }

    public function printReceipt()
    {
        $this->dispatch('printReceipt');
    }

    public function render()
    {
        return view('livewire.owner.sales.posscreen');
    }
}
