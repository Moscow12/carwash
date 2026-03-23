<div class="bar-pos-container">
    <style>
        :root {
            --bar-primary: #6366f1;
            --bar-success: #10b981;
            --bar-warning: #f59e0b;
            --bar-danger: #ef4444;
            --bar-info: #06b6d4;
            --bar-bg: #0f172a;
            --bar-card: #1e293b;
            --bar-border: #334155;
        }

        .bar-pos-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--bar-bg);
            color: #f1f5f9;
        }

        /* Header */
        .bar-header {
            background: var(--bar-card);
            padding: 12px 20px;
            border-bottom: 2px solid var(--bar-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .bar-header-left {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .bar-header-right {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        /* Happy Hour Badge */
        .happy-hour-badge {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.875rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* Main Layout */
        .bar-main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .bar-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--bar-bg);
            overflow: hidden;
        }

        .bar-cart {
            width: 400px;
            background: var(--bar-card);
            border-left: 2px solid var(--bar-border);
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 1200px) {
            .bar-cart {
                width: 350px;
            }
        }

        @media (max-width: 992px) {
            .bar-main {
                flex-direction: column;
            }
            .bar-cart {
                width: 100%;
                border-left: none;
                border-top: 2px solid var(--bar-border);
                max-height: 40vh;
            }
        }

        /* Categories */
        .category-tabs {
            display: flex;
            gap: 8px;
            padding: 15px;
            overflow-x: auto;
            background: var(--bar-card);
            border-bottom: 1px solid var(--bar-border);
        }

        .category-tab {
            padding: 10px 20px;
            background: transparent;
            border: 2px solid var(--bar-border);
            border-radius: 8px;
            color: #94a3b8;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .category-tab:hover {
            border-color: var(--bar-primary);
            color: #f1f5f9;
        }

        .category-tab.active {
            background: var(--bar-primary);
            border-color: var(--bar-primary);
            color: white;
        }

        /* Menu Items Grid */
        .menu-items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }

        .menu-item-card {
            background: var(--bar-card);
            border: 2px solid var(--bar-border);
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
            position: relative;
        }

        .menu-item-card:hover {
            border-color: var(--bar-primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }

        .menu-item-card.happy-hour {
            border-color: var(--bar-warning);
        }

        .menu-item-card .item-name {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 8px;
            color: #f1f5f9;
        }

        .menu-item-card .item-price {
            color: var(--bar-success);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .menu-item-card .original-price {
            text-decoration: line-through;
            color: #64748b;
            font-size: 0.85rem;
            margin-right: 5px;
        }

        .happy-hour-tag {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--bar-warning);
            color: var(--bar-bg);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Cart */
        .cart-header {
            padding: 15px;
            border-bottom: 1px solid var(--bar-border);
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
        }

        .cart-item {
            background: var(--bar-bg);
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .cart-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 8px;
        }

        .cart-item-name {
            font-weight: 600;
            color: #f1f5f9;
        }

        .cart-item-price {
            color: var(--bar-success);
            font-weight: 700;
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        .qty-btn {
            width: 32px;
            height: 32px;
            background: var(--bar-primary);
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 700;
            cursor: pointer;
        }

        .qty-display {
            min-width: 40px;
            text-align: center;
            font-weight: 700;
            color: #f1f5f9;
        }

        /* Cart Summary */
        .cart-summary {
            padding: 15px;
            border-top: 2px solid var(--bar-border);
            background: var(--bar-bg);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #cbd5e1;
        }

        .summary-row.total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--bar-success);
            padding-top: 10px;
            border-top: 1px solid var(--bar-border);
        }

        /* Buttons */
        .btn-bar {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-bar-primary {
            background: var(--bar-primary);
            color: white;
        }

        .btn-bar-success {
            background: var(--bar-success);
            color: white;
        }

        .btn-bar-warning {
            background: var(--bar-warning);
            color: var(--bar-bg);
        }

        .btn-bar-danger {
            background: var(--bar-danger);
            color: white;
        }

        .btn-bar:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Modals */
        .bar-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .bar-modal-content {
            background: var(--bar-card);
            border-radius: 12px;
            padding: 24px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .bar-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .bar-modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #f1f5f9;
        }

        .tab-list-item {
            background: var(--bar-bg);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: pointer;
            border: 2px solid var(--bar-border);
            transition: all 0.2s;
        }

        .tab-list-item:hover {
            border-color: var(--bar-primary);
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            color: #cbd5e1;
            font-weight: 600;
        }

        .form-control-bar {
            width: 100%;
            padding: 10px;
            background: var(--bar-bg);
            border: 2px solid var(--bar-border);
            border-radius: 6px;
            color: #f1f5f9;
        }

        .form-control-bar:focus {
            outline: none;
            border-color: var(--bar-primary);
        }

        .search-box {
            width: 100%;
            padding: 12px 20px;
            background: var(--bar-bg);
            border: 2px solid var(--bar-border);
            border-radius: 8px;
            color: #f1f5f9;
            font-size: 1rem;
        }

        .order-mode-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .mode-btn {
            flex: 1;
            padding: 10px;
            background: var(--bar-bg);
            border: 2px solid var(--bar-border);
            border-radius: 8px;
            color: #cbd5e1;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .mode-btn.active {
            background: var(--bar-primary);
            border-color: var(--bar-primary);
            color: white;
        }
    </style>

    <!-- Header -->
    <div class="bar-header">
        <div class="bar-header-left">
            <div>
                <select wire:model.live="selectedBusiness" class="form-control-bar" style="width: 200px;">
                    @foreach($ownerBusinesses as $biz)
                        <option value="{{ $biz->id }}">{{ $biz->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="selectedOutlet" class="form-control-bar" style="width: 200px;">
                    <option value="">Select Outlet</option>
                    @foreach($availableOutlets as $outlet)
                        <option value="{{ $outlet['id'] }}">{{ $outlet['name'] }}</option>
                    @endforeach
                </select>
                @if(count($availableOutlets) === 0 && $selectedBusiness)
                    <a href="{{ route('owner.hotel.pos-outlets') }}" target="_blank"
                       style="color: var(--bar-warning); font-size: 0.75rem; text-decoration: underline; margin-left: 5px;">
                        + Add Outlet
                    </a>
                @endif
            </div>
            @if($currentSession)
                <div style="color: var(--bar-success); font-weight: 600;">
                    ● Session Active
                </div>
            @elseif($selectedOutlet)
                <div style="color: var(--bar-warning); font-weight: 600;">
                    ⚠ No Active Session
                </div>
            @endif
        </div>
        <div class="bar-header-right">
            @if($happyHourActive)
                <div class="happy-hour-badge">
                    🍹 {{ $happyHourMessage }}
                </div>
            @endif
            <div style="color: #cbd5e1;">
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
        </div>
    </div>

    <!-- Empty State Alerts -->
    @if(count($availableOutlets) === 0 && $selectedBusiness)
        <div style="padding: 20px; background: var(--bar-warning); color: var(--bar-bg); text-align: center; border-bottom: 2px solid var(--bar-border);">
            <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 8px;">⚠ No POS Outlets Found</div>
            <div style="margin-bottom: 12px;">Please create a POS outlet to start using Bar POS</div>
            <a href="{{ route('owner.hotel.pos-outlets') }}" class="btn-bar btn-bar-primary" style="display: inline-block; text-decoration: none;">
                Manage POS Outlets
            </a>
        </div>
    @elseif($selectedOutlet && !$currentSession && $sessionRequired)
        <div style="padding: 20px; background: rgba(245, 158, 11, 0.15); color: #f1f5f9; text-align: center; border-bottom: 2px solid var(--bar-warning);">
            <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 8px; color: var(--bar-warning);">⚠ No Active POS Session</div>
            <div style="margin-bottom: 12px; color: #cbd5e1;">Please open a session to process orders and track cash</div>
            <button wire:click="openSessionModal" class="btn-bar btn-bar-warning" style="display: inline-block;">
                Open Session
            </button>
        </div>
    @endif

    <!-- Main Content -->
    <div class="bar-main">
        <!-- Menu Section -->
        <div class="bar-menu">
            <!-- Search -->
            <div style="padding: 15px; background: var(--bar-card);">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search drinks..." class="search-box">
            </div>

            <!-- Categories -->
            <div class="category-tabs">
                <button wire:click="$set('selectedCategory', '')"
                        class="category-tab {{ $selectedCategory === '' ? 'active' : '' }}">
                    All
                </button>
                @foreach($availableCategories as $category)
                    <button wire:click="$set('selectedCategory', '{{ $category['id'] }}')"
                            class="category-tab {{ $selectedCategory === $category['id'] ? 'active' : '' }}">
                        {{ $category['name'] }}
                    </button>
                @endforeach
            </div>

            <!-- Menu Items Grid -->
            <div class="menu-items-grid">
                @forelse($availableMenuItems as $item)
                    <div wire:click="addToCart('{{ $item['id'] }}')"
                         class="menu-item-card {{ isset($item['happy_hour_prices']) && count($item['happy_hour_prices']) > 0 ? 'happy-hour' : '' }}">

                        @if(isset($item['happy_hour_prices']) && count($item['happy_hour_prices']) > 0)
                            <div class="happy-hour-tag">HAPPY HOUR</div>
                        @endif

                        <div class="item-name">{{ $item['name'] }}</div>
                        <div class="item-price">
                            @php
                                $displayPrice = $item['price'];
                                $hasDiscount = false;

                                if($happyHourActive && $barProfile) {
                                    $discountPct = $barProfile->happy_hour_discount_pct ?? 0;
                                    if($discountPct > 0) {
                                        $displayPrice = $item['price'] * (1 - ($discountPct / 100));
                                        $hasDiscount = true;
                                    }
                                }
                            @endphp

                            @if($hasDiscount)
                                <span class="original-price">TSh {{ number_format($item['price'], 0) }}</span>
                            @endif
                            TSh {{ number_format($displayPrice, 0) }}
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 40px; color: #94a3b8;">
                        @if(!$selectedOutlet)
                            <div style="font-size: 2.5rem; margin-bottom: 15px;">📍</div>
                            <div style="font-size: 1.2rem; font-weight: 600; margin-bottom: 8px; color: #cbd5e1;">Please Select an Outlet</div>
                            <p style="color: #64748b;">Choose a POS outlet from the dropdown above to view menu items</p>
                        @else
                            <div style="font-size: 2.5rem; margin-bottom: 15px;">🍹</div>
                            <div style="font-size: 1.2rem; font-weight: 600; margin-bottom: 8px; color: #cbd5e1;">No Menu Items</div>
                            <p style="color: #64748b; margin-bottom: 15px;">This outlet doesn't have any menu items configured yet</p>
                            @if(count($availableCategories) === 0)
                                <p style="color: #94a3b8; font-size: 0.9rem; margin-bottom: 15px;">Tip: Create menu categories first, then add items</p>
                            @endif
                            <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
                                <a href="{{ route('owner.hotel.pos-outlets') }}" target="_blank" class="btn-bar btn-bar-primary" style="text-decoration: none;">
                                    Manage Menu Items
                                </a>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Cart Section -->
        <div class="bar-cart">
            <div class="cart-header">
                <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 12px;">Current Order</div>

                <!-- Customer Selection -->
                <div style="margin-bottom: 12px;">
                    <div style="display: flex; gap: 8px; align-items: stretch;">
                        <select wire:model="customer_id" class="form-control-bar" style="flex: 1;">
                            <option value="">Select Customer (Optional)</option>
                            @foreach($availableCustomers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <button wire:click="openCustomerModal" class="btn-bar btn-bar-primary" style="padding: 8px 12px; white-space: nowrap;">
                            + Add
                        </button>
                    </div>
                </div>

                <!-- Order Mode Selector -->
                <div class="order-mode-selector">
                    <button wire:click="setOrderMode('immediate')"
                            class="mode-btn {{ $orderMode === 'immediate' ? 'active' : '' }}">
                        Pay Now
                    </button>
                    <button wire:click="setOrderMode('tab')"
                            class="mode-btn {{ $orderMode === 'tab' ? 'active' : '' }}">
                        Add to Tab
                    </button>
                    <button wire:click="openNewTabModal()" class="mode-btn">
                        New Tab
                    </button>
                </div>

                @if($selectedTab)
                    <div style="background: var(--bar-success); padding: 8px 12px; border-radius: 6px; text-align: center; margin-top: 10px;">
                        <strong>Tab: {{ $selectedTab['tab_no'] }} - {{ $selectedTab['tab_name'] }}</strong>
                    </div>
                @endif
            </div>

            <div class="cart-items">
                @forelse($cart as $key => $item)
                    <div class="cart-item">
                        <div class="cart-item-header">
                            <div class="cart-item-name">{{ $item['name'] }}</div>
                            <button wire:click="removeFromCart('{{ $key }}')"
                                    style="background: var(--bar-danger); border: none; color: white; padding: 4px 8px; border-radius: 4px; cursor: pointer;">
                                ✕
                            </button>
                        </div>
                        <div class="cart-item-price">
                            @if($item['happy_hour_applied'])
                                <span style="text-decoration: line-through; color: #64748b; font-size: 0.85rem; margin-right: 5px;">
                                    TSh {{ number_format($item['regular_price'], 0) }}
                                </span>
                            @endif
                            TSh {{ number_format($item['price'], 0) }}
                        </div>
                        <div class="cart-item-controls">
                            <button wire:click="decrementQuantity('{{ $key }}')" class="qty-btn">-</button>
                            <div class="qty-display">{{ $item['quantity'] }}</div>
                            <button wire:click="incrementQuantity('{{ $key }}')" class="qty-btn">+</button>
                            <div style="margin-left: auto; color: var(--bar-success); font-weight: 700;">
                                TSh {{ number_format($item['price'] * $item['quantity'], 0) }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        <p>Cart is empty</p>
                        <p style="font-size: 0.875rem;">Tap items to add</p>
                    </div>
                @endforelse
            </div>

            <div class="cart-summary">
                <div class="summary-row">
                    <span>Items</span>
                    <span>{{ $cartItemsCount }}</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span>TSh {{ number_format($cartTotal, 0) }}</span>
                </div>

                @if(count($cart) > 0)
                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 15px;">
                        @if($orderMode === 'immediate')
                            <!-- Quick Cash Payment -->
                            <button wire:click="payWithCash" class="btn-bar btn-bar-success" style="width: 100%; font-size: 1.1rem; padding: 15px;">
                                💵 Pay Cash - TSh {{ number_format($cartTotal, 0) }}
                            </button>
                            <!-- Other Payment Methods -->
                            <button wire:click="openPaymentModal" class="btn-bar btn-bar-primary" style="width: 100%;">
                                💳 Other Payment Methods
                            </button>
                        @else
                            <button wire:click="processOrder" class="btn-bar btn-bar-warning" style="width: 100%; font-size: 1.1rem; padding: 15px;">
                                ➕ Add to Tab
                            </button>
                        @endif
                        <!-- Clear Cart -->
                        <button wire:click="clearCart" class="btn-bar btn-bar-danger" style="width: 100%;">
                            🗑️ Clear Cart
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tab Selector Modal -->
    @if($showTabSelector)
        <div class="bar-modal" wire:click="closeTabSelector">
            <div class="bar-modal-content" wire:click.stop>
                <div class="bar-modal-header">
                    <div class="bar-modal-title">Select Open Tab</div>
                    <button wire:click="closeTabSelector" class="btn-bar btn-bar-danger">✕</button>
                </div>

                @forelse($availableTabs as $tab)
                    <div wire:click="selectTab('{{ $tab['id'] }}')" class="tab-list-item">
                        <div style="font-weight: 700; margin-bottom: 5px;">{{ $tab['tab_no'] }} - {{ $tab['tab_name'] }}</div>
                        <div style="color: #94a3b8; font-size: 0.875rem;">
                            Balance: TSh {{ number_format($tab['balance'], 0) }}
                            @if(isset($tab['customer']['name']))
                                | Customer: {{ $tab['customer']['name'] }}
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: #64748b;">
                        No open tabs found
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Add Customer Modal -->
    @if($showCustomerModal)
        <div class="bar-modal" wire:click="closeCustomerModal">
            <div class="bar-modal-content" wire:click.stop style="max-width: 500px;">
                <div class="bar-modal-header">
                    <div class="bar-modal-title">Add New Customer</div>
                    <button wire:click="closeCustomerModal" class="btn-bar btn-bar-danger">✕</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer Name *</label>
                    <input type="text" wire:model="newCustomerName" class="form-control-bar" placeholder="Enter full name" autofocus>
                    @error('newCustomerName')
                        <span style="color: var(--bar-danger); font-size: 0.875rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="tel" wire:model="newCustomerPhone" class="form-control-bar" placeholder="+1234567890">
                    @error('newCustomerPhone')
                        <span style="color: var(--bar-danger); font-size: 0.875rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email (Optional)</label>
                    <input type="email" wire:model="newCustomerEmail" class="form-control-bar" placeholder="customer@example.com">
                    @error('newCustomerEmail')
                        <span style="color: var(--bar-danger); font-size: 0.875rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button wire:click="closeCustomerModal" class="btn-bar btn-bar-danger" style="flex: 1;">
                        Cancel
                    </button>
                    <button wire:click="saveCustomer" class="btn-bar btn-bar-success" style="flex: 1;">
                        Save Customer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- New Tab Modal -->
    @if($showNewTabModal)
        <div class="bar-modal" wire:click="closeNewTabModal">
            <div class="bar-modal-content" wire:click.stop>
                <div class="bar-modal-header">
                    <div class="bar-modal-title">Create New Tab</div>
                    <button wire:click="closeNewTabModal" class="btn-bar btn-bar-danger">✕</button>
                </div>

                <div class="form-group">
                    <label class="form-label">Tab Name *</label>
                    <input type="text" wire:model="newTabName" class="form-control-bar" placeholder="e.g., John's Tab, Table 5">
                </div>

                <div class="form-group">
                    <label class="form-label">Customer (Optional)</label>
                    <select wire:model="newTabCustomerId" class="form-control-bar">
                        <option value="">Select Customer</option>
                        @foreach($availableCustomers as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button wire:click="closeNewTabModal" class="btn-bar btn-bar-danger" style="flex: 1;">
                        Cancel
                    </button>
                    <button wire:click="createNewTab" class="btn-bar btn-bar-success" style="flex: 1;">
                        Create Tab
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Payment Modal -->
    @if($showPaymentModal)
        <div class="bar-modal" wire:click="closePaymentModal">
            <div class="bar-modal-content" wire:click.stop>
                <div class="bar-modal-header">
                    <div class="bar-modal-title">💳 Payment Methods</div>
                    <button wire:click="closePaymentModal" class="btn-bar btn-bar-danger">✕</button>
                </div>

                <div style="background: var(--bar-bg); padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--bar-success);">
                    <div style="font-size: 0.85rem; color: #94a3b8; margin-bottom: 5px;">Total Amount</div>
                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--bar-success);">
                        TSh {{ number_format($cartTotal, 0) }}
                    </div>
                </div>

                <!-- Payment Methods Section -->
                <div style="margin-bottom: 20px;">
                    @if(count($paymentRows) > 1)
                        <div style="background: rgba(99, 102, 241, 0.1); padding: 8px 12px; border-radius: 6px; margin-bottom: 15px; border-left: 3px solid var(--bar-primary);">
                            <strong style="color: var(--bar-primary);">💰 Split Payment</strong>
                            <span style="color: #94a3b8; font-size: 0.875rem; margin-left: 8px;">
                                ({{ count($paymentRows) }} methods)
                            </span>
                        </div>
                    @endif

                    @foreach($paymentRows as $index => $row)
                        <div class="form-group" style="background: var(--bar-bg); padding: 12px; border-radius: 8px; margin-bottom: 10px;">
                            @if(count($paymentRows) > 1)
                                <div style="color: #cbd5e1; font-size: 0.875rem; margin-bottom: 8px; font-weight: 600;">
                                    Payment #{{ $index + 1 }}
                                </div>
                            @endif
                            <div style="display: flex; gap: 10px; align-items: end;">
                                <div style="flex: 1;">
                                    <label class="form-label">Method</label>
                                    <select wire:model="paymentRows.{{ $index }}.payment_method_id" class="form-control-bar">
                                        <option value="">Select Method</option>
                                        @foreach($availablePaymentMethods as $method)
                                            <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <label class="form-label">Amount (TSh)</label>
                                    <input type="number" step="1" wire:model="paymentRows.{{ $index }}.amount" class="form-control-bar" placeholder="0">
                                </div>
                                @if(count($paymentRows) > 1)
                                    <button wire:click="removePaymentRow({{ $index }})" class="btn-bar btn-bar-danger" style="padding: 10px 12px;">
                                        ✕
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <button wire:click="addPaymentRow" class="btn-bar btn-bar-primary" style="width: 100%; margin-bottom: 20px;">
                    ➕ Add Another Payment Method (Split Payment)
                </button>

                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea wire:model="orderNotes" class="form-control-bar" rows="2" placeholder="Add any notes about this order..."></textarea>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button wire:click="closePaymentModal" class="btn-bar btn-bar-danger" style="flex: 1;">
                        Cancel
                    </button>
                    <button wire:click="processOrder" class="btn-bar btn-bar-success" style="flex: 2; font-size: 1.05rem;">
                        ✓ Complete Payment
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Open Session Modal -->
    @if($showSessionModal)
        <div class="bar-modal" wire:click="closeSessionModal">
            <div class="bar-modal-content" wire:click.stop style="max-width: 500px;">
                <div class="bar-modal-header">
                    <div class="bar-modal-title">Open POS Session</div>
                    <button wire:click="closeSessionModal" class="btn-bar btn-bar-danger">✕</button>
                </div>

                <div style="background: rgba(99, 102, 241, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid var(--bar-primary);">
                    <div style="font-size: 0.95rem; color: #cbd5e1; line-height: 1.5;">
                        <strong style="color: #f1f5f9;">Opening a session allows you to:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                            <li>Track cash float and transactions</li>
                            <li>Process orders and payments</li>
                            <li>Reconcile at end of shift</li>
                        </ul>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Opening Float (TSh) *</label>
                    <input type="number" step="0.01" wire:model="openingFloat" class="form-control-bar" placeholder="Enter starting cash amount" autofocus>
                    <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 5px;">
                        The amount of cash in the register at the start of your shift
                    </div>
                    @error('openingFloat')
                        <span style="color: var(--bar-danger); font-size: 0.875rem; margin-top: 4px; display: block;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button wire:click="closeSessionModal" class="btn-bar btn-bar-danger" style="flex: 1;">
                        Cancel
                    </button>
                    <button wire:click="startSession" class="btn-bar btn-bar-success" style="flex: 2;">
                        Open Session
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div id="flash-message" style="position: fixed; top: 80px; right: 20px; background: var(--bar-success); color: white; padding: 15px 50px 15px 20px; border-radius: 8px; z-index: 2000; animation: slideIn 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 400px;">
            <button onclick="this.parentElement.remove()" style="position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.2); border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">✕</button>
            <div style="font-weight: 600; margin-bottom: 4px;">✓ Success</div>
            <div>{{ session('message') }}</div>
        </div>
    @endif

    @if (session()->has('error'))
        <div id="flash-error" style="position: fixed; top: 80px; right: 20px; background: var(--bar-danger); color: white; padding: 15px 50px 15px 20px; border-radius: 8px; z-index: 2000; animation: slideIn 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 400px;">
            <button onclick="this.parentElement.remove()" style="position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.2); border: none; color: white; width: 24px; height: 24px; border-radius: 4px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">✕</button>
            <div style="font-weight: 600; margin-bottom: 4px;">✗ Error</div>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div id="flash-warning" style="position: fixed; top: 80px; right: 20px; background: var(--bar-warning); color: var(--bar-bg); padding: 15px 50px 15px 20px; border-radius: 8px; z-index: 2000; animation: slideIn 0.3s; box-shadow: 0 4px 12px rgba(0,0,0,0.3); max-width: 400px;">
            <button onclick="this.parentElement.remove()" style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.2); border: none; color: var(--bar-bg); width: 24px; height: 24px; border-radius: 4px; cursor: pointer; font-size: 16px; display: flex; align-items: center; justify-content: center;">✕</button>
            <div style="font-weight: 600; margin-bottom: 4px;">⚠ Warning</div>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    <style>
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Auto-hide flash messages after 5 seconds */
        #flash-message, #flash-error, #flash-warning {
            animation: slideIn 0.3s, fadeOut 0.5s 4.5s forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(400px);
            }
        }
    </style>
</div>
