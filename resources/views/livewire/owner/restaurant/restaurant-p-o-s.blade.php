<div class="restaurant-pos-container">
    <style>
        :root {
            --pos-primary: #0d6efd;
            --pos-success: #198754;
            --pos-warning: #ffc107;
            --pos-danger: #dc3545;
            --pos-info: #0dcaf0;
            --pos-bg: #f8f9fa;
            --pos-card: #ffffff;
        }

        .restaurant-pos-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--pos-bg);
        }

        /* Header */
        .pos-header {
            background: var(--pos-card);
            padding: 12px 20px;
            border-bottom: 2px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Main Layout */
        .pos-main {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .pos-menu {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--pos-bg);
            overflow: hidden;
            padding: 20px;
        }

        .pos-cart {
            width: 400px;
            background: var(--pos-card);
            border-left: 2px solid #e0e0e0;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        @media (max-width: 1200px) {
            .pos-cart {
                width: 350px;
            }
        }

        @media (max-width: 992px) {
            .pos-main {
                flex-direction: column;
            }
            .pos-cart {
                width: 100%;
                border-left: none;
                border-top: 2px solid #e0e0e0;
                max-height: 50vh;
            }
        }

        /* Menu Search & Categories */
        .menu-search-bar {
            margin-bottom: 15px;
        }

        .menu-categories {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .category-btn {
            padding: 8px 16px;
            border: 1px solid #ddd;
            background: #fff;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .category-btn:hover, .category-btn.active {
            background: var(--pos-primary);
            color: #fff;
            border-color: var(--pos-primary);
        }

        /* Menu Grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 15px;
            overflow-y: auto;
            flex: 1;
            padding: 5px;
        }

        .menu-item-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .menu-item-card:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
            transform: translateY(-2px);
        }

        .menu-item-image {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .menu-item-placeholder {
            width: 100%;
            height: 100px;
            background: #f0f4f8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        /* Cart */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            margin: 15px 0;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .cart-item.sent-to-kitchen {
            background: #e7f5ff;
            border-left: 3px solid var(--pos-info);
        }

        .qty-controls {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-input {
            width: 40px;
            text-align: center;
            padding: 4px;
        }

        .cart-summary {
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            margin-top: auto;
        }
    </style>

    {{-- Header --}}
    <div class="pos-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">
                    <i class="ti ti-tool-kitchen me-2"></i>
                    Restaurant POS
                </h5>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- Business Selection --}}
                @if(!empty($ownerBusinesses))
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted mb-0" style="white-space: nowrap;">
                        <i class="ti ti-building-store"></i> Business:
                    </label>
                    @if(count($ownerBusinesses) > 1)
                        <select wire:model.live="selectedBusiness" class="form-select form-select-sm" style="min-width: 180px;">
                            @foreach($ownerBusinesses as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-primary">{{ reset($ownerBusinesses) }}</span>
                    @endif
                </div>
                @endif

                {{-- Outlet Selection --}}
                @if(!empty($availableOutlets))
                <div class="d-flex align-items-center gap-2">
                    <label class="small text-muted mb-0" style="white-space: nowrap;">
                        <i class="ti ti-layout-grid"></i> Outlet:
                    </label>
                    @if(count($availableOutlets) > 1)
                        <select wire:model.live="selectedOutlet" class="form-select form-select-sm" style="min-width: 160px;">
                            @foreach($availableOutlets as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="badge bg-info">{{ reset($availableOutlets) }}</span>
                    @endif
                </div>
                @endif

                <a href="{{ route('owner.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- No Business/Outlet Warning --}}
    @if(empty($ownerBusinesses))
        <div class="container py-5">
            <div class="alert alert-warning text-center">
                <i class="ti ti-alert-triangle fs-1 d-block mb-3"></i>
                <h5>No Businesses Assigned</h5>
                <p class="mb-0">You don't have access to any restaurant businesses. Please contact your administrator.</p>
            </div>
        </div>
    @elseif(empty($availableOutlets))
        <div class="container py-5">
            <div class="alert alert-warning text-center">
                <i class="ti ti-layout-grid fs-1 d-block mb-3"></i>
                <h5>No Outlets Available</h5>
                <p class="mb-0">No outlets found for the selected business. Please add outlets in settings or contact your administrator.</p>
            </div>
        </div>
    @else
    {{-- Main POS Screen --}}
    <div class="pos-main">
        {{-- Menu Section --}}
        <div class="pos-menu">
            <div class="menu-search-bar">
                <input type="text"
                       wire:model.live.debounce.300ms="menuSearch"
                       class="form-control"
                       placeholder="Search menu items...">
            </div>

            <div class="menu-categories">
                <button wire:click="$set('selectedCategory', '')"
                        class="category-btn {{ !$selectedCategory ? 'active' : '' }}">
                    All Items
                </button>
                @foreach($menuCategories as $catId => $catName)
                <button wire:click="$set('selectedCategory', '{{ $catId }}')"
                        class="category-btn {{ $selectedCategory === $catId ? 'active' : '' }}">
                    {{ $catName }}
                </button>
                @endforeach
            </div>

            <div class="menu-grid">
                @forelse($menuItems as $item)
                <div wire:click="addMenuItem('{{ $item['id'] }}')" class="menu-item-card">
                    @if($item['image'])
                        <img src="{{ asset('storage/' . $item['image']) }}"
                             alt="{{ $item['name'] }}"
                             class="menu-item-image">
                    @else
                        <div class="menu-item-placeholder">
                            <i class="ti ti-tool-kitchen fs-1 text-muted"></i>
                        </div>
                    @endif

                    <div class="fw-medium small text-truncate">{{ $item['name'] }}</div>
                    <div class="text-primary small fw-bold">TZS {{ number_format($item['price'], 0) }}</div>

                    @if($item['is_vegetarian'])
                        <span class="badge bg-success-subtle text-success mt-1" style="font-size: 9px;">
                            <i class="ti ti-leaf"></i> Veg
                        </span>
                    @endif
                    @if($item['is_vegan'])
                        <span class="badge bg-info-subtle text-info mt-1" style="font-size: 9px;">
                            <i class="ti ti-plant"></i> Vegan
                        </span>
                    @endif
                </div>
                @empty
                <div class="text-center py-5 text-muted" style="grid-column: 1 / -1;">
                    <i class="ti ti-chef-hat fs-1 d-block mb-2"></i>
                    No menu items found
                </div>
                @endforelse
            </div>
        </div>

        {{-- Cart Section --}}
        <div class="pos-cart">
            <h6 class="mb-3">Order Details</h6>

            {{-- Order Type & Table Selection --}}
            <div class="mb-3">
                <label class="form-label small">Order Type</label>
                <select wire:model.live="orderType" class="form-select form-select-sm">
                    <option value="dine_in">Dine In</option>
                    <option value="takeaway">Takeaway</option>
                    <option value="delivery">Delivery</option>
                </select>
            </div>

            @if($orderType === 'dine_in' && !empty($tables))
            <div class="mb-3">
                <label class="form-label small">Table</label>
                <select wire:model.live="selectedTableId" class="form-select form-select-sm">
                    <option value="">Select Table...</option>
                    @foreach($tables as $table)
                        <option value="{{ $table['id'] }}" {{ $table['status'] !== 'available' && !$selectedTableId ? 'disabled' : '' }}>
                            Table {{ $table['table_number'] }}
                            @if($table['status'] === 'occupied') (Occupied) @endif
                            @if($table['status'] === 'reserved') (Reserved) @endif
                            - {{ $table['capacity'] }} seats
                        </option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small">Covers</label>
                    <input type="number" wire:model="covers" class="form-control form-control-sm" min="1">
                </div>
                <div class="col-6">
                    <label class="form-label small">{{ $orderType === 'dine_in' ? 'Waiter' : 'Customer' }}</label>
                    <select wire:model="selectedStaff" class="form-select form-select-sm">
                        <option value="">Select...</option>
                        @if($orderType === 'dine_in')
                            @foreach($availableStaffs as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        @else
                            @foreach($availableCustomers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            {{-- Cart Items --}}
            <div class="cart-items">
                @forelse($orderItems as $index => $item)
                <div class="cart-item {{ $item['sent_to_kitchen'] ? 'sent-to-kitchen' : '' }}">
                    <div class="flex-grow-1">
                        <div class="fw-medium small">{{ $item['name'] }}</div>
                        <div class="text-muted small">TZS {{ number_format($item['unit_price'], 0) }}</div>
                        @if($item['sent_to_kitchen'])
                            <span class="badge bg-info-subtle text-info" style="font-size: 9px;">
                                <i class="ti ti-chef-hat"></i> In Kitchen
                            </span>
                        @endif
                    </div>

                    @if(!$item['sent_to_kitchen'])
                    <div class="qty-controls">
                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                class="btn btn-sm btn-outline-secondary qty-btn">
                            <i class="ti ti-minus"></i>
                        </button>
                        <input type="number"
                               value="{{ $item['quantity'] }}"
                               wire:change="updateQuantity({{ $index }}, $event.target.value)"
                               class="form-control form-control-sm qty-input">
                        <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                class="btn btn-sm btn-outline-secondary qty-btn">
                            <i class="ti ti-plus"></i>
                        </button>
                    </div>
                    @else
                    <div class="text-end">
                        <div class="fw-medium">{{ $item['quantity'] }}x</div>
                    </div>
                    @endif

                    <div class="text-end ms-3">
                        <div class="fw-bold">TZS {{ number_format($item['subtotal'], 0) }}</div>
                    </div>

                    @if($item['sent_to_kitchen'])
                        <button wire:click="openVoidModal('{{ $item['id'] }}')"
                                class="btn btn-sm btn-outline-danger ms-2"
                                title="Void Item">
                            <i class="ti ti-ban"></i>
                        </button>
                    @else
                        <button wire:click="removeItem({{ $index }})"
                                class="btn btn-sm text-danger ms-2">
                            <i class="ti ti-x"></i>
                        </button>
                    @endif
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>
                    <small>No items in order</small>
                </div>
                @endforelse
            </div>

            {{-- Cart Summary --}}
            <div class="cart-summary">
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span class="fw-bold">TZS {{ number_format($this->total, 0) }}</span>
                </div>
                @if(count($unsentItems) > 0)
                <div class="d-flex justify-content-between mb-2 text-warning">
                    <span>Unsent Items:</span>
                    <span class="fw-bold">TZS {{ number_format($this->unsentTotal, 0) }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between pt-2 border-top">
                    <span class="fw-bold">Total:</span>
                    <span class="fw-bold text-primary fs-5">TZS {{ number_format($this->total, 0) }}</span>
                </div>

                <div class="d-grid gap-2 mt-3">
                    @if(count($unsentItems) > 0)
                    <button wire:click="sendToKitchen" class="btn btn-primary">
                        <i class="ti ti-chef-hat me-1"></i> Send to Kitchen
                    </button>
                    @endif

                    @if($currentOrder && count($orderItems) > 0)
                    <div class="btn-group" role="group">
                        <button wire:click="openSplitBillModal" class="btn btn-info">
                            <i class="ti ti-cut me-1"></i> Split
                        </button>
                        <button wire:click="openPaymentModal" class="btn btn-success">
                            <i class="ti ti-cash me-1"></i> Pay
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Void Item Modal --}}
    @if($showVoidModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-ban me-2"></i> Void Item
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeVoidModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>
                        This action will void the item and notify the kitchen to cancel preparation.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason for voiding <span class="text-danger">*</span></label>
                        <textarea wire:model="voidReason"
                                  class="form-control"
                                  rows="3"
                                  placeholder="Enter reason (e.g., customer changed mind, wrong order, etc.)"></textarea>
                        @error('voidReason') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeVoidModal" class="btn btn-secondary">Cancel</button>
                    <button wire:click="voidItem" class="btn btn-danger">
                        <i class="ti ti-ban me-1"></i> Void Item
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Split Bill Modal --}}
    @if($showSplitBillModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-cut me-2"></i> Split Bill
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeSplitBillModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Number of Diners</label>
                        <select wire:model.live="splitCount" class="form-select" style="width: 150px;">
                            @for($i = 2; $i <= 6; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="row g-3">
                        @foreach($orderItems as $index => $item)
                            @if($item['status'] !== 'voided')
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-medium">{{ $item['name'] }}</div>
                                                <div class="text-muted small">{{ $item['quantity'] }}x @ TZS {{ number_format($item['unit_price'], 0) }}</div>
                                            </div>
                                            <div class="fw-bold">TZS {{ number_format($item['subtotal'], 0) }}</div>
                                        </div>
                                        <div class="mt-2">
                                            @foreach($splitItems as $splitIndex => $split)
                                            <button wire:click="assignItemToSplit({{ $index }}, {{ $splitIndex }})"
                                                    class="btn btn-sm btn-outline-primary me-1 mb-1">
                                                Diner {{ $split['diner'] }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="row g-3 mt-3">
                        @foreach($splitItems as $splitIndex => $split)
                        <div class="col-md-{{ 12 / count($splitItems) }}">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <i class="ti ti-user me-2"></i> Diner {{ $split['diner'] }}
                                </div>
                                <div class="card-body">
                                    @forelse($split['items'] as $item)
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span>{{ $item['name'] }} ({{ $item['quantity'] }}x)</span>
                                        <span>TZS {{ number_format($item['subtotal'], 0) }}</span>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted small py-3">
                                        No items assigned
                                    </div>
                                    @endforelse

                                    <div class="border-top pt-2 mt-2">
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total:</span>
                                            <span class="text-primary">TZS {{ number_format($split['total'], 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeSplitBillModal" class="btn btn-secondary">Close</button>
                    <button class="btn btn-primary">
                        <i class="ti ti-printer me-1"></i> Print Split Bills
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Payment Modal --}}
    @if($showPaymentModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); z-index: 1060;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-cash me-2"></i> Process Payment
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Order Total:</span>
                            <span class="fw-bold fs-5">TZS {{ number_format($this->total, 0) }}</span>
                        </div>
                        @if($remainingAmount > 0)
                        <div class="d-flex justify-content-between text-warning">
                            <span>Remaining:</span>
                            <span class="fw-bold">TZS {{ number_format($remainingAmount, 0) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Quick Payment Buttons --}}
                    <div class="d-flex gap-2 mb-3">
                        <button wire:click="quickPayCash" class="btn btn-success">
                            <i class="ti ti-cash me-1"></i> Cash
                        </button>
                        <button wire:click="addPaymentRow" class="btn btn-outline-primary">
                            <i class="ti ti-plus me-1"></i> Split Payment
                        </button>
                    </div>

                    {{-- Payment Rows --}}
                    @foreach($paymentRows as $index => $row)
                    <div class="card mb-2">
                        <div class="card-body py-2">
                            <div class="row g-2 align-items-center">
                                <div class="col-md-5">
                                    <label class="form-label small mb-1">Amount</label>
                                    <input type="number"
                                           wire:model.live="paymentRows.{{ $index }}.amount"
                                           class="form-control form-control-sm"
                                           step="0.01"
                                           min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small mb-1">Payment Method</label>
                                    <select wire:model="paymentRows.{{ $index }}.method_id" class="form-select form-select-sm">
                                        <option value="">Select...</option>
                                        @foreach($availablePaymentMethods as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    @if(count($paymentRows) > 1)
                                    <button wire:click="removePaymentRow({{ $index }})"
                                            class="btn btn-sm btn-outline-danger mt-3"
                                            title="Remove">
                                        <i class="ti ti-x"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @php
                        $totalPayment = collect($paymentRows)->sum('amount');
                        $change = max(0, $totalPayment - $this->total);
                    @endphp

                    @if($change > 0)
                    <div class="alert alert-success">
                        <div class="d-flex justify-content-between">
                            <span>Change:</span>
                            <span class="fw-bold">TZS {{ number_format($change, 0) }}</span>
                        </div>
                    </div>
                    @endif

                    @if($totalPayment < $this->total)
                    <div class="alert alert-warning">
                        <small><i class="ti ti-alert-triangle me-1"></i> Payment amount is less than order total</small>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="closePaymentModal" class="btn btn-secondary">Cancel</button>
                    <button wire:click="processPayment" class="btn btn-success btn-lg" {{ $totalPayment < $this->total ? 'disabled' : '' }}>
                        <i class="ti ti-check me-1"></i> Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
