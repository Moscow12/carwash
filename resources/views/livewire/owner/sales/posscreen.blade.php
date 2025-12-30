<div class="pos-container">
    <style>
        :root {
            --pos-primary: #0d6efd;
            --pos-success: #198754;
            --pos-warning: #ffc107;
            --pos-danger: #dc3545;
            --pos-info: #0dcaf0;
            --pos-bg: #f0f4f8;
            --pos-header: #e8f4fc;
        }

        .pos-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--pos-bg);
        }

        /* Header */
        .pos-header {
            background: var(--pos-header);
            padding: 10px 15px;
            border-bottom: 1px solid #d0e3f0;
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

        /* Desktop: Side by side */
        @media (min-width: 992px) {
            .pos-cart { width: 45%; }
            .pos-products { width: 55%; }
            .mobile-toggle { display: none !important; }
            .pos-cart, .pos-products { display: flex !important; }
        }

        /* Mobile/Tablet: Full width with toggle */
        @media (max-width: 991px) {
            .pos-cart, .pos-products {
                width: 100%;
                display: none;
            }
            .pos-cart.active, .pos-products.active {
                display: flex !important;
            }
            .mobile-toggle {
                display: flex !important;
            }
        }

        .pos-cart {
            background: #fff;
            flex-direction: column;
            border-right: 1px solid #e0e0e0;
        }

        .pos-products {
            background: #f8fafc;
            flex-direction: column;
            overflow: hidden;
        }

        /* Mobile Toggle Tabs */
        .mobile-toggle {
            display: none;
            background: #fff;
            border-bottom: 2px solid #e0e0e0;
        }

        .mobile-toggle .toggle-btn {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            font-weight: 600;
            color: #6c757d;
            position: relative;
        }

        .mobile-toggle .toggle-btn.active {
            color: var(--pos-primary);
        }

        .mobile-toggle .toggle-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--pos-primary);
        }

        .mobile-toggle .cart-badge {
            position: absolute;
            top: 8px;
            right: 25%;
            background: var(--pos-danger);
            color: #fff;
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 10px;
            min-width: 18px;
        }

        /* Product Grid - Responsive */
        .product-grid {
            display: grid;
            gap: 10px;
            padding: 10px;
            overflow-y: auto;
            flex: 1;
        }

        @media (min-width: 992px) {
            .product-grid { grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .product-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @media (min-width: 576px) and (max-width: 767px) {
            .product-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 575px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; padding: 8px; }
        }

        .product-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 10px 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            -webkit-tap-highlight-color: transparent;
        }

        .product-card:hover, .product-card:active {
            border-color: var(--pos-primary);
            box-shadow: 0 2px 12px rgba(13, 110, 253, 0.2);
            transform: scale(1.02);
        }

        .product-icon {
            width: 45px;
            height: 45px;
            margin: 0 auto 6px;
            background: #f0f4f8;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 575px) {
            .product-icon { width: 40px; height: 40px; }
            .product-card { padding: 8px 6px; }
            .product-card .small { font-size: 11px; }
        }

        /* Cart Table */
        .cart-table {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .cart-table table {
            font-size: 14px;
        }

        @media (max-width: 575px) {
            .cart-table table { font-size: 13px; }
            .cart-table .btn-sm { padding: 4px 6px; }
            .cart-table input.form-control { width: 40px !important; padding: 4px; }
        }

        /* Cart Summary */
        .cart-summary {
            background: #f8fafc;
            padding: 12px 15px;
            border-top: 1px solid #e0e0e0;
        }

        /* Footer */
        .pos-footer {
            background: var(--pos-header);
            padding: 10px 15px;
            border-top: 1px solid #d0e3f0;
            position: sticky;
            bottom: 0;
            z-index: 100;
        }

        @media (max-width: 767px) {
            .pos-footer {
                padding: 8px 10px;
            }
            .pos-footer .btn-action {
                padding: 8px 12px;
                font-size: 13px;
            }
            .pos-footer .total-display {
                padding: 8px 12px;
                font-size: 14px;
            }
            .pos-footer .total-display .fs-4 {
                font-size: 16px !important;
            }
        }

        @media (max-width: 575px) {
            .pos-footer .d-flex {
                flex-wrap: wrap;
                gap: 8px;
            }
            .pos-footer .btn-action {
                flex: 1;
                min-width: auto;
                padding: 10px 8px;
            }
            .pos-footer .btn-action .btn-text {
                display: none;
            }
            .pos-footer .total-display {
                width: 100%;
                text-align: center;
                order: -1;
                margin-bottom: 8px;
            }
        }

        .btn-action {
            min-width: 90px;
            font-weight: 500;
        }

        .total-display {
            background: #1a3a5c;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            white-space: nowrap;
        }

        /* Modals - Mobile Friendly */
        @media (max-width: 575px) {
            .modal-dialog {
                margin: 10px;
                max-width: calc(100% - 20px);
            }
            .modal-dialog.modal-lg {
                max-width: calc(100% - 20px);
            }
            .modal-body {
                padding: 15px;
            }
            .modal .form-control,
            .modal .form-select {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }

        /* Touch-friendly inputs */
        .form-control, .form-select {
            min-height: 44px;
        }

        @media (max-width: 575px) {
            .form-control-sm, .form-select-sm,
            .input-group-sm > .form-control,
            .input-group-sm > .form-select {
                min-height: 40px;
            }
        }

        /* Quantity buttons - touch friendly */
        .qty-btn {
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 575px) {
            .qty-btn { width: 32px; height: 32px; }
        }

        /* Hide scrollbar but keep functionality */
        .cart-table::-webkit-scrollbar,
        .product-grid::-webkit-scrollbar {
            width: 4px;
        }

        .cart-table::-webkit-scrollbar-thumb,
        .product-grid::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 2px;
        }
    </style>

    {{-- Header --}}
    <div class="pos-header">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold text-primary">
                    <i class="ti ti-map-pin me-1"></i>
                    @php $carwash = collect($ownerCarwashes)->firstWhere('id', $selectedCarwash); @endphp
                    <span class="d-none d-sm-inline">{{ $carwash['name'] ?? 'Select Location' }}</span>
                    <span class="d-sm-none">{{ Str::limit($carwash['name'] ?? 'Select', 10) }}</span>
                </span>
                <span class="text-muted small d-none d-md-inline">
                    <i class="ti ti-calendar me-1"></i>
                    {{ now()->format('M d, Y H:i') }}
                </span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                @if(count($ownerCarwashes) > 1)
                <select wire:model.live="selectedCarwash" class="form-select form-select-sm" style="width: auto; max-width: 150px;">
                    @foreach($ownerCarwashes as $cw)
                        <option value="{{ $cw['id'] }}">{{ $cw['name'] }}</option>
                    @endforeach
                </select>
                @endif
                <a href="{{ route('owner.dashboard') }}" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex">
                    <i class="ti ti-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show m-2 py-2" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show m-2 py-2" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Mobile Toggle Tabs --}}
    <div class="mobile-toggle">
        <button type="button" class="toggle-btn active" onclick="toggleView('products')">
            <i class="ti ti-package me-1"></i> Products
        </button>
        <button type="button" class="toggle-btn" onclick="toggleView('cart')">
            <i class="ti ti-shopping-cart me-1"></i> Cart
            @if($cartItemsCount > 0)
                <span class="cart-badge">{{ $cartItemsCount }}</span>
            @endif
        </button>
    </div>

    {{-- Main Content --}}
    <div class="pos-main">
        {{-- Cart Section --}}
        <div class="pos-cart" id="cartSection">
            {{-- Customer & Staff Selection --}}
            <div class="p-2 p-md-3 border-bottom">
                <div class="row g-2">
                    <div class="col-12 col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-user"></i></span>
                            <select wire:model="customer_id" class="form-select">
                                <option value="">Walk-In Customer</option>
                                @foreach($availableCustomers as $customer)
                                    <option value="{{ $customer['id'] }}">{{ $customer['name'] }} ({{ $customer['phone'] }})</option>
                                @endforeach
                            </select>
                            <button wire:click="openCustomerModal" class="btn btn-primary" title="Add Customer">
                                <i class="ti ti-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <select wire:model="selectedStaff" class="form-select">
                            <option value="">Select Staff</option>
                            @foreach($availableStaffs as $staff)
                                <option value="{{ $staff['id'] }}">{{ $staff['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Cart Table --}}
            <div class="cart-table">
                <table class="table table-sm mb-0">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th>Product</th>
                            <th class="text-center" style="width: 110px;">Qty</th>
                            <th class="text-end" style="width: 90px;">Total</th>
                            <th style="width: 40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cart as $key => $item)
                        <tr>
                            <td>
                                <div class="fw-medium small">{{ Str::limit($item['name'], 20) }}</div>
                                @if($item['plate_number'])
                                    <small class="text-primary"><i class="ti ti-car"></i> {{ $item['plate_number'] }}</small>
                                @endif
                                <small class="text-muted d-block">TZS {{ number_format($item['price'], 0) }}</small>
                            </td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <button wire:click="decrementQuantity('{{ $key }}')" class="btn btn-outline-secondary qty-btn">
                                        <i class="ti ti-minus"></i>
                                    </button>
                                    <input type="number" value="{{ $item['quantity'] }}" wire:change="updateQuantity('{{ $key }}', $event.target.value)" class="form-control form-control-sm text-center" style="width: 45px;">
                                    <button wire:click="incrementQuantity('{{ $key }}')" class="btn btn-outline-secondary qty-btn">
                                        <i class="ti ti-plus"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="text-end fw-medium small">
                                TZS {{ number_format($item['price'] * $item['quantity'], 0) }}
                            </td>
                            <td class="text-center">
                                <button wire:click="removeFromCart('{{ $key }}')" class="btn btn-sm text-danger p-1">
                                    <i class="ti ti-x"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="ti ti-shopping-cart fs-1 d-block mb-2"></i>
                                <span class="d-none d-lg-inline">Cart is empty. Click on products to add.</span>
                                <span class="d-lg-none">Tap products to add to cart</span>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Cart Summary --}}
            <div class="cart-summary">
                <div class="row g-2 small">
                    <div class="col-6">
                        <span class="text-muted">Items:</span>
                        <span class="fw-medium">{{ $cartItemsCount }}</span>
                    </div>
                    <div class="col-6 text-end">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-medium">TZS {{ number_format($cartTotal + $cartDiscount, 0) }}</span>
                    </div>
                    @if($cartDiscount > 0)
                    <div class="col-6">
                        <span class="text-muted">Discount:</span>
                        <span class="text-danger fw-medium">- TZS {{ number_format($cartDiscount, 0) }}</span>
                    </div>
                    @endif
                    <div class="col-12 text-end pt-2 border-top">
                        <span class="text-muted">Total:</span>
                        <span class="fw-bold text-primary fs-5">TZS {{ number_format($cartTotal, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Products Section --}}
        <div class="pos-products active" id="productsSection">
            {{-- Search & Filters --}}
            <div class="p-2 p-md-3 bg-white border-bottom">
                <div class="row g-2">
                    <div class="col-7 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="ti ti-search"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                    <div class="col-5 col-md-6">
                        <select wire:model.live="selectedCategory" class="form-select">
                            <option value="">All</option>
                            @foreach($availableCategories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="product-grid">
                @forelse($availableItems as $item)
                <div wire:click="addToCart('{{ $item['id'] }}')" class="product-card">
                    <div class="product-icon">
                        @if($item['type'] === 'Service')
                            <i class="ti ti-car-garage text-primary fs-4"></i>
                        @else
                            <i class="ti ti-package text-info fs-4"></i>
                        @endif
                    </div>
                    <div class="small fw-medium text-truncate" title="{{ $item['name'] }}">{{ Str::limit($item['name'], 12) }}</div>
                    <div class="text-primary small fw-bold">TZS {{ number_format($item['selling_price'], 0) }}</div>
                    <span class="badge bg-{{ $item['type'] === 'Service' ? 'primary' : 'info' }}-subtle text-{{ $item['type'] === 'Service' ? 'primary' : 'info' }}" style="font-size: 9px;">
                        {{ $item['type'] === 'Service' ? 'Service' : 'Product' }}
                    </span>
                </div>
                @empty
                <div class="text-center py-5 text-muted" style="grid-column: 1 / -1;">
                    <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                    No products found
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Footer Actions --}}
    <div class="pos-footer">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            {{-- Total Display - First on mobile --}}
            <div class="total-display order-first order-md-last">
                <span class="small d-none d-sm-inline">Total:</span>
                <span class="fs-4 fw-bold">TZS {{ number_format($cartTotal, 0) }}</span>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex gap-2 flex-wrap">
                <button wire:click="openPaymentModal('credit')" class="btn btn-info btn-action" {{ empty($cart) ? 'disabled' : '' }}>
                    <i class="ti ti-file-invoice"></i>
                    <span class="btn-text ms-1 d-none d-sm-inline">Credit</span>
                </button>
                <button wire:click="openPaymentModal('card')" class="btn btn-warning btn-action" {{ empty($cart) ? 'disabled' : '' }}>
                    <i class="ti ti-credit-card"></i>
                    <span class="btn-text ms-1 d-none d-sm-inline">Card</span>
                </button>
                <button wire:click="openPaymentModal('multiple')" class="btn btn-secondary btn-action" {{ empty($cart) ? 'disabled' : '' }}>
                    <i class="ti ti-wallet"></i>
                    <span class="btn-text ms-1 d-none d-sm-inline">Multiple</span>
                </button>
                <button wire:click="openPaymentModal('cash')" class="btn btn-success btn-action" {{ empty($cart) ? 'disabled' : '' }}>
                    <i class="ti ti-cash"></i>
                    <span class="btn-text ms-1 d-none d-sm-inline">Cash</span>
                </button>
                <button wire:click="clearCart" class="btn btn-outline-danger btn-action" {{ empty($cart) ? 'disabled' : '' }}>
                    <i class="ti ti-x"></i>
                    <span class="btn-text ms-1 d-none d-sm-inline">Clear</span>
                </button>
                <button wire:click="openRecentModal" class="btn btn-outline-secondary btn-action">
                    <i class="ti ti-history"></i>
                    <span class="btn-text ms-1 d-none d-md-inline">Recent</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Add Customer Modal --}}
    @if($showCustomerModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-user-plus me-2"></i> Add Customer</h5>
                    <button type="button" class="btn-close" wire:click="closeCustomerModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" wire:model="newCustomerName" class="form-control" placeholder="Customer name">
                        @error('newCustomerName') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone <span class="text-danger">*</span></label>
                        <input type="tel" wire:model="newCustomerPhone" class="form-control" placeholder="Phone number">
                        @error('newCustomerPhone') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" wire:model="newCustomerEmail" class="form-control" placeholder="Email (optional)">
                    </div>
                    <div class="d-flex gap-2">
                        <button wire:click="closeCustomerModal" class="btn btn-light flex-fill">Cancel</button>
                        <button wire:click="saveCustomer" class="btn btn-primary flex-fill">
                            <i class="ti ti-check me-1"></i> Save
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Plate Number Modal --}}
    @if($showPlateModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-car me-2"></i> Enter Plate Number</h5>
                    <button type="button" class="btn-close" wire:click="closePlateModal"></button>
                </div>
                <div class="modal-body">
                    @if($currentItemForPlate)
                    <div class="text-center mb-3">
                        <span class="badge bg-primary fs-6 px-3 py-2">{{ $currentItemForPlate['name'] }}</span>
                    </div>
                    @endif
                    <div class="mb-3">
                        <input type="text" wire:model="plateNumber" class="form-control form-control-lg text-center text-uppercase" placeholder="T 123 ABC" style="font-size: 24px; letter-spacing: 2px;" autofocus>
                    </div>
                    <div class="d-flex gap-2">
                        <button wire:click="closePlateModal" class="btn btn-light flex-fill py-3">Cancel</button>
                        <button wire:click="addItemWithPlate" class="btn btn-primary flex-fill py-3">
                            <i class="ti ti-check me-1"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Payment Modal - Multiple Payment Support --}}
    @if($showPaymentModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-cash-banknote me-2"></i> Payment
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="row g-0">
                        {{-- Left Section: Payment Rows --}}
                        <div class="col-md-8 p-3 border-end">
                            @if($paymentType === 'credit')
                            {{-- Credit Sale Info --}}
                            <div class="alert alert-info mb-3">
                                <i class="ti ti-info-circle me-2"></i>
                                This sale will be recorded as <strong>unpaid</strong>. Customer can pay later.
                            </div>
                            @else
                            {{-- Payment Rows --}}
                            <div class="payment-rows mb-3">
                                @foreach($paymentRows as $index => $row)
                                <div class="card mb-2 border" wire:key="payment-row-{{ $index }}">
                                    <div class="card-body p-3">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Amount <span class="text-danger">*</span></label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">TSh</span>
                                                    <input type="number"
                                                           wire:model.live="paymentRows.{{ $index }}.amount"
                                                           class="form-control text-end"
                                                           placeholder="0.00"
                                                           min="0"
                                                           step="0.01"
                                                           inputmode="decimal">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small mb-1">Payment Method <span class="text-danger">*</span></label>
                                                <select wire:model.live="paymentRows.{{ $index }}.payment_method_id" class="form-select form-select-sm">
                                                    <option value="">Select Method</option>
                                                    @foreach($availablePaymentMethods as $method)
                                                        <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small mb-1">Note</label>
                                                <input type="text"
                                                       wire:model="paymentRows.{{ $index }}.note"
                                                       class="form-control form-control-sm"
                                                       placeholder="Note...">
                                            </div>
                                            <div class="col-md-1">
                                                @if(count($paymentRows) > 1)
                                                <button wire:click="removePaymentRow({{ $index }})"
                                                        class="btn btn-outline-danger btn-sm w-100"
                                                        title="Remove">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Add Payment Row Button --}}
                            <button wire:click="addPaymentRow" class="btn btn-primary w-100 mb-3">
                                <i class="ti ti-plus me-1"></i> Add Payment Row
                            </button>
                            @endif

                            {{-- Notes Section --}}
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small">Sell note:</label>
                                    <textarea wire:model="sellNote" class="form-control form-control-sm" rows="2" placeholder="Sell note"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Staff note:</label>
                                    <textarea wire:model="staffNote" class="form-control form-control-sm" rows="2" placeholder="Staff note"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Right Section: Summary --}}
                        <div class="col-md-4 bg-warning p-3 d-flex flex-column">
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <div class="text-dark small">Total Items:</div>
                                    <div class="fs-4 fw-bold text-dark">{{ number_format($cartItemsCount, 2) }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-dark small">Total Payable:</div>
                                    <div class="fs-5 fw-bold text-dark">TSh {{ number_format($cartTotal, 2) }}</div>
                                </div>

                                @if($paymentType !== 'credit')
                                <div class="mb-3">
                                    <div class="text-dark small">Total Paying:</div>
                                    <div class="fs-5 fw-bold text-dark">TSh {{ number_format($this->totalPaying, 2) }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-dark small">Change Return:</div>
                                    <div class="fs-5 fw-bold text-success">TSh {{ number_format($this->changeReturn, 2) }}</div>
                                </div>

                                <div class="mb-3 pb-3 border-bottom border-dark">
                                    <div class="text-dark small">Balance:</div>
                                    <div class="fs-5 fw-bold {{ $this->balanceDue > 0 ? 'text-danger' : 'text-dark' }}">
                                        TSh {{ number_format($this->balanceDue, 2) }}
                                    </div>
                                </div>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            <div class="d-flex gap-2 mt-auto">
                                <button wire:click="closePaymentModal" class="btn btn-light flex-fill">
                                    Close
                                </button>
                                <button wire:click="processSale"
                                        class="btn btn-primary flex-fill"
                                        {{ $paymentType !== 'credit' && $this->totalPaying <= 0 ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="processSale">
                                        <i class="ti ti-check me-1"></i> Finalize Payment
                                    </span>
                                    <span wire:loading wire:target="processSale">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Sales Modal --}}
    @if($showRecentModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-history me-2"></i> Recent Transactions</h5>
                    <button type="button" class="btn-close" wire:click="closeRecentModal"></button>
                </div>
                <div class="modal-body p-0" style="max-height: 60vh; overflow-y: auto;">
                    {{-- Mobile Card View --}}
                    <div class="d-md-none">
                        @forelse($recentSales as $sale)
                        <div class="p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="fw-bold">INV-{{ strtoupper(substr($sale['id'], 0, 8)) }}</span>
                                    <br>
                                    <small class="text-muted">{{ $sale['customer']['name'] ?? 'Walk-In' }}</small>
                                </div>
                                <span class="badge bg-{{ $sale['payment_status'] === 'paid' ? 'success' : ($sale['payment_status'] === 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($sale['payment_status']) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">{{ \Carbon\Carbon::parse($sale['sale_date'])->format('M d, H:i') }}</small>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="fw-bold text-primary">TZS {{ number_format($sale['total_amount'], 0) }}</span>
                                    <button wire:click="showReceipt('{{ $sale['id'] }}')" class="btn btn-sm btn-outline-primary" title="Print Receipt">
                                        <i class="ti ti-printer"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted">No recent sales</div>
                        @endforelse
                    </div>

                    {{-- Desktop Table View --}}
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                <tr>
                                    <td>
                                        <div class="small">{{ \Carbon\Carbon::parse($sale['sale_date'])->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($sale['sale_date'])->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">INV-{{ strtoupper(substr($sale['id'], 0, 8)) }}</span>
                                    </td>
                                    <td>{{ $sale['customer']['name'] ?? 'Walk-In' }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($sale['total_amount'], 0) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $sale['payment_status'] === 'paid' ? 'success' : ($sale['payment_status'] === 'partial' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($sale['payment_status']) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="showReceipt('{{ $sale['id'] }}')" class="btn btn-sm btn-outline-primary" title="Print Receipt">
                                            <i class="ti ti-printer"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No recent sales</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button wire:click="closeRecentModal" class="btn btn-light">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Receipt Modal for Thermal Printer --}}
    @if($showReceiptModal && $lastSale)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 py-2">
                    <h6 class="modal-title"><i class="ti ti-receipt me-2"></i> Receipt</h6>
                    <button type="button" class="btn-close" wire:click="closeReceiptModal"></button>
                </div>
                <div class="modal-body p-0">
                    {{-- Receipt Preview --}}
                    <div id="receiptContent" class="receipt-thermal">
                        {{-- Header --}}
                        <div class="receipt-header">
                            <div class="shop-name">{{ $carwashInfo['name'] ?? 'SHOP NAME' }}</div>
                            <div class="shop-address">{{ $carwashInfo['address'] ?? '' }}</div>
                            <div class="shop-contact">
                                @if($carwashInfo['phone'] ?? false)
                                    Mobile: {{ $carwashInfo['phone'] }}
                                @endif
                                @if($carwashInfo['website'] ?? false)
                                    <br>{{ $carwashInfo['website'] }}
                                @endif
                            </div>
                        </div>

                        <div class="receipt-divider">=================================</div>

                        <div class="receipt-title">Invoice</div>

                        <div class="receipt-divider">---------------------------------</div>

                        {{-- Invoice Info --}}
                        <div class="receipt-info">
                            <div class="info-row">
                                <span>Invoice No.</span>
                                <span>{{ $lastSale['invoice_number'] ?? 'INV-' . strtoupper(substr($lastSale['id'], 0, 8)) }}</span>
                            </div>
                            <div class="info-row">
                                <span>Date</span>
                                <span>{{ \Carbon\Carbon::parse($lastSale['sale_date'])->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="info-row">
                                <span>Customer</span>
                                <span>{{ $lastSale['customer']['name'] ?? 'Walk-In Customer' }}</span>
                            </div>
                            @if($lastSale['customer']['phone'] ?? false)
                            <div class="info-row">
                                <span>Mobile</span>
                                <span>{{ $lastSale['customer']['phone'] }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="receipt-divider">=================================</div>

                        {{-- Items Table --}}
                        <table class="receipt-items">
                            <thead>
                                <tr>
                                    <th class="text-left">Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-right">Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lastSaleItems as $item)
                                <tr>
                                    <td class="text-left">
                                        {{ Str::limit($item['item']['name'] ?? '-', 15) }}
                                        @if($item['plate_number'])
                                            <br><small>{{ $item['plate_number'] }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ number_format($item['quantity'], 0) }}</td>
                                    <td class="text-right">{{ number_format($item['price'], 0) }}</td>
                                    <td class="text-right">{{ number_format($item['price'] * $item['quantity'], 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="receipt-divider">---------------------------------</div>

                        {{-- Payments --}}
                        @if(count($lastSalePayments) > 0)
                        <div class="receipt-payments">
                            @foreach($lastSalePayments as $payment)
                            <div class="payment-row">
                                <span>{{ $payment['payment_method']['name'] ?? 'Payment' }}</span>
                                <span>TSh {{ number_format($payment['amount'], 2) }}</span>
                                <span>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d/m/Y') }}</span>
                            </div>
                            @endforeach
                            <div class="payment-row total-paid">
                                <span>Total Paid</span>
                                <span>TSh {{ number_format(collect($lastSalePayments)->sum('amount'), 2) }}</span>
                            </div>
                        </div>
                        <div class="receipt-divider">---------------------------------</div>
                        @endif

                        {{-- Totals --}}
                        <div class="receipt-totals">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span>TSh {{ number_format($lastSale['total_amount'], 2) }}</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total:</span>
                                <span>TSh {{ number_format($lastSale['total_amount'], 2) }}</span>
                            </div>
                            @php
                                $totalPaid = collect($lastSalePayments)->sum('amount');
                                $balance = (float)$lastSale['total_amount'] - $totalPaid;
                            @endphp
                            @if($balance > 0)
                            <div class="total-row balance-due">
                                <span>Balance Due:</span>
                                <span>TSh {{ number_format($balance, 2) }}</span>
                            </div>
                            @elseif($totalPaid > (float)$lastSale['total_amount'])
                            <div class="total-row change">
                                <span>Change:</span>
                                <span>TSh {{ number_format($totalPaid - (float)$lastSale['total_amount'], 2) }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="receipt-divider">=================================</div>

                        {{-- Footer --}}
                        <div class="receipt-footer">
                            <div class="thank-you">Thank you for your business!</div>
                            <div class="visit-again">Please visit again</div>
                            <div class="powered-by">Powered by TechScales POS</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-2">
                    <button wire:click="closeReceiptModal" class="btn btn-sm btn-secondary">
                        <i class="ti ti-x me-1"></i> Close
                    </button>
                    <button onclick="printThermalReceipt()" class="btn btn-sm btn-primary">
                        <i class="ti ti-printer me-1"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Thermal Receipt Print Styles --}}
    <style>
        /* Receipt Thermal Styles */
        .receipt-thermal {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 10px;
            background: #fff;
            max-width: 280px;
            margin: 0 auto;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 5px;
        }

        .receipt-header .shop-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-header .shop-address,
        .receipt-header .shop-contact {
            font-size: 11px;
        }

        .receipt-divider {
            text-align: center;
            font-size: 10px;
            letter-spacing: -1px;
            margin: 5px 0;
        }

        .receipt-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }

        .receipt-info {
            margin: 5px 0;
        }

        .receipt-info .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .receipt-items th {
            border-bottom: 1px dashed #000;
            padding: 3px 2px;
            font-weight: bold;
        }

        .receipt-items td {
            padding: 3px 2px;
            vertical-align: top;
        }

        .receipt-items small {
            font-size: 9px;
            color: #666;
        }

        .receipt-payments {
            margin: 5px 0;
        }

        .receipt-payments .payment-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            gap: 5px;
        }

        .receipt-payments .total-paid {
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        .receipt-totals {
            margin: 5px 0;
        }

        .receipt-totals .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }

        .receipt-totals .grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 3px;
            margin-top: 3px;
        }

        .receipt-totals .balance-due {
            color: #c00;
            font-weight: bold;
        }

        .receipt-totals .change {
            color: #080;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
        }

        .receipt-footer .thank-you {
            font-weight: bold;
            font-size: 12px;
        }

        .receipt-footer .powered-by {
            margin-top: 5px;
            font-size: 9px;
            color: #666;
        }

        /* Print Styles */
        @media print {
            body * {
                visibility: hidden;
            }

            #receiptContent,
            #receiptContent * {
                visibility: visible;
            }

            #receiptContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
                padding: 2mm;
                margin: 0;
                font-size: 10pt;
            }

            .receipt-thermal {
                max-width: 80mm;
                padding: 0;
            }

            .receipt-header .shop-name {
                font-size: 14pt;
            }

            .receipt-divider {
                font-size: 8pt;
            }

            .receipt-title {
                font-size: 12pt;
            }

            .receipt-items,
            .receipt-info .info-row,
            .receipt-payments .payment-row,
            .receipt-totals .total-row {
                font-size: 9pt;
            }

            .receipt-totals .grand-total {
                font-size: 11pt;
            }

            .receipt-footer {
                font-size: 8pt;
            }

            .modal,
            .modal-dialog,
            .modal-content,
            .modal-header,
            .modal-footer {
                position: static;
                width: auto;
                padding: 0;
                margin: 0;
                border: none;
                background: none;
                box-shadow: none;
            }

            .btn-close,
            .modal-footer {
                display: none !important;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>

    <script>
        function toggleView(view) {
            const cartSection = document.getElementById('cartSection');
            const productsSection = document.getElementById('productsSection');
            const toggleBtns = document.querySelectorAll('.toggle-btn');

            toggleBtns.forEach(btn => btn.classList.remove('active'));

            if (view === 'cart') {
                cartSection.classList.add('active');
                productsSection.classList.remove('active');
                toggleBtns[1].classList.add('active');
            } else {
                productsSection.classList.add('active');
                cartSection.classList.remove('active');
                toggleBtns[0].classList.add('active');
            }
        }

        // Auto-switch to cart when item is added on mobile
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('itemAdded', () => {
                if (window.innerWidth < 992) {
                    toggleView('cart');
                }
            });
        });

        // Print thermal receipt
        function printThermalReceipt() {
            const receiptContent = document.getElementById('receiptContent');
            if (!receiptContent) return;

            // Create a new window for printing
            const printWindow = window.open('', '_blank', 'width=300,height=600');

            // Get the receipt styles
            const styles = `
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        font-size: 12px;
                        line-height: 1.4;
                        width: 80mm;
                        padding: 2mm;
                        background: #fff;
                    }
                    .receipt-header { text-align: center; margin-bottom: 5px; }
                    .receipt-header .shop-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
                    .receipt-header .shop-address, .receipt-header .shop-contact { font-size: 11px; }
                    .receipt-divider { text-align: center; font-size: 10px; letter-spacing: -1px; margin: 5px 0; }
                    .receipt-title { text-align: center; font-size: 14px; font-weight: bold; }
                    .receipt-info { margin: 5px 0; }
                    .receipt-info .info-row { display: flex; justify-content: space-between; font-size: 11px; }
                    .receipt-items { width: 100%; border-collapse: collapse; font-size: 11px; }
                    .receipt-items th { border-bottom: 1px dashed #000; padding: 3px 2px; font-weight: bold; }
                    .receipt-items td { padding: 3px 2px; vertical-align: top; }
                    .receipt-items small { font-size: 9px; color: #666; }
                    .receipt-payments { margin: 5px 0; }
                    .receipt-payments .payment-row { display: flex; justify-content: space-between; font-size: 11px; gap: 5px; }
                    .receipt-payments .total-paid { font-weight: bold; border-top: 1px dashed #000; padding-top: 3px; margin-top: 3px; }
                    .receipt-totals { margin: 5px 0; }
                    .receipt-totals .total-row { display: flex; justify-content: space-between; font-size: 12px; }
                    .receipt-totals .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #000; padding-top: 3px; margin-top: 3px; }
                    .receipt-totals .balance-due { color: #c00; font-weight: bold; }
                    .receipt-totals .change { color: #080; }
                    .receipt-footer { text-align: center; margin-top: 10px; font-size: 10px; }
                    .receipt-footer .thank-you { font-weight: bold; font-size: 12px; }
                    .receipt-footer .powered-by { margin-top: 5px; font-size: 9px; color: #666; }
                    .text-left { text-align: left; }
                    .text-center { text-align: center; }
                    .text-right { text-align: right; }
                    @page { size: 80mm auto; margin: 0; }
                    @media print {
                        body { width: 80mm; }
                    }
                </style>
            `;

            // Build the print document
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Receipt</title>
                    ${styles}
                </head>
                <body>
                    ${receiptContent.innerHTML}
                </body>
                </html>
            `);

            printWindow.document.close();

            // Wait for content to load, then print
            printWindow.onload = function() {
                printWindow.focus();
                printWindow.print();
                // Close after print dialog closes (with small delay)
                setTimeout(() => {
                    printWindow.close();
                }, 500);
            };
        }
    </script>
</div>
