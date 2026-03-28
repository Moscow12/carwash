<?php

namespace App\Livewire\Owner\Bar;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\BarTab;
use App\Models\BarHappyHourPrice;
use App\Models\BarProfile;
use App\Models\MenuItemRecipe;
use App\Models\PosSession;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\PosOutlet;
use App\Models\payment_method;
use App\Models\customers;
use App\Models\item_balance;
use App\Models\items;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class BarPOS extends Component
{
    // Business/Outlet selection
    public $selectedBusiness = '';
    public $selectedOutlet = '';
    public $ownerBusinesses = [];
    public $availableOutlets = [];

    // Session
    public $currentSession = null;
    public $sessionRequired = true;
    public $showSessionModal = false;
    public $openingFloat = 0;

    // Menu filters
    public $search = '';
    public $selectedCategory = '';

    // Cart
    public $cart = [];
    public $cartTotal = 0;
    public $cartDiscount = 0;
    public $cartTax = 0;
    public $cartItemsCount = 0;

    // Tab management
    public $orderMode = 'immediate'; // 'immediate', 'tab', 'new_tab'
    public $selectedTab = null;
    public $availableTabs = [];
    public $showTabSelector = false;

    // New Tab Creation
    public $showNewTabModal = false;
    public $newTabName = '';
    public $newTabCustomerId = '';
    public $newTabGuestId = '';
    public $newTabFolioId = '';
    public $newTabTableId = '';

    // Customer
    public $customer_id = '';
    public $availableCustomers = [];
    public $showCustomerModal = false;
    public $newCustomerName = '';
    public $newCustomerPhone = '';
    public $newCustomerEmail = '';

    // Payment
    public $showPaymentModal = false;
    public $paymentRows = [];
    public $orderNotes = '';

    // Data collections
    public $availableMenuItems = [];
    public $availableCategories = [];
    public $availablePaymentMethods = [];
    public $barProfile = null;

    // Happy Hour
    public $happyHourActive = false;
    public $happyHourMessage = '';

    public function mount()
    {
        $this->ownerBusinesses = Auth::user()->ownedBusinesses()->orderBy('name')->get();

        $firstBusiness = $this->ownerBusinesses->first();
        if ($firstBusiness) {
            $this->selectedBusiness = $firstBusiness->id;
            $this->loadOutlets();

            $firstOutlet = collect($this->availableOutlets)->first();
            if ($firstOutlet) {
                $this->selectedOutlet = $firstOutlet['id'];
                $this->loadData();
            }
        }
    }

    public function updatedSelectedBusiness()
    {
        $this->loadOutlets();
        $this->selectedOutlet = '';
        $this->currentSession = null;
        $this->clearCart();
    }

    public function updatedSelectedOutlet()
    {
        $this->loadData();
        $this->clearCart();
    }

    public function updatedSearch()
    {
        $this->loadMenuItems();
    }

    public function updatedSelectedCategory()
    {
        $this->loadMenuItems();
    }

    public function loadOutlets()
    {
        if (!$this->selectedBusiness) {
            $this->availableOutlets = [];
            return;
        }

        $this->availableOutlets = PosOutlet::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadData()
    {
        if (!$this->selectedOutlet) return;

        $this->loadSession();
        $this->loadBarProfile();
        $this->loadMenuItems();
        $this->loadCategories();
        $this->loadCustomers();
        $this->loadPaymentMethods();
        $this->loadOpenTabs();
        $this->checkHappyHour();
    }

    public function loadSession()
    {
        // Get active session for current outlet
        $this->currentSession = PosSession::where('outlet_id', $this->selectedOutlet)
            ->whereNull('closed_at')
            ->latest()
            ->first();

        if (!$this->currentSession && $this->sessionRequired) {
            session()->flash('warning', 'No active POS session found. Please open a session first.');
        }
    }

    public function loadBarProfile()
    {
        $this->barProfile = BarProfile::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'active')
            ->first();
    }

    public function loadMenuItems()
    {
        if (!$this->selectedOutlet) {
            $this->availableMenuItems = [];
            return;
        }

        $query = MenuItem::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'active')
            ->where('is_available', true);

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        $this->availableMenuItems = $query
            ->with(['category', 'happyHourPrices', 'recipes.item', 'item.unit'])
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadCategories()
    {
        $this->availableCategories = MenuCategory::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadCustomers()
    {
        $this->availableCustomers = customers::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadPaymentMethods()
    {
        // Load all active payment methods (not business-specific)
        $this->availablePaymentMethods = payment_method::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function loadOpenTabs()
    {
        if (!$this->selectedOutlet) {
            $this->availableTabs = [];
            return;
        }

        $this->availableTabs = BarTab::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'open')
            ->with(['customer', 'table'])
            ->orderBy('tab_no')
            ->get()
            ->toArray();
    }

    public function checkHappyHour()
    {
        if (!$this->barProfile || !$this->barProfile->happy_hour_enabled) {
            $this->happyHourActive = false;
            return;
        }

        $now = Carbon::now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');

        // Check if current day is in happy hour days
        $happyHourDays = $this->barProfile->happy_hour_days ?? [];

        // Ensure it's an array
        if (!is_array($happyHourDays)) {
            $happyHourDays = [];
        }

        if (!empty($happyHourDays) && !in_array($currentDay, array_map('strtolower', $happyHourDays))) {
            $this->happyHourActive = false;
            return;
        }

        // Check if current time is within happy hour range
        $startTime = $this->barProfile->happy_hour_start;
        $endTime = $this->barProfile->happy_hour_end;

        if ($currentTime >= $startTime && $currentTime <= $endTime) {
            $this->happyHourActive = true;
            $this->happyHourMessage = "Happy Hour Active! ({$this->barProfile->happy_hour_discount_pct}% off)";
        } else {
            $this->happyHourActive = false;
        }
    }

    // Cart management
    public function addToCart($menuItemId)
    {
        $item = collect($this->availableMenuItems)->firstWhere('id', $menuItemId);
        if (!$item) return;

        // Calculate price (with happy hour if applicable)
        $price = $this->calculateItemPrice($item);

        // Check if item already in cart
        $cartKey = $item['id'];
        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['quantity']++;
        } else {
            $this->cart[$cartKey] = [
                'menu_item_id' => $item['id'],
                'name' => $item['name'],
                'price' => $price,
                'regular_price' => $item['price'],
                'quantity' => 1,
                'discount' => 0,
                'happy_hour_applied' => $price < $item['price'],
                'recipes' => $item['recipes'] ?? [],
                'linked_item_id' => $item['item_id'] ?? null,
            ];
        }

        $this->calculateTotals();
    }

    private function calculateItemPrice($item)
    {
        $regularPrice = $item['price'];

        // Check for specific happy hour price overrides first
        if (!empty($item['happy_hour_prices'])) {
            foreach ($item['happy_hour_prices'] as $hhPrice) {
                if ($hhPrice['status'] === 'active' && $this->isHappyHourPriceActive($hhPrice)) {
                    $happyHourModel = new BarHappyHourPrice($hhPrice);
                    return $happyHourModel->calculatePrice($regularPrice);
                }
            }
        }

        // Apply general happy hour discount from bar profile
        if ($this->happyHourActive && $this->barProfile) {
            $discountPct = $this->barProfile->happy_hour_discount_pct ?? 0;
            return $regularPrice * (1 - ($discountPct / 100));
        }

        return $regularPrice;
    }

    private function isHappyHourPriceActive($hhPrice)
    {
        $now = Carbon::now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');

        // Check days
        $days = $hhPrice['override_days'] ?? [];

        // Ensure it's an array
        if (!is_array($days)) {
            $days = [];
        }

        if (!empty($days) && !in_array($currentDay, array_map('strtolower', $days))) {
            return false;
        }

        // Check time
        $startTime = $hhPrice['start_time'];
        $endTime = $hhPrice['end_time'];

        return $currentTime >= $startTime && $currentTime <= $endTime;
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
        $this->cartTax = 0;
        $this->cartItemsCount = 0;
        $this->customer_id = '';
        $this->selectedTab = null;
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

    // Tab management
    public function setOrderMode($mode)
    {
        $this->orderMode = $mode;

        if ($mode === 'tab') {
            $this->loadOpenTabs();
            $this->showTabSelector = true;
        } elseif ($mode === 'new_tab') {
            $this->showNewTabModal = true;
        }
    }

    public function selectTab($tabId)
    {
        $this->selectedTab = collect($this->availableTabs)->firstWhere('id', $tabId);
        $this->showTabSelector = false;
        $this->orderMode = 'tab';
    }

    public function closeTabSelector()
    {
        $this->showTabSelector = false;
    }

    public function openNewTabModal()
    {
        $this->resetNewTabForm();
        $this->showNewTabModal = true;
    }

    public function closeNewTabModal()
    {
        $this->showNewTabModal = false;
        $this->resetNewTabForm();
    }

    private function resetNewTabForm()
    {
        $this->newTabName = '';
        $this->newTabCustomerId = '';
        $this->newTabGuestId = '';
        $this->newTabFolioId = '';
        $this->newTabTableId = '';
    }

    public function createNewTab()
    {
        $this->validate([
            'newTabName' => 'required|min:2',
        ]);

        try {
            if (!$this->currentSession) {
                session()->flash('error', 'No active session found.');
                return;
            }

            // Generate tab number
            $lastTab = BarTab::where('outlet_id', $this->selectedOutlet)
                ->whereDate('created_at', today())
                ->orderBy('tab_no', 'desc')
                ->first();

            $tabNo = $lastTab ? ((int)substr($lastTab->tab_no, -4) + 1) : 1;
            $tabNo = 'T' . str_pad($tabNo, 4, '0', STR_PAD_LEFT);

            $tab = BarTab::create([
                'tab_no' => $tabNo,
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'session_id' => $this->currentSession->id,
                'table_id' => $this->newTabTableId ?: null,
                'customer_id' => $this->newTabCustomerId ?: null,
                'guest_id' => $this->newTabGuestId ?: null,
                'folio_id' => $this->newTabFolioId ?: null,
                'tab_name' => $this->newTabName,
                'status' => 'open',
                'total_amount' => 0,
                'paid_amount' => 0,
                'balance' => 0,
                'opened_by' => Auth::id(),
                'opened_at' => now(),
            ]);

            $this->selectedTab = $tab->toArray();
            $this->orderMode = 'tab';
            $this->loadOpenTabs();
            $this->closeNewTabModal();

            session()->flash('message', 'Tab created successfully: ' . $tabNo);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating tab: ' . $e->getMessage());
        }
    }

    // Payment
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        if ($this->orderMode === 'immediate') {
            // Get default cash payment method
            $cashMethod = collect($this->availablePaymentMethods)->firstWhere('name', 'Cash');
            $defaultMethodId = $cashMethod ? $cashMethod['id'] : ($this->availablePaymentMethods[0]['id'] ?? '');

            $this->paymentRows = [
                [
                    'amount' => $this->cartTotal,
                    'payment_method_id' => $defaultMethodId,
                    'note' => '',
                ]
            ];
        }

        $this->showPaymentModal = true;
    }

    public function payWithCash()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        // Get cash payment method
        $cashMethod = collect($this->availablePaymentMethods)->firstWhere('name', 'Cash');

        if (!$cashMethod) {
            session()->flash('error', 'Cash payment method not found.');
            return;
        }

        $this->paymentRows = [
            [
                'amount' => $this->cartTotal,
                'payment_method_id' => $cashMethod['id'],
                'note' => '',
            ]
        ];

        $this->orderMode = 'immediate';
        $this->processOrder();
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentRows = [];
        $this->orderNotes = '';
    }

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

    public function processOrder()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty.');
            return;
        }

        if (!$this->currentSession) {
            session()->flash('error', 'No active POS session.');
            return;
        }

        DB::beginTransaction();
        try {
            // Generate order number with lock to prevent duplicates
            $lastOrder = PosOrder::where('outlet_id', $this->selectedOutlet)
                ->whereDate('created_at', today())
                ->lockForUpdate()
                ->orderBy('created_at', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            if ($lastOrder && preg_match('/(\d+)$/', $lastOrder->order_no, $matches)) {
                $orderNum = intval($matches[1]) + 1;
            } else {
                $orderNum = 1;
            }

            // Ensure uniqueness by checking if order number already exists
            do {
                $orderNo = 'ORD-' . now()->format('Ymd') . '-' . str_pad($orderNum, 4, '0', STR_PAD_LEFT);
                $exists = PosOrder::where('order_no', $orderNo)->exists();
                if ($exists) {
                    $orderNum++;
                }
            } while ($exists);

            // Get staff record for current user (if exists)
            $staffRecord = \App\Models\staffs::where('user_id', Auth::id())
                ->where('business_id', $this->selectedBusiness)
                ->first();

            // Create order
            $order = PosOrder::create([
                'order_no' => $orderNo,
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'session_id' => $this->currentSession->id,
                'order_type' => 'bar',
                'status' => $this->orderMode === 'tab' ? 'open' : 'paid',
                'customer_id' => $this->customer_id ?: null,
                'subtotal' => $this->cartTotal,
                'total' => $this->cartTotal,
                'notes' => $this->orderNotes,
                'served_by' => $staffRecord ? $staffRecord->id : null,
            ]);

            // Create order items and deduct recipes
            foreach ($this->cart as $cartItem) {
                $itemTotal = $cartItem['price'] * $cartItem['quantity'];
                $discountAmount = $cartItem['discount'] ?? 0;

                PosOrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem['menu_item_id'],
                    'quantity' => $cartItem['quantity'],
                    'unit_price' => $cartItem['price'],
                    'subtotal' => $itemTotal,
                    'discount_amount' => $discountAmount,
                    'total' => $itemTotal - $discountAmount,
                    'notes' => null,
                ]);

                // Deduct stock items (linked items and/or recipe ingredients)
                $this->deductStockItems($order->id, $cartItem);
            }

            // Handle tab or immediate payment
            if ($this->orderMode === 'tab' && $this->selectedTab) {
                // Attach order to tab
                DB::table('bar_tab_orders')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'tab_id' => $this->selectedTab['id'],
                    'order_id' => $order->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update tab total
                $tab = BarTab::find($this->selectedTab['id']);
                $tab->total_amount += $this->cartTotal;
                $tab->balance = $tab->total_amount - $tab->paid_amount;
                $tab->save();

                $message = 'Order added to tab: ' . $tab->tab_no;
            } else {
                // Process immediate payment
                foreach ($this->paymentRows as $paymentRow) {
                    $amount = (float) ($paymentRow['amount'] ?? 0);
                    $methodId = $paymentRow['payment_method_id'] ?? '';

                    if ($amount > 0 && $methodId) {
                        \App\Models\HotelPayment::create([
                            'business_id' => $this->selectedBusiness,
                            'pos_order_id' => $order->id,
                            'payment_method_id' => $methodId,
                            'amount' => $amount,
                            'currency' => 'TSh',
                            'status' => 'completed',
                            'received_by' => Auth::id(),
                            'paid_at' => now(),
                        ]);
                    }
                }

                $message = 'Order completed successfully!';
            }

            DB::commit();

            $this->closePaymentModal();
            $this->clearCart();

            session()->flash('message', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing order: ' . $e->getMessage());
            \Log::error('Bar POS Order Error: ' . $e->getMessage());
        }
    }

    private function deductStockItems($orderId, $cartItem)
    {
        // Priority 1: Deduct linked stock item (direct menu item -> stock item link)
        if (!empty($cartItem['linked_item_id'])) {
            $quantityToDeduct = $cartItem['quantity'];

            // Get current balance
            $lastBalance = item_balance::where('item_id', $cartItem['linked_item_id'])
                ->where('business_id', $this->selectedBusiness)
                ->latest('created_at')
                ->first();

            $previousBalance = $lastBalance ? (float) $lastBalance->current_balance : 0;
            $newBalance = $previousBalance - $quantityToDeduct;

            // Create item balance record
            item_balance::create([
                'item_id' => $cartItem['linked_item_id'],
                'user_id' => Auth::id(),
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'order_id' => $orderId,
                'previous_balance' => $previousBalance,
                'current_balance' => $newBalance,
                'quantity_changed' => $quantityToDeduct,
                'quantity_ml' => 0,
                'movement_reason' => 'normal',
                'stock_type' => 'out',
                'stransaction_type' => 'sale',
                'invoice_number' => 'ORD-' . now()->format('YmdHis'),
            ]);
        }
        // Priority 2: Deduct recipe ingredients (if no linked item)
        elseif (!empty($cartItem['recipes'])) {
            foreach ($cartItem['recipes'] as $recipe) {
                if (!$recipe['item_id']) continue;

                $quantityToDeduct = $recipe['quantity'] * $cartItem['quantity'];

                // Get current balance
                $lastBalance = item_balance::where('item_id', $recipe['item_id'])
                    ->where('business_id', $this->selectedBusiness)
                    ->latest('created_at')
                    ->first();

                $previousBalance = $lastBalance ? (float) $lastBalance->current_balance : 0;
                $newBalance = $previousBalance - $quantityToDeduct;

                // Create item balance record
                item_balance::create([
                    'item_id' => $recipe['item_id'],
                    'user_id' => Auth::id(),
                    'business_id' => $this->selectedBusiness,
                    'outlet_id' => $this->selectedOutlet,
                    'order_id' => $orderId,
                    'previous_balance' => $previousBalance,
                    'current_balance' => $newBalance,
                    'quantity_changed' => $quantityToDeduct,
                    'quantity_ml' => 0,
                    'movement_reason' => 'normal',
                    'stock_type' => 'out',
                    'stransaction_type' => 'sale',
                    'invoice_number' => 'ORD-' . now()->format('YmdHis'),
                ]);
            }
        }
    }

    // Customer Management
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

    private function resetCustomerForm()
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
                'business_id' => $this->selectedBusiness,
                'user_id' => Auth::id(),
                'status' => 'active',
            ]);

            $this->loadCustomers();
            $this->customer_id = $customer->id;
            $this->closeCustomerModal();

            session()->flash('message', 'Customer added successfully: ' . $customer->name);
        } catch (\Exception $e) {
            session()->flash('error', 'Error adding customer: ' . $e->getMessage());
        }
    }

    // Session Management
    public function openSessionModal()
    {
        if (!$this->selectedOutlet) {
            session()->flash('error', 'Please select an outlet first.');
            return;
        }

        $this->openingFloat = 0;
        $this->showSessionModal = true;
    }

    public function closeSessionModal()
    {
        $this->showSessionModal = false;
        $this->openingFloat = 0;
    }

    public function startSession()
    {
        $this->validate([
            'openingFloat' => 'required|numeric|min:0',
        ]);

        try {
            $session = PosSession::create([
                'outlet_id' => $this->selectedOutlet,
                'opened_by' => Auth::id(),
                'opening_float' => $this->openingFloat,
                'opened_at' => now(),
            ]);

            $this->currentSession = $session;
            $this->closeSessionModal();

            session()->flash('message', 'POS Session opened successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error opening session: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.owner.bar.bar-p-o-s');
    }
}
