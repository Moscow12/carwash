<div class="pos-container" x-data="{ view: 'products' }" :data-view="view">
    <style>
        .pos-container {
            --pos-surface: var(--bs-body-bg);
            --pos-muted: var(--bs-tertiary-bg);
            --pos-border: var(--bs-border-color);
            --pos-cart-width: 400px;
            display: flex;
            flex-direction: column;
            background: var(--pos-muted);
            border: 1px solid var(--pos-border);
            border-radius: 16px;
            overflow: clip;
        }

        @media (min-width: 992px) {
            .pos-container { height: calc(100dvh - 130px); min-height: 560px; }
            .product-grid { overflow-y: auto; flex: 1; }
        }

        /* Header */
        .pos-header {
            background: var(--pos-surface);
            border-bottom: 1px solid var(--pos-border);
            padding: 10px 16px;
        }

        .pos-header .store-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Layout */
        .pos-main {
            display: flex;
            flex: 1;
            min-height: 0;
        }

        .pos-products {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .pos-cart {
            width: var(--pos-cart-width);
            flex-shrink: 0;
            background: var(--pos-surface);
            border-left: 1px solid var(--pos-border);
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        /* Mobile tabs */
        .pos-tabs {
            display: none;
            background: var(--pos-surface);
            border-bottom: 1px solid var(--pos-border);
            padding: 6px;
            gap: 6px;
        }

        .pos-tabs button {
            flex: 1;
            border: 0;
            border-radius: 10px;
            padding: 10px;
            font-weight: 600;
            background: transparent;
            color: var(--bs-secondary-color);
        }

        .pos-tabs button.active {
            background: var(--bs-primary-bg-subtle);
            color: var(--bs-primary-text-emphasis);
        }

        .pos-mobile-bar {
            display: none;
        }

        @media (max-width: 991.98px) {
            .pos-tabs { display: flex; }
            .pos-cart {
                width: 100%;
                border-left: 0;
            }
            .pos-cart .cart-footer {
                position: sticky;
                bottom: 0;
                z-index: 5;
                box-shadow: 0 -6px 16px rgba(0, 0, 0, .06);
            }
            .pos-container[data-view="products"] .pos-cart { display: none; }
            .pos-container[data-view="cart"] .pos-products { display: none; }
            .pos-container[data-view="products"] .pos-mobile-bar { display: block; }
            .pos-products { padding-bottom: 84px; }
        }

        .pos-mobile-bar {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 10px 12px calc(10px + env(safe-area-inset-bottom));
            background: var(--pos-surface);
            border-top: 1px solid var(--pos-border);
            z-index: 1030;
        }

        /* Category chips */
        .category-chips {
            display: flex;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 2px;
            scrollbar-width: none;
        }

        .category-chips::-webkit-scrollbar { display: none; }

        .category-chips .btn {
            white-space: nowrap;
            border-radius: 999px;
            flex-shrink: 0;
        }

        /* Product grid */
        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 12px;
            padding: 16px;
            align-content: start;
        }

        @media (max-width: 575.98px) {
            .product-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; padding: 12px; }
        }

        .product-card {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
            text-align: left;
            background: var(--pos-surface);
            border: 1px solid var(--pos-border);
            border-radius: 14px;
            padding: 10px;
            color: inherit;
            transition: border-color .15s, box-shadow .15s, transform .1s;
            -webkit-tap-highlight-color: transparent;
        }

        .product-card:hover { border-color: var(--bs-primary); box-shadow: 0 4px 14px rgba(13, 110, 253, .12); }
        .product-card:active { transform: scale(.97); }
        .product-card.in-cart { border-color: var(--bs-primary); }

        .product-thumb {
            aspect-ratio: 4 / 3;
            border-radius: 10px;
            background: var(--pos-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-thumb img { width: 100%; height: 100%; object-fit: cover; }

        .product-name {
            font-weight: 600;
            font-size: .875rem;
            line-height: 1.25;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.5em;
        }

        .product-qty-badge {
            position: absolute;
            top: 6px;
            right: 6px;
            min-width: 26px;
            height: 26px;
            border-radius: 13px;
            padding: 0 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            font-weight: 700;
            background: var(--bs-primary);
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, .2);
        }

        /* Cart */
        .cart-items {
            flex: 1;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .cart-line {
            padding: 12px 16px;
            border-bottom: 1px solid var(--pos-border);
        }

        .qty-stepper {
            display: inline-flex;
            align-items: center;
            border: 1px solid var(--pos-border);
            border-radius: 10px;
            overflow: hidden;
        }

        .qty-stepper button {
            width: 38px;
            height: 38px;
            border: 0;
            background: var(--pos-muted);
            color: inherit;
        }

        .qty-stepper input {
            width: 48px;
            height: 38px;
            border: 0;
            text-align: center;
            font-weight: 600;
            background: transparent;
            color: inherit;
            -moz-appearance: textfield;
        }

        .qty-stepper input::-webkit-outer-spin-button,
        .qty-stepper input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

        .cart-footer {
            border-top: 1px solid var(--pos-border);
            padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
            background: var(--pos-surface);
        }

        .pay-btn {
            min-height: 52px;
            font-weight: 600;
            border-radius: 12px;
        }

        .pos-field {
            border-radius: 12px;
            padding: 10px 12px;
            min-height: 60px;
            background: var(--pos-muted);
        }

        .served-by { background: var(--bs-primary-bg-subtle); }

        .pos-field .avatar-initials {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--bs-primary);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }

        .customer-field .avatar-initials {
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
        }

        .pos-field .field-action {
            width: 36px;
            height: 36px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* Make Select2 read like the plain "Served by" select */
        .pos-field .select2-container { display: block; }

        .pos-field .select2-container--bootstrap-5 .select2-selection {
            border: 0;
            background-color: transparent;
            box-shadow: none !important;
            min-height: 24px;
            padding: 0 2.75rem 0 0;
            font-weight: 600;
            font-size: .875rem;
        }

        .pos-field .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: 24px;
            color: inherit;
        }

        .pos-field .select2-container--bootstrap-5 .select2-selection--single .select2-selection__placeholder {
            color: inherit;
        }

        .pos-field .select2-container--bootstrap-5 .select2-selection__clear {
            right: 1.5rem;
        }

        .pos-container .pos-field .form-select {
            border: 0;
            background-color: transparent;
            font-weight: 600;
            padding-left: 0;
            min-height: auto;
            box-shadow: none !important;
        }

        .pos-toast {
            position: fixed;
            top: 70px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1090;
            width: min(92vw, 480px);
        }

        /* Touch-friendly inputs; 16px avoids iOS zoom */
        .pos-container .form-control,
        .pos-container .form-select { min-height: 44px; }

        @media (max-width: 575.98px) {
            .pos-container .form-control,
            .pos-container .form-select { font-size: 16px; }
        }
    </style>

    {{-- Header --}}
    <div class="pos-header">
        <div class="d-flex justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <span class="store-icon bg-primary-subtle text-primary"><i class="ti ti-building-store fs-4"></i></span>
                <div class="min-w-0">
                    @if(count($ownerBusinesses) > 1)
                        <select wire:model.live="selectedBusiness" class="form-select form-select-sm fw-semibold border-0 ps-0 bg-transparent shadow-none" style="min-height: auto;" aria-label="Business">
                            @foreach($ownerBusinesses as $id => $name)
                                <option value="{{ $id }}" wire:key="pos-business-{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    @else
                        <div class="fw-semibold text-truncate">{{ $ownerBusinesses[$selectedBusiness] ?? 'No business' }}</div>
                    @endif
                    <small class="text-muted d-none d-sm-block">{{ now()->format('D, M d Y · H:i') }}</small>
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <button wire:click="openRecentModal" class="btn btn-sm btn-outline-secondary" title="Recent sales">
                    <i class="ti ti-history"></i><span class="d-none d-md-inline ms-1">Recent</span>
                </button>
                <a href="{{ route('owner.dashboard') }}" class="btn btn-sm btn-outline-secondary" title="Back to dashboard">
                    <i class="ti ti-arrow-left"></i><span class="d-none d-md-inline ms-1">Back</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="pos-toast" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
            <div class="alert alert-success shadow d-flex align-items-center mb-0 py-2" role="alert">
                <i class="ti ti-circle-check me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('message') }}</div>
                <button type="button" class="btn-close ms-2" @click="show = false"></button>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="pos-toast" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition>
            <div class="alert alert-danger shadow d-flex align-items-center mb-0 py-2" role="alert">
                <i class="ti ti-alert-circle me-2 fs-5"></i>
                <div class="flex-grow-1">{{ session('error') }}</div>
                <button type="button" class="btn-close ms-2" @click="show = false"></button>
            </div>
        </div>
    @endif

    {{-- Mobile Tabs --}}
    <div class="pos-tabs">
        <button type="button" :class="{ 'active': view === 'products' }" @click="view = 'products'">
            <i class="ti ti-layout-grid me-1"></i> Products
        </button>
        <button type="button" :class="{ 'active': view === 'cart' }" @click="view = 'cart'">
            <i class="ti ti-shopping-cart me-1"></i> Cart
            @if($cartItemsCount > 0)
                <span class="badge rounded-pill bg-danger ms-1">{{ $cartItemsCount }}</span>
            @endif
        </button>
    </div>

    <div class="pos-main">
        {{-- Products --}}
        <section class="pos-products">
            <div class="p-3 pb-2 bg-body border-bottom">
                <div class="input-group mb-2">
                    <span class="input-group-text bg-body"><i class="ti ti-search"></i></span>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search products or services..." aria-label="Search products">
                    <button wire:click="openScanner" class="btn btn-primary" type="button" title="Scan barcode">
                        <i class="ti ti-scan"></i><span class="d-none d-sm-inline ms-1">Scan</span>
                    </button>
                </div>
                @if(count($availableCategories) > 0)
                    <div class="category-chips">
                        <button type="button" wire:click="$set('selectedCategory', '')" class="btn btn-sm {{ $selectedCategory === '' ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All
                        </button>
                        @foreach($availableCategories as $categoryId => $categoryName)
                            <button type="button" wire:key="category-chip-{{ $categoryId }}" wire:click="$set('selectedCategory', '{{ $categoryId }}')" class="btn btn-sm {{ $selectedCategory === $categoryId ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $categoryName }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @php
                $cartQuantities = collect($cart)->groupBy('id')->map(fn ($lines) => $lines->sum('quantity'));
            @endphp
            <div class="product-grid" wire:loading.class="opacity-75" wire:target="search,selectedCategory">
                @forelse($availableItems as $item)
                    @php $inCartQuantity = $cartQuantities[$item['id']] ?? 0; @endphp
                    <button type="button"
                            wire:key="product-{{ $item['id'] }}"
                            wire:click="addToCart('{{ $item['id'] }}')"
                            class="product-card {{ $inCartQuantity > 0 ? 'in-cart' : '' }}"
                            title="{{ $item['name'] }}">
                        @if($inCartQuantity > 0)
                            <span class="product-qty-badge">{{ $inCartQuantity + 0 }}</span>
                        @endif
                        <div class="product-thumb">
                            @if(!empty($item['image']))
                                <img src="{{ asset('storage/' . $item['image']) }}" alt="" loading="lazy" onerror="this.remove()">
                            @endif
                            <i class="ti ti-{{ $item['type'] === 'Service' ? 'car-garage text-primary' : 'package text-info' }} fs-1"></i>
                        </div>
                        <div class="product-name">{{ $item['name'] }}</div>
                        <div class="d-flex justify-content-between align-items-center gap-1">
                            <span class="fw-bold text-primary small">TZS {{ number_format($item['selling_price'], 0) }}</span>
                            @if($item['require_plate_number'] === 'yes')
                                <i class="ti ti-car text-muted" title="Requires plate number"></i>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="text-center py-5 text-muted" style="grid-column: 1 / -1;">
                        <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                        {{ $search || $selectedCategory ? 'No products match your filters.' : 'No products available for this business.' }}
                        @if($search || $selectedCategory)
                            <div class="mt-2">
                                <button type="button" wire:click="clearFilters" class="btn btn-sm btn-outline-primary">Clear filters</button>
                            </div>
                        @endif
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Cart --}}
        <aside class="pos-cart">
            <div class="p-3 border-bottom d-flex flex-column gap-2">
                {{-- Served by --}}
                @php
                    $staffName = $availableStaffs[$selectedStaff] ?? null;
                    $staffInitials = $staffName ? collect(explode(' ', $staffName))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') : '?';
                @endphp
                <div class="pos-field served-by d-flex align-items-center gap-2 {{ $staffName ? '' : 'border border-danger' }}">
                    <span class="avatar-initials {{ $staffName ? '' : 'bg-danger' }}">{{ $staffInitials }}</span>
                    <div class="flex-grow-1 min-w-0">
                        <small class="text-muted d-block lh-1">Served by</small>
                        <select wire:model.live="selectedStaff" class="form-select form-select-sm" aria-label="Served by">
                            @if(!$staffName)
                                <option value="">Select staff member</option>
                            @endif
                            @foreach($availableStaffs as $staffId => $name)
                                <option value="{{ $staffId }}" wire:key="staff-option-{{ $staffId }}">
                                    {{ $name }}{{ $staffId === $defaultStaffId ? ' (You)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if($defaultStaffId && $selectedStaff !== $defaultStaffId)
                        <button type="button" wire:click="$set('selectedStaff', '{{ $defaultStaffId }}')" class="btn btn-sm btn-link text-decoration-none" title="Switch back to yourself">
                            <i class="ti ti-arrow-back-up"></i> Me
                        </button>
                    @endif
                </div>

                {{-- Customer --}}
                <div class="pos-field customer-field d-flex align-items-center gap-2">
                    <span class="avatar-initials"><i class="ti ti-user"></i></span>
                    <div class="flex-grow-1 min-w-0">
                        <small class="text-muted d-block lh-1">Customer</small>
                        {{-- Ignored by morphing so Select2 is not torn down on every cart update; the key rebuilds it when the list or selection changes. --}}
                        <div wire:ignore wire:key="customer-select-{{ count($availableCustomers) }}-{{ $customer_id ?: 'walk-in' }}">
                            <x-forms.select2
                                name="customer_id"
                                :options="$availableCustomers"
                                :selected="$customer_id"
                                wire:model="customer_id"
                                placeholder="Walk-in customer"
                                wrapper="false"
                                inputGroup="true"
                                class="form-select"
                            />
                        </div>
                    </div>
                    <button type="button" wire:click="openCustomerModal" class="btn btn-sm btn-outline-primary field-action" title="Add new customer" aria-label="Add new customer">
                        <i class="ti ti-user-plus"></i>
                    </button>
                </div>
            </div>

            {{-- Cart lines --}}
            <div class="cart-items">
                @forelse($cart as $key => $item)
                    <div class="cart-line" wire:key="cart-line-{{ $key }}">
                        <div class="d-flex justify-content-between gap-2">
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ $item['name'] }}</div>
                                <small class="text-muted">TZS {{ number_format($item['price'], 0) }} each</small>
                                @if($item['plate_number'])
                                    <span class="badge bg-primary-subtle text-primary ms-1"><i class="ti ti-car"></i> {{ $item['plate_number'] }}</span>
                                @endif
                            </div>
                            <button wire:click="removeFromCart('{{ $key }}')" class="btn btn-sm btn-link text-danger p-0 align-self-start" title="Remove {{ $item['name'] }}">
                                <i class="ti ti-trash fs-5"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <div class="qty-stepper">
                                <button type="button" wire:click="decrementQuantity('{{ $key }}')" aria-label="Decrease quantity"><i class="ti ti-minus"></i></button>
                                <input type="number" min="1" inputmode="numeric" value="{{ $item['quantity'] }}" wire:change="updateQuantity('{{ $key }}', $event.target.value)" aria-label="Quantity">
                                <button type="button" wire:click="incrementQuantity('{{ $key }}')" aria-label="Increase quantity"><i class="ti ti-plus"></i></button>
                            </div>
                            <span class="fw-bold">TZS {{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 px-3">
                        <i class="ti ti-shopping-cart fs-1 d-block mb-2"></i>
                        <div class="fw-semibold">Cart is empty</div>
                        <small><span class="d-none d-lg-inline">Click</span><span class="d-lg-none">Tap</span> a product to add it.</small>
                        <div class="d-lg-none mt-3">
                            <button type="button" class="btn btn-outline-primary btn-sm" @click="view = 'products'">
                                <i class="ti ti-layout-grid me-1"></i> Browse products
                            </button>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Totals & payment --}}
            <div class="cart-footer">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>{{ $cartItemsCount }} {{ Str::plural('item', $cartItemsCount) }}</span>
                    <span>Subtotal TZS {{ number_format($cartTotal + $cartDiscount, 0) }}</span>
                </div>
                @if($cartDiscount > 0)
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Discount</span>
                        <span class="text-danger">- TZS {{ number_format($cartDiscount, 0) }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between align-items-baseline mb-3">
                    <span class="fw-semibold">Total</span>
                    <span class="fs-3 fw-bold text-primary">TZS {{ number_format($cartTotal, 0) }}</span>
                </div>

                <button wire:click="quickCashOut" wire:loading.attr="disabled" class="btn btn-success pay-btn w-100 mb-2" @disabled(empty($cart))>
                    <span wire:loading.remove wire:target="quickCashOut"><i class="ti ti-cash me-1"></i> Pay Cash · TZS {{ number_format($cartTotal, 0) }}</span>
                    <span wire:loading wire:target="quickCashOut"><span class="spinner-border spinner-border-sm me-1"></span> Processing...</span>
                </button>
                <div class="d-grid gap-2" style="grid-template-columns: repeat(3, 1fr);">
                    <button wire:click="openPaymentModal('card')" class="btn btn-outline-primary pay-btn" @disabled(empty($cart))>
                        <i class="ti ti-credit-card d-block fs-5"></i><small>Card</small>
                    </button>
                    <button wire:click="openPaymentModal('multiple')" class="btn btn-outline-primary pay-btn" @disabled(empty($cart))>
                        <i class="ti ti-arrows-split-2 d-block fs-5"></i><small>Split</small>
                    </button>
                    <button wire:click="openPaymentModal('credit')" class="btn btn-outline-primary pay-btn" @disabled(empty($cart))>
                        <i class="ti ti-file-invoice d-block fs-5"></i><small>Credit</small>
                    </button>
                </div>
                @if(!empty($cart))
                    <button wire:click="clearCart" wire:confirm="Remove all items from the cart?" class="btn btn-link text-danger w-100 mt-1 text-decoration-none">
                        <i class="ti ti-trash me-1"></i> Clear cart
                    </button>
                @endif
            </div>
        </aside>
    </div>

    {{-- Mobile bottom bar (products view) --}}
    <div class="pos-mobile-bar d-lg-none">
        <button type="button" class="btn btn-primary w-100 pay-btn d-flex justify-content-between align-items-center px-3" @click="view = 'cart'" @disabled(empty($cart))>
            <span>
                <i class="ti ti-shopping-cart me-1"></i>
                View cart
                @if($cartItemsCount > 0)
                    <span class="badge rounded-pill bg-light text-primary ms-1">{{ $cartItemsCount }}</span>
                @endif
            </span>
            <span class="fw-bold">TZS {{ number_format($cartTotal, 0) }}</span>
        </button>
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
                        <input type="text" wire:model="plateNumber" wire:keydown.enter="addItemWithPlate" x-init="$nextTick(() => $el.focus())" class="form-control form-control-lg text-center text-uppercase" placeholder="T 123 ABC" style="font-size: 24px; letter-spacing: 2px;" autocomplete="off">
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
        <div class="modal-dialog modal-dialog-centered modal-lg modal-fullscreen-sm-down">
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
                                                <x-forms.select2
                                                    name="paymentRows.{{ $index }}.payment_method_id"
                                                    :options="$availablePaymentMethods"
                                                    wire:model.live="paymentRows.{{ $index }}.payment_method_id"
                                                    placeholder="Select Method"
                                                    wrapper="false"
                                                    class="form-select-sm"
                                                />
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
                        <div class="col-md-4 bg-body-tertiary p-3 d-flex flex-column order-first order-md-last">
                            <div class="flex-grow-1">
                                <div class="mb-3">
                                    <div class="text-muted small">Total Items:</div>
                                    <div class="fs-4 fw-bold">{{ number_format($cartItemsCount, 2) }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-muted small">Total Payable:</div>
                                    <div class="fs-5 fw-bold">TSh {{ number_format($cartTotal, 2) }}</div>
                                </div>

                                @if($paymentType !== 'credit')
                                <div class="mb-3">
                                    <div class="text-muted small">Total Paying:</div>
                                    <div class="fs-5 fw-bold">TSh {{ number_format($this->totalPaying, 2) }}</div>
                                </div>

                                <div class="mb-3">
                                    <div class="text-muted small">Change Return:</div>
                                    <div class="fs-5 fw-bold text-success">TSh {{ number_format($this->changeReturn, 2) }}</div>
                                </div>

                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="text-muted small">Balance:</div>
                                    <div class="fs-5 fw-bold {{ $this->balanceDue > 0 ? 'text-danger' : '' }}">
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
                        {{-- Header with Logo --}}
                        <div class="receipt-header">
                            @php
                                $showLogo = ($businessSettings['show_logo_on_receipt'] ?? false) && ($businessInfo['logo'] ?? false);
                                $logoUrl = $businessInfo['logo'] ?? null;
                            @endphp
                            @if($showLogo && $logoUrl)
                                <img src="{{ asset('storage/' . $logoUrl) }}" alt="Logo" class="receipt-logo" style="max-width: 120px; max-height: 60px; margin-bottom: 5px;">
                            @endif
                            <div class="shop-name">{{ $businessSettings['business_name'] ?? $businessInfo['name'] ?? 'SHOP NAME' }}</div>
                            <div class="shop-address">{{ $businessSettings['business_address'] ?? $businessInfo['address'] ?? '' }}</div>
                            <div class="shop-contact">
                                @if($businessSettings['business_phone'] ?? $businessInfo['phone'] ?? false)
                                    Mobile: {{ $businessSettings['business_phone'] ?? $businessInfo['phone'] }}
                                @endif
                            </div>
                        </div>

                        {{-- Custom Receipt Header --}}
                        @if($businessSettings['receipt_header'] ?? false)
                        <div class="receipt-custom-header">
                            {!! nl2br(e($businessSettings['receipt_header'])) !!}
                        </div>
                        @endif

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
                            @if($businessSettings['receipt_footer'] ?? false)
                                <div class="custom-footer">{!! nl2br(e($businessSettings['receipt_footer'])) !!}</div>
                            @else
                                <div class="thank-you">Thank you for your business!</div>
                                <div class="visit-again">Please visit again</div>
                            @endif
                            <div class="receipt-powered-by">Powered by Tech Scales Co. Ltd</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-2">
                    <button wire:click="closeReceiptModal" class="btn btn-sm btn-secondary">
                        <i class="ti ti-x me-1"></i> Close
                    </button>
                    <button onclick="printThermalReceipt()" class="btn btn-sm btn-primary">
                        <i class="ti ti-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Barcode Scanner Modal --}}
    @if($showScannerModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.9); z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow bg-dark">
                <div class="modal-header border-0 pb-2 bg-dark text-white">
                    <h5 class="modal-title"><i class="ti ti-scan me-2"></i> Scan Barcode</h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeScanner"></button>
                </div>
                <div class="modal-body p-2 bg-dark">
                    {{-- Camera preview --}}
                    <div class="scanner-container position-relative">
                        <video id="scanner-preview" class="w-100 rounded" style="max-height: 400px; background: #000;" autoplay playsinline></video>

                        {{-- Scanning overlay --}}
                        <div class="scanner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                            <div class="scanner-frame" style="width: 80%; height: 200px; border: 3px solid #0d6efd; border-radius: 10px; box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);"></div>
                        </div>

                        {{-- Loading indicator --}}
                        <div id="scanner-loading" class="position-absolute top-50 start-50 translate-middle text-center text-white">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <div>Starting camera...</div>
                        </div>

                        {{-- Status Messages --}}
                        <div id="scanner-status" class="position-absolute bottom-0 start-0 end-0 text-center py-2 bg-dark bg-opacity-75 text-white" style="display: none;"></div>
                    </div>

                    {{-- Instructions --}}
                    <div class="text-center text-white mt-3">
                        <i class="ti ti-info-circle me-1"></i>
                        <small>Position the barcode within the frame</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-dark">
                    <button wire:click="closeScanner" class="btn btn-light w-100">
                        <i class="ti ti-x me-1"></i> Close Scanner
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

        .receipt-logo {
            max-width: 120px;
            max-height: 60px;
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

        .receipt-custom-header {
            text-align: center;
            font-size: 10px;
            margin: 5px 0;
            font-style: italic;
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

        .receipt-footer .custom-footer {
            font-size: 11px;
        }

        .receipt-footer .visit-again {
            font-size: 10px;
        }

        .receipt-powered-by {
            margin-top: 8px;
            padding-top: 4px;
            border-top: 1px dashed #000;
            font-size: 9px;
            text-align: center;
            color: #555;
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
                    html, body {
                        width: 100%;
                        height: 100%;
                    }
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        font-size: 12px;
                        line-height: 1.4;
                        background: #fff;
                        display: flex;
                        justify-content: center;
                    }
                    .receipt-wrapper {
                        width: 80mm;
                        padding: 2mm;
                    }
                    .receipt-header { text-align: center; margin-bottom: 5px; }
                    .receipt-logo { max-width: 120px; max-height: 60px; margin-bottom: 5px; }
                    .receipt-header .shop-name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
                    .receipt-header .shop-address, .receipt-header .shop-contact { font-size: 11px; }
                    .receipt-custom-header { text-align: center; font-size: 10px; margin: 5px 0; font-style: italic; }
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
                    .receipt-footer .custom-footer { font-size: 11px; }
                    .receipt-powered-by { margin-top: 8px; padding-top: 4px; border-top: 1px dashed #000; font-size: 9px; text-align: center; color: #555; }
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
                <\/head>
                <body>
                    <div class="receipt-wrapper">
                        ${receiptContent.innerHTML}
                    </div>
                <\/body>
                <\/html>
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

    {{-- ZXing Barcode Scanner Library --}}
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>

    <script>
        let codeReader = null;
        let scannerStream = null;

        // Initialize scanner when Livewire is loaded
        document.addEventListener('livewire:initialized', () => {
            // Listen for start-scanner event
            Livewire.on('start-scanner', () => {
                startBarcodeScanner();
            });

            // Listen for stop-scanner event
            Livewire.on('stop-scanner', () => {
                stopBarcodeScanner();
            });
        });

        async function startBarcodeScanner() {
            const preview = document.getElementById('scanner-preview');
            const loading = document.getElementById('scanner-loading');
            const statusEl = document.getElementById('scanner-status');

            if (!preview) return;

            try {
                // Show loading
                if (loading) loading.style.display = 'block';
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-dark bg-opacity-75 text-info';
                    statusEl.innerHTML = '<i class="ti ti-camera me-1"></i> Initializing camera...';
                }

                // Initialize code reader
                codeReader = new ZXing.BrowserMultiFormatReader();

                // Get video devices
                const videoInputDevices = await codeReader.listVideoInputDevices();

                if (videoInputDevices.length === 0) {
                    throw new Error('No camera found');
                }

                // Prefer back camera on mobile
                let selectedDeviceId = videoInputDevices[0]?.deviceId;
                const backCamera = videoInputDevices.find(device =>
                    device.label.toLowerCase().includes('back') ||
                    device.label.toLowerCase().includes('rear') ||
                    device.label.toLowerCase().includes('environment')
                );
                if (backCamera) {
                    selectedDeviceId = backCamera.deviceId;
                }

                // Hide loading, show ready status
                if (loading) loading.style.display = 'none';
                if (statusEl) {
                    statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-dark bg-opacity-75 text-success';
                    statusEl.innerHTML = '<i class="ti ti-camera-check me-1"></i> Camera ready - Point at barcode';
                }

                // Start decoding
                codeReader.decodeFromVideoDevice(selectedDeviceId, 'scanner-preview', (result, err) => {
                    if (result) {
                        // Barcode detected
                        const code = result.getText();
                        console.log('Barcode scanned:', code);

                        // Show success message
                        if (statusEl) {
                            statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-success text-white';
                            statusEl.innerHTML = '<i class="ti ti-check me-1"></i> Barcode captured: ' + code;
                        }

                        // Vibration feedback if supported
                        if (navigator.vibrate) {
                            navigator.vibrate(200);
                        }

                        // Play beep sound
                        playBeep();

                        // Dispatch to Livewire after short delay to show success message
                        setTimeout(() => {
                            Livewire.dispatch('barcode-scanned', { code: code });
                        }, 800);
                    }

                    if (err && err.name !== 'NotFoundException') {
                        console.error('Scanner error:', err);
                    }
                });

            } catch (err) {
                console.error('Failed to start scanner:', err);
                if (loading) loading.style.display = 'none';
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-danger text-white';
                    statusEl.innerHTML = '<i class="ti ti-camera-off me-1"></i> Camera not available. Please grant camera permissions.';
                }

                // Show alert as well for critical error
                setTimeout(() => {
                    alert('Failed to access camera. Please grant camera permissions and try again.');
                    Livewire.dispatch('stop-scanner');
                }, 2000);
            }
        }

        function stopBarcodeScanner() {
            if (codeReader) {
                codeReader.reset();
                codeReader = null;
            }

            // Stop all video streams
            const preview = document.getElementById('scanner-preview');
            if (preview && preview.srcObject) {
                const tracks = preview.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                preview.srcObject = null;
            }
        }

        // Play beep sound on successful scan
        function playBeep() {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.3;

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            stopBarcodeScanner();
        });
    </script>
</div>
