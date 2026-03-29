<div class="restaurant-pos-container">
    <style>
        :root {
            --pos-primary: #0d6efd;
            --pos-success: #198754;
            --pos-warning: #ffc107;
            --pos-danger: #dc3545;
            --pos-info: #0dcaf0;
            --pos-bg: #f0f4f8;
        }

        .restaurant-pos-container {
            min-height: 100vh;
            background: var(--pos-bg);
        }

        /* Header */
        .pos-header {
            background: #fff;
            padding: 15px 20px;
            border-bottom: 2px solid #e0e0e0;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Floor Plan Grid */
        .floor-plan {
            padding: 20px;
        }

        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .table-card {
            background: #fff;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .table-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .table-card.available {
            border-color: var(--pos-success);
            background: #f0fff4;
        }

        .table-card.occupied {
            border-color: var(--pos-danger);
            background: #fff5f5;
        }

        .table-card.reserved {
            border-color: var(--pos-warning);
            background: #fffbf0;
        }

        .table-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .table-status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
        }

        .table-info {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }

        /* Section Header */
        .section-header {
            background: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #333;
        }

        /* Modals */
        .modal-xl {
            max-width: 95%;
        }

        /* Order Modal */
        .order-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            height: 70vh;
        }

        @media (max-width: 991px) {
            .order-content {
                grid-template-columns: 1fr;
                height: auto;
            }
        }

        .menu-section {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        .menu-search {
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

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            overflow-y: auto;
            flex: 1;
            padding: 5px;
        }

        .menu-item-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }

        .menu-item-card:hover {
            border-color: var(--pos-primary);
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
        }

        .menu-item-image {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .menu-item-placeholder {
            width: 100%;
            height: 80px;
            background: #f0f4f8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
        }

        .order-panel {
            display: flex;
            flex-direction: column;
            border-left: 1px solid #e0e0e0;
            padding-left: 20px;
        }

        .order-items-list {
            flex: 1;
            overflow-y: auto;
            margin: 15px 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 8px;
        }

        .order-item.sent-to-kitchen {
            background: #e7f5ff;
            border-left: 3px solid var(--pos-info);
        }

        .order-summary {
            border-top: 2px solid #e0e0e0;
            padding-top: 15px;
            margin-top: auto;
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

        /* Split Bill Modal */
        .split-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }

        .split-column {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            background: #f8f9fa;
        }

        .split-column h6 {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }

        .unassigned-items {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
        }

        .draggable-item {
            padding: 10px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            margin-bottom: 8px;
            cursor: move;
        }

        .draggable-item:hover {
            border-color: var(--pos-primary);
        }

        /* Loading */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>

    {{-- Header --}}
    <div class="pos-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-2 mb-md-0">
                <h4 class="mb-0">
                    <i class="ti ti-tool-kitchen me-2"></i>
                    Restaurant POS
                </h4>
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
    {{-- Quick Actions Bar --}}
    <div class="bg-white border-bottom px-3 py-2">
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <button wire:click="openOrderWithoutTable('takeaway')" class="btn btn-sm btn-success">
                <i class="ti ti-package me-1"></i> Takeaway Order
            </button>
            <button wire:click="openOrderWithoutTable('delivery')" class="btn btn-sm btn-info">
                <i class="ti ti-truck-delivery me-1"></i> Delivery Order
            </button>
            @if(!empty($tables))
            <button wire:click="$toggle('showTableSection')" class="btn btn-sm btn-outline-secondary ms-auto">
                <i class="ti ti-{{ $showTableSection ? 'eye-off' : 'eye' }} me-1"></i>
                {{ $showTableSection ? 'Hide' : 'Show' }} Tables
            </button>
            @endif
        </div>
    </div>

    {{-- Floor Plan --}}
    @if($showTableSection && !empty($tables))
    <div class="floor-plan">
        @php
            $sections = collect($tables)->groupBy('section');
        @endphp

        @forelse($sections as $section => $sectionTables)
            <div class="section-header">
                <i class="ti ti-layout-grid me-2"></i>
                {{ $section ?: 'Main Area' }}
            </div>

            <div class="tables-grid">
                @foreach($sectionTables as $table)
                <div wire:click="selectTable('{{ $table['id'] }}')"
                     class="table-card {{ $table['status'] }}">
                    <span class="table-status-badge badge
                        @if($table['status'] === 'available') bg-success
                        @elseif($table['status'] === 'occupied') bg-danger
                        @elseif($table['status'] === 'reserved') bg-warning
                        @else bg-secondary @endif">
                        {{ ucfirst($table['status']) }}
                    </span>

                    <i class="ti ti-armchair fs-1 mb-2"></i>
                    <div class="table-number">Table {{ $table['table_number'] }}</div>
                    <div class="table-info">
                        <i class="ti ti-users"></i> {{ $table['capacity'] }} seats
                        @if($table['has_order'])
                            <div class="mt-2">
                                <span class="badge bg-info">Order Active</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @empty
            <div class="text-center py-5">
                <i class="ti ti-armchair fs-1 text-muted"></i>
                <p class="text-muted mt-3">No tables available. Please add tables in settings.</p>
            </div>
        @endforelse
    </div>
    @elseif(!$showTableSection)
        <div class="container py-5">
            <div class="alert alert-info text-center">
                <i class="ti ti-armchair fs-1 d-block mb-3"></i>
                <p class="mb-0">Table view is hidden. Use quick action buttons above to create orders, or click "Show Tables" to view table floor plan.</p>
            </div>
        </div>
    @endif
    @endif

    {{-- Order Modal --}}
    @if($showOrderModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @if($selectedTable)
                            <i class="ti ti-armchair me-2"></i>
                            Table {{ $selectedTable['table_number'] }}
                        @else
                            <i class="ti ti-{{ $orderType === 'delivery' ? 'truck-delivery' : 'package' }} me-2"></i>
                            {{ ucfirst($orderType) }} Order
                        @endif
                        @if($currentOrder)
                            - Order #{{ substr($currentOrder['order_no'] ?? '', -5) }}
                        @endif
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeOrderModal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="order-content p-3">
                        {{-- Menu Section --}}
                        <div class="menu-section">
                            <div class="menu-search">
                                <input type="text"
                                       wire:model.live.debounce.300ms="menuSearch"
                                       class="form-control"
                                       placeholder="Search menu...">
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
                                        <span class="badge bg-success-subtle text-success" style="font-size: 9px;">
                                            <i class="ti ti-leaf"></i> Veg
                                        </span>
                                    @endif
                                    @if($item['is_vegan'])
                                        <span class="badge bg-info-subtle text-info" style="font-size: 9px;">
                                            <i class="ti ti-plant"></i> Vegan
                                        </span>
                                    @endif
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted" style="grid-column: 1 / -1;">
                                    No menu items found
                                </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Order Panel --}}
                        <div class="order-panel">
                            <div>
                                <h6 class="mb-3">Order Details</h6>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label small">Type</label>
                                        <select wire:model="orderType" class="form-select form-select-sm" {{ $selectedTable ? 'disabled' : '' }}>
                                            <option value="dine_in">Dine In</option>
                                            <option value="takeaway">Takeaway</option>
                                            <option value="delivery">Delivery</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">Covers</label>
                                        <input type="number" wire:model="covers" class="form-control form-control-sm" min="1">
                                    </div>
                                </div>
                                <div class="row g-2 mb-3">
                                    @if($selectedTable)
                                    <div class="col-12">
                                        <label class="form-label small">Waiter</label>
                                        <select wire:model="selectedStaff" class="form-select form-select-sm">
                                            <option value="">Select...</option>
                                            @foreach($availableStaffs as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                    <div class="col-12">
                                        <label class="form-label small">Customer</label>
                                        <select wire:model="selectedCustomer" class="form-select form-select-sm">
                                            <option value="">Select...</option>
                                            @foreach($availableCustomers as $id => $name)
                                                <option value="{{ $id }}">{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="order-items-list">
                                @forelse($orderItems as $index => $item)
                                <div class="order-item {{ $item['sent_to_kitchen'] ? 'sent-to-kitchen' : '' }}">
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
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-shopping-cart-off fs-1 d-block mb-2"></i>
                                    No items in order
                                </div>
                                @endforelse
                            </div>

                            <div class="order-summary">
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

                                <div class="d-flex gap-2 mt-3">
                                    @if(count($unsentItems) > 0)
                                    <button wire:click="sendToKitchen" class="btn btn-primary flex-fill">
                                        <i class="ti ti-chef-hat me-1"></i> Send to Kitchen
                                    </button>
                                    @endif

                                    @if($currentOrder && count($orderItems) > 0)
                                    <button wire:click="openSplitBillModal" class="btn btn-info">
                                        <i class="ti ti-cut"></i>
                                    </button>
                                    <button wire:click="openPaymentModal" class="btn btn-success">
                                        <i class="ti ti-cash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
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

                    <div class="unassigned-items">
                        <h6 class="mb-3">
                            <i class="ti ti-list me-2"></i> All Items (Click to assign to diner)
                        </h6>
                        <div class="row g-2">
                            @foreach($orderItems as $index => $item)
                                @if($item['status'] !== 'voided')
                                <div class="col-md-6">
                                    <div class="draggable-item">
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
                                                    class="btn btn-sm btn-outline-primary me-1">
                                                Diner {{ $split['diner'] }}
                                            </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <div class="split-container mt-4">
                        @foreach($splitItems as $splitIndex => $split)
                        <div class="split-column">
                            <h6>
                                <i class="ti ti-user me-2"></i>
                                Diner {{ $split['diner'] }}
                            </h6>

                            @forelse($split['items'] as $item)
                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
                                <div class="small">
                                    {{ $item['name'] }}
                                    <span class="text-muted">({{ $item['quantity'] }}x)</span>
                                </div>
                                <div class="fw-bold small">TZS {{ number_format($item['subtotal'], 0) }}</div>
                            </div>
                            @empty
                            <div class="text-center text-muted small py-3">
                                No items assigned
                            </div>
                            @endforelse

                            <div class="border-top pt-2 mt-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total:</span>
                                    <span class="fw-bold text-primary">TZS {{ number_format($split['total'], 0) }}</span>
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-cash me-2"></i> Process Payment
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <span>Order Total:</span>
                            <span class="fw-bold">TZS {{ number_format($this->total, 0) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number"
                               wire:model="paymentAmount"
                               class="form-control form-control-lg"
                               step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <select wire:model="paymentMethodId" class="form-select">
                            @foreach($availablePaymentMethods as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if($paymentAmount > $this->total)
                    <div class="alert alert-success">
                        <div class="d-flex justify-content-between">
                            <span>Change:</span>
                            <span class="fw-bold">TZS {{ number_format($paymentAmount - $this->total, 0) }}</span>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button wire:click="closePaymentModal" class="btn btn-secondary">Cancel</button>
                    <button wire:click="processPayment" class="btn btn-success btn-lg">
                        <i class="ti ti-check me-1"></i> Complete Payment
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
