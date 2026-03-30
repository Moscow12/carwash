<?php

namespace App\Livewire\Owner\Restaurant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PosTable;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\KitchenTicket;
use App\Models\MenuItem;
use App\Models\MenuCategory;
use App\Models\PosOutlet;
use App\Models\staffs;
use App\Models\customers;
use App\Models\payment_method;

#[Layout('components.layouts.app-owner')]
class RestaurantPOS extends Component
{
    // Business & Outlet Selection
    public $selectedBusiness = '';
    public $ownerBusinesses = [];
    public $selectedOutlet = '';
    public $availableOutlets = [];

    // Tables & Orders
    public $tables = [];
    public $selectedTableId = '';
    public $currentOrder = null;
    public $orderItems = [];

    // Menu
    public $menuCategories = [];
    public $menuItems = [];
    public $selectedCategory = '';
    public $menuSearch = '';

    // Order Management
    public $covers = 1;
    public $orderNotes = '';
    public $orderType = 'dine_in';

    // Staff & Customer
    public $selectedStaff = '';
    public $availableStaffs = [];
    public $selectedCustomer = '';
    public $availableCustomers = [];

    // Split Bill
    public $showSplitBillModal = false;
    public $splitCount = 2;
    public $splitItems = [];

    // Void Item
    public $showVoidModal = false;
    public $voidingItemId = null;
    public $voidReason = '';

    // Payment
    public $showPaymentModal = false;
    public $paymentAmount = 0;
    public $availablePaymentMethods = [];
    public $paymentRows = [];
    public $remainingAmount = 0;

    // Kitchen
    public $unsentItems = [];

    public function mount()
    {
        $user = Auth::user();

        // Get accessible businesses (handles both owned and assigned)
        $businessesWithOutlets = $user->assignedBusinesses()
            ->whereHas('outlets', function($query) {
                $query->where('type', 'restaurant')
                      ->orWhere('type', 'bar')
                      ->orWhere('type', 'cafe');
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $businessesByType = $user->assignedBusinesses()
            ->where('type', 'restaurant')
            ->orderBy('name')
            ->get(['id', 'name']);

        $allBusinesses = $businessesWithOutlets->merge($businessesByType)->unique('id');

        $this->ownerBusinesses = $allBusinesses->pluck('name', 'id')->toArray();

        if (!empty($this->ownerBusinesses)) {
            $this->selectedBusiness = array_key_first($this->ownerBusinesses);
            $this->loadData();
        }
    }

    public function updatedSelectedBusiness()
    {
        $this->loadData();
        $this->resetSelection();
    }

    public function updatedSelectedOutlet()
    {
        $this->loadTables();
        $this->loadMenu();
        $this->resetSelection();
    }

    public function updatedSelectedTableId()
    {
        $this->loadCurrentOrder();
    }

    public function updatedOrderType()
    {
        // If switching to takeaway/delivery, clear table
        if (in_array($this->orderType, ['takeaway', 'delivery'])) {
            $this->selectedTableId = '';
        }
    }

    public function updatedSelectedCategory()
    {
        $this->loadMenuItems();
    }

    public function updatedMenuSearch()
    {
        $this->loadMenuItems();
    }

    public function loadData()
    {
        if (!$this->selectedBusiness) return;

        $this->loadOutlets();
        $this->loadStaffs();
        $this->loadCustomers();
        $this->loadPaymentMethods();
    }

    public function loadOutlets()
    {
        $user = Auth::user();

        // Check if user has outlet-specific assignments for this business
        $assignedOutletIds = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->whereNotNull('outlet_id')
            ->pluck('outlet_id')
            ->toArray();

        // Check if user has business-level access (no specific outlet)
        $hasBusinessLevelAccess = DB::table('user_business_roles')
            ->where('user_id', $user->id)
            ->where('business_id', $this->selectedBusiness)
            ->where('is_active', true)
            ->whereNull('outlet_id')
            ->exists();

        // Check if user owns this business
        $ownsBusinesss = $user->assignedBusinesses()->where('id', $this->selectedBusiness)->exists();

        // Build query
        $query = PosOutlet::where('business_id', $this->selectedBusiness)
            ->where('status', 'active');

        // If user has specific outlet assignments and doesn't own business and doesn't have business-level access
        if (!empty($assignedOutletIds) && !$ownsBusinesss && !$hasBusinessLevelAccess) {
            $query->whereIn('id', $assignedOutletIds);
        }

        $this->availableOutlets = $query->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        if (!empty($this->availableOutlets) && !$this->selectedOutlet) {
            $this->selectedOutlet = array_key_first($this->availableOutlets);
            $this->loadTables();
            $this->loadMenu();
        }
    }

    public function loadTables()
    {
        if (!$this->selectedOutlet) return;

        $this->tables = PosTable::where('outlet_id', $this->selectedOutlet)
            ->where('is_active', true)
            ->with(['orders' => function ($query) {
                $query->where('status', '!=', 'paid')
                      ->where('status', '!=', 'voided')
                      ->latest();
            }])
            ->orderBy('section')
            ->orderBy('table_number')
            ->get()
            ->map(function ($table) {
                return [
                    'id' => $table->id,
                    'table_number' => $table->table_number,
                    'capacity' => $table->capacity,
                    'section' => $table->section,
                    'status' => $table->status,
                    'has_order' => $table->orders->count() > 0,
                    'order_id' => $table->orders->first()->id ?? null,
                ];
            })
            ->toArray();
    }

    public function loadMenu()
    {
        if (!$this->selectedOutlet) return;

        $this->menuCategories = MenuCategory::where('outlet_id', $this->selectedOutlet)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->pluck('name', 'id')
            ->toArray();

        $this->loadMenuItems();
    }

    public function loadMenuItems()
    {
        if (!$this->selectedOutlet) {
            $this->menuItems = [];
            return;
        }

        $query = MenuItem::where('outlet_id', $this->selectedOutlet)
            ->where('is_available', true)
            ->where('status', 'active');

        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }

        if ($this->menuSearch) {
            $query->where('name', 'like', "%{$this->menuSearch}%");
        }

        $this->menuItems = $query->orderBy('name')
            ->get(['id', 'name', 'description', 'price', 'image', 'is_vegetarian', 'is_vegan'])
            ->toArray();
    }

    public function loadStaffs()
    {
        $this->availableStaffs = staffs::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadCustomers()
    {
        $this->availableCustomers = customers::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function loadPaymentMethods()
    {
        $this->availablePaymentMethods = payment_method::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->pluck('name', 'id')
            ->toArray();
    }

    public function resetSelection()
    {
        $this->selectedTableId = '';
        $this->currentOrder = null;
        $this->orderItems = [];
        $this->covers = 1;
        $this->orderNotes = '';
        $this->selectedStaff = '';
        $this->selectedCustomer = '';
        $this->menuSearch = '';
        $this->selectedCategory = '';
        $this->orderType = 'dine_in';
    }

    // Table Management
    public function loadCurrentOrder()
    {
        if (!$this->selectedTableId) {
            $this->currentOrder = null;
            $this->orderItems = [];
            return;
        }

        // Find table and check for active order
        $table = collect($this->tables)->firstWhere('id', $this->selectedTableId);
        if (!$table) return;

        $this->orderType = 'dine_in';

        if ($table['has_order'] && $table['order_id']) {
            $this->loadOrder($table['order_id']);
        } else {
            $this->currentOrder = null;
            $this->orderItems = [];
        }
    }

    public function loadOrder($orderId)
    {
        $order = PosOrder::with(['items.menuItem', 'servedBy', 'customer'])
            ->find($orderId);

        if (!$order) return;

        $this->currentOrder = $order->toArray();
        $this->orderItems = $order->items->map(function ($item) {
            return [
                'id' => $item->id,
                'menu_item_id' => $item->menu_item_id,
                'name' => $item->menuItem->name ?? 'Unknown',
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal' => $item->subtotal,
                'status' => $item->status,
                'kitchen_notes' => $item->kitchen_notes,
                'sent_to_kitchen' => in_array($item->status, ['preparing', 'ready', 'served']),
            ];
        })->toArray();

        $this->covers = $order->covers;
        $this->orderNotes = $order->notes;
        $this->selectedStaff = $order->served_by;
        $this->selectedCustomer = $order->customer_id;
        $this->orderType = $order->order_type;

        $this->calculateUnsentItems();
    }


    // Order Management
    public function addMenuItem($menuItemId)
    {
        $menuItem = collect($this->menuItems)->firstWhere('id', $menuItemId);
        if (!$menuItem) return;

        // For takeaway/delivery, must create order first if none exists
        if (!$this->currentOrder && !$this->selectedTableId) {
            $this->createImmediateOrder();
        }

        // Check if item already in order
        $existingIndex = collect($this->orderItems)->search(function ($item) use ($menuItemId) {
            return $item['menu_item_id'] === $menuItemId && !$item['sent_to_kitchen'];
        });

        if ($existingIndex !== false) {
            // Increment quantity
            $this->orderItems[$existingIndex]['quantity']++;
            $this->orderItems[$existingIndex]['subtotal'] =
                $this->orderItems[$existingIndex]['quantity'] * $this->orderItems[$existingIndex]['unit_price'];
        } else {
            // Add new item
            $this->orderItems[] = [
                'id' => null,
                'menu_item_id' => $menuItemId,
                'name' => $menuItem['name'],
                'quantity' => 1,
                'unit_price' => $menuItem['price'],
                'subtotal' => $menuItem['price'],
                'status' => 'pending',
                'kitchen_notes' => '',
                'sent_to_kitchen' => false,
            ];
        }

        $this->calculateUnsentItems();
    }

    private function createImmediateOrder()
    {
        try {
            $orderData = [
                'order_no' => $this->generateOrderNumber(),
                'business_id' => $this->selectedBusiness,
                'outlet_id' => $this->selectedOutlet,
                'customer_id' => $this->selectedCustomer ?: null,
                'order_type' => $this->orderType,
                'covers' => $this->covers,
                'notes' => $this->orderNotes,
                'served_by' => $this->selectedStaff ?: null,
                'status' => 'open',
                'subtotal' => 0,
                'total' => 0,
            ];

            $this->currentOrder = PosOrder::create($orderData);
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    public function updateQuantity($index, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }

        if (isset($this->orderItems[$index])) {
            $this->orderItems[$index]['quantity'] = $quantity;
            $this->orderItems[$index]['subtotal'] = $quantity * $this->orderItems[$index]['unit_price'];
        }

        $this->calculateUnsentItems();
    }

    public function removeItem($index)
    {
        if (isset($this->orderItems[$index])) {
            // Only allow removal if not sent to kitchen
            if (!$this->orderItems[$index]['sent_to_kitchen']) {
                unset($this->orderItems[$index]);
                $this->orderItems = array_values($this->orderItems);
            } else {
                session()->flash('error', 'Cannot remove items already sent to kitchen. Use void instead.');
            }
        }

        $this->calculateUnsentItems();
    }

    public function calculateUnsentItems()
    {
        $this->unsentItems = collect($this->orderItems)
            ->filter(fn($item) => !$item['sent_to_kitchen'])
            ->values()
            ->toArray();
    }

    public function getTotalProperty()
    {
        return collect($this->orderItems)->sum('subtotal');
    }

    public function getUnsentTotalProperty()
    {
        return collect($this->unsentItems)->sum('subtotal');
    }

    // Kitchen Integration
    public function sendToKitchen()
    {
        if (empty($this->unsentItems)) {
            session()->flash('error', 'No items to send to kitchen.');
            return;
        }

        DB::beginTransaction();
        try {
            // Create or update order
            if ($this->currentOrder) {
                $order = PosOrder::find($this->currentOrder['id']);
                $order->update([
                    'covers' => $this->covers,
                    'notes' => $this->orderNotes,
                    'served_by' => $this->selectedStaff ?: null,
                    'status' => 'sent_to_kitchen',
                ]);
            } else {
                $orderData = [
                    'order_no' => $this->generateOrderNumber(),
                    'business_id' => $this->selectedBusiness,
                    'outlet_id' => $this->selectedOutlet,
                    'customer_id' => $this->selectedCustomer ?: null,
                    'order_type' => $this->orderType,
                    'covers' => $this->covers,
                    'notes' => $this->orderNotes,
                    'served_by' => $this->selectedStaff ?: null,
                    'status' => 'sent_to_kitchen',
                    'subtotal' => 0,
                    'total' => 0,
                ];

                // Add table_id only if table is selected
                if ($this->selectedTableId) {
                    $orderData['table_id'] = $this->selectedTableId;
                }

                $order = PosOrder::create($orderData);
            }

            // Create order items and kitchen tickets
            foreach ($this->unsentItems as $item) {
                $orderItem = PosOrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $item['menu_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'status' => 'preparing',
                    'kitchen_notes' => $item['kitchen_notes'] ?? null,
                ]);

                // Create kitchen ticket
                $menuItem = MenuItem::find($item['menu_item_id']);
                KitchenTicket::create([
                    'order_item_id' => $orderItem->id,
                    'order_id' => $order->id,
                    'outlet_id' => $this->selectedOutlet,
                    'station' => $menuItem->printer_station ?? 'Main Kitchen',
                    'status' => 'queued',
                    'received_at' => now(),
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $order->items()->sum('subtotal'),
                'total' => $order->items()->sum('subtotal'),
            ]);

            // Update table status if table is selected
            if ($this->selectedTableId) {
                PosTable::where('id', $this->selectedTableId)
                    ->update(['status' => 'occupied']);
            }

            DB::commit();

            $this->loadOrder($order->id);
            $this->loadTables();
            session()->flash('message', 'Order sent to kitchen successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error sending order to kitchen: ' . $e->getMessage());
        }
    }

    // Void Item
    public function openVoidModal($itemId)
    {
        $this->voidingItemId = $itemId;
        $this->voidReason = '';
        $this->showVoidModal = true;
    }

    public function closeVoidModal()
    {
        $this->showVoidModal = false;
        $this->voidingItemId = null;
        $this->voidReason = '';
    }

    public function voidItem()
    {
        if (!$this->voidReason) {
            session()->flash('error', 'Please provide a reason for voiding.');
            return;
        }

        try {
            $orderItem = PosOrderItem::find($this->voidingItemId);
            if ($orderItem) {
                $orderItem->update([
                    'status' => 'voided',
                    'voided_reason' => $this->voidReason,
                ]);

                // Update kitchen ticket
                KitchenTicket::where('order_item_id', $orderItem->id)
                    ->update(['status' => 'cancelled']);

                // Recalculate order total
                $order = $orderItem->order;
                $order->update([
                    'subtotal' => $order->items()->whereNotIn('status', ['voided'])->sum('subtotal'),
                    'total' => $order->items()->whereNotIn('status', ['voided'])->sum('subtotal'),
                ]);

                $this->loadOrder($order->id);
                $this->closeVoidModal();
                session()->flash('message', 'Item voided successfully.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error voiding item: ' . $e->getMessage());
        }
    }

    // Split Bill
    public function openSplitBillModal()
    {
        $this->splitCount = 2;
        $this->initializeSplitItems();
        $this->showSplitBillModal = true;
    }

    public function closeSplitBillModal()
    {
        $this->showSplitBillModal = false;
        $this->splitItems = [];
    }

    public function initializeSplitItems()
    {
        $activeItems = collect($this->orderItems)->where('status', '!=', 'voided')->values();

        $this->splitItems = [];
        for ($i = 0; $i < $this->splitCount; $i++) {
            $this->splitItems[$i] = [
                'diner' => $i + 1,
                'items' => [],
                'total' => 0,
            ];
        }
    }

    public function assignItemToSplit($itemIndex, $splitIndex)
    {
        $item = $this->orderItems[$itemIndex] ?? null;
        if (!$item || $item['status'] === 'voided') return;

        // Remove from other splits
        foreach ($this->splitItems as $idx => $split) {
            $this->splitItems[$idx]['items'] = collect($split['items'])
                ->reject(fn($i) => $i['index'] === $itemIndex)
                ->values()
                ->toArray();
        }

        // Add to selected split
        $this->splitItems[$splitIndex]['items'][] = [
            'index' => $itemIndex,
            'name' => $item['name'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['subtotal'],
        ];

        // Recalculate totals
        foreach ($this->splitItems as $idx => $split) {
            $this->splitItems[$idx]['total'] = collect($split['items'])->sum('subtotal');
        }
    }

    // Payment
    public function openPaymentModal()
    {
        if (!$this->currentOrder) {
            session()->flash('error', 'No active order.');
            return;
        }

        $this->paymentAmount = $this->total;
        $this->remainingAmount = $this->total;

        // Initialize with one payment row - pre-populate with first payment method
        $defaultMethodId = array_key_first($this->availablePaymentMethods) ?? '';
        $this->paymentRows = [
            ['amount' => $this->total, 'method_id' => $defaultMethodId]
        ];

        $this->showPaymentModal = true;
    }

    public function closePaymentModal()
    {
        $this->showPaymentModal = false;
        $this->paymentAmount = 0;
        $this->paymentRows = [];
        $this->remainingAmount = 0;
    }

    public function addPaymentRow()
    {
        $this->paymentRows[] = ['amount' => $this->remainingAmount, 'method_id' => ''];
        $this->calculateRemainingAmount();
    }

    public function removePaymentRow($index)
    {
        unset($this->paymentRows[$index]);
        $this->paymentRows = array_values($this->paymentRows);
        $this->calculateRemainingAmount();
    }

    public function updatedPaymentRows()
    {
        $this->calculateRemainingAmount();
    }

    private function calculateRemainingAmount()
    {
        $totalPaid = collect($this->paymentRows)->sum('amount');
        $this->remainingAmount = max(0, $this->total - $totalPaid);
    }

    public function quickPayCash()
    {
        // Find cash payment method
        $cashMethod = collect($this->availablePaymentMethods)->search(function($name) {
            return stripos($name, 'cash') !== false;
        });

        if ($cashMethod === false) {
            $cashMethod = array_key_first($this->availablePaymentMethods);
        }

        $this->paymentRows = [
            ['amount' => $this->total, 'method_id' => $cashMethod]
        ];
        $this->calculateRemainingAmount();
    }

    public function processPayment()
    {
        if (!$this->currentOrder) {
            session()->flash('error', 'No active order to process payment.');
            return;
        }

        // Validate payment rows
        $totalPayment = collect($this->paymentRows)->sum('amount');

        if ($totalPayment < $this->total) {
            session()->flash('error', 'Total payment amount must equal order total.');
            return;
        }

        foreach ($this->paymentRows as $index => $row) {
            if (empty($row['method_id']) || $row['amount'] <= 0) {
                session()->flash('error', 'Please complete all payment rows.');
                return;
            }
        }

        DB::beginTransaction();
        try {
            $order = PosOrder::find($this->currentOrder['id']);

            if (!$order) {
                throw new \Exception('Order not found.');
            }

            // Update order status
            $order->update([
                'status' => 'paid',
                'payment_status' => 'paid',
                'closed_at' => now(),
            ]);

            // Create payment records
            foreach ($this->paymentRows as $row) {
                // Create Payment record using polymorphic relationship
                \App\Models\Payment::create([
                    'business_id' => $this->selectedBusiness,
                    'payable_type' => 'App\Models\PosOrder',
                    'payable_id' => $order->id,
                    'amount' => $row['amount'],
                    'currency' => 'TZS',
                    'exchange_rate' => 1,
                    'amount_local' => $row['amount'],
                    'payment_method_id' => $row['method_id'],
                    'paid_at' => now(),
                    'received_by' => Auth::id(),
                    'reference_no' => 'POS-' . $order->order_no,
                    'status' => 'completed',
                ]);
            }

            // Update table status only if order has a table
            if ($this->selectedTableId) {
                PosTable::where('id', $this->selectedTableId)
                    ->update(['status' => 'available']);
            }

            DB::commit();

            $this->closePaymentModal();
            $this->resetSelection();
            $this->loadTables();
            session()->flash('message', 'Payment processed successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error processing payment: ' . $e->getMessage());
        }
    }


    private function generateOrderNumber()
    {
        $prefix = 'ORD';
        $year = date('Y');
        $lastOrder = PosOrder::where('order_no', 'like', "{$prefix}-{$year}-%")
            ->orderBy('order_no', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_no, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "{$prefix}-{$year}-{$newNumber}";
    }

    public function render()
    {
        return view('livewire.owner.restaurant.restaurant-p-o-s');
    }
}
