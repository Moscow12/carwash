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

    // Payment
    public $showPaymentModal = false;
    public $paymentType = 'cash';
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
    public function openPaymentModal($type = 'cash')
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        $this->paymentType = $type;
        $this->paymentAmount = $this->cartTotal;
        $this->showPaymentModal = true;

        // Set default payment method
        if ($type === 'cash') {
            $cashMethod = collect($this->availablePaymentMethods)->firstWhere('name', 'Cash');
            $this->paymentMethodId = $cashMethod ? $cashMethod['id'] : '';
        }
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentAmount = 0;
        $this->paymentMethodId = '';
        $this->paymentNote = '';
    }

    public function processSale()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        if (!$this->paymentMethodId) {
            session()->flash('error', 'Please select a payment method.');
            return;
        }

        DB::beginTransaction();
        try {
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
                'payment_status' => $this->paymentType === 'credit' ? 'unpaid' : 'paid',
                'notes' => $this->paymentNote ?: null,
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

            // Create payment record if not credit
            if ($this->paymentType !== 'credit') {
                sales_payments::create([
                    'sale_id' => $sale->id,
                    'user_id' => Auth::id(),
                    'amount' => $this->paymentAmount,
                    'payment_date' => now(),
                    'payment_method_id' => $this->paymentMethodId,
                    'notes' => $this->paymentNote ?: null,
                ]);
            }

            DB::commit();

            $this->closePaymentModal();
            $this->clearCart();
            session()->flash('message', 'Sale completed successfully! Invoice: ' . $sale->invoice_number);
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

    public function render()
    {
        return view('livewire.owner.sales.posscreen');
    }
}
