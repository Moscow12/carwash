<div class="container-fluid px-6 py-4" x-data="{ showAlert: false, alertType: '', alertMessage: '' }"
     @alert.window="showAlert = true; alertType = $event.detail[0].type; alertMessage = $event.detail[0].message; setTimeout(() => showAlert = false, 5000)">
    <!-- Header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-4 mb-4 d-flex align-items-center justify-content-between">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-1 h2 fw-bold">Create Purchase Order</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('owner.inventory.purchases') }}">Purchases</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Alpine Alert -->
    <div x-show="showAlert" x-transition class="alert alert-dismissible fade show mb-3"
         :class="{
             'alert-success': alertType === 'success',
             'alert-warning': alertType === 'warning',
             'alert-danger': alertType === 'error',
             'alert-info': alertType === 'info'
         }"
         style="display: none;">
        <span x-text="alertMessage"></span>
        <button type="button" class="btn-close" @click="showAlert = false"></button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            {{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form wire:submit.prevent="createPurchase">
        <!-- Purchase Details Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Purchase Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">Business <span class="text-danger">*</span></label>
                        <select wire:model.live="selectedBusiness" class="form-select" required>
                            <option value="">Select Business</option>
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Outlet <span class="text-danger">*</span></label>
                        <select wire:model.live="selectedOutlet" class="form-select" required>
                            <option value="">Select Outlet</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select wire:model="supplierId" class="form-select @error('supplierId') is-invalid @enderror" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplierId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select wire:model="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="submitted">Submitted</option>
                            <option value="approved">Approved</option>
                            <option value="partially_received">Partially Received</option>
                            <option value="received">Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label class="form-label">PO Number</label>
                        <input type="text" wire:model="poNumber" class="form-control" placeholder="Auto-generated if empty">
                        <small class="text-muted">Leave empty for auto-generation</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order Date <span class="text-danger">*</span></label>
                        <input type="date" wire:model="orderDate" class="form-control @error('orderDate') is-invalid @enderror" required>
                        @error('orderDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Expected Date</label>
                        <input type="date" wire:model="expectedDate" class="form-control @error('expectedDate') is-invalid @enderror">
                        @error('expectedDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" class="form-control" rows="1" placeholder="Optional notes"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Purchase Items Card -->
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-3">Purchase Items</h5>

                <!-- Item Search Section -->
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Search & Add Items</label>
                        <div class="position-relative" x-data>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-search"></i></span>
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="itemSearch"
                                    wire:focus="focusItemSearch"
                                    @click.away="$wire.hideItemSearchDropdown()"
                                    class="form-control"
                                    placeholder="Search by item name or barcode..."
                                    autocomplete="off"
                                >
                            </div>

                            <!-- Loading Indicator -->
                            <div wire:loading wire:target="itemSearch" class="position-absolute end-0 top-50 translate-middle-y me-5">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <!-- Search Results Dropdown -->
                            @if(!empty($itemSearch) && $showItemSearchDropdown)
                                @if($this->searchedItems->count() > 0)
                                <div class="position-absolute w-100 bg-white border rounded shadow-lg"
                                     style="z-index: 1050; max-height: 300px; overflow-y: auto; top: 100%; left: 0; margin-top: 2px;">
                                    @foreach($this->searchedItems as $stockItem)
                                    <div
                                        wire:click="addItemToList('{{ $stockItem->id }}')"
                                        wire:loading.class="opacity-50"
                                        wire:loading.attr="disabled"
                                        wire:target="addItemToList"
                                        class="dropdown-item-custom px-3 py-2"
                                        style="cursor: pointer; border-bottom: 1px solid #e9ecef; transition: background-color 0.15s;"
                                        onmouseover="this.style.backgroundColor='#f8f9fa'"
                                        onmouseout="this.style.backgroundColor='white'"
                                    >
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1 me-3">
                                                <div class="fw-semibold text-dark">
                                                    {{ $stockItem->name }}
                                                </div>
                                                @if($stockItem->barcode)
                                                    <small class="text-muted">
                                                        <i class="ti ti-barcode" style="font-size: 0.7rem;"></i> {{ $stockItem->barcode }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="text-end" style="min-width: 100px;">
                                                @if($stockItem->cost_price)
                                                    <small class="text-success d-block fw-semibold">
                                                        Cost: TSh {{ number_format($stockItem->cost_price, 2) }}
                                                    </small>
                                                @endif
                                                @if($stockItem->selling_price)
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">
                                                        Price: TSh {{ number_format($stockItem->selling_price, 2) }}
                                                    </small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                <div class="position-absolute w-100 bg-white border rounded shadow-sm px-3 py-2"
                                     style="z-index: 1050; top: 100%; left: 0; margin-top: 2px;">
                                    <small class="text-muted">
                                        <i class="ti ti-info-circle me-1"></i>No items found for "{{ $itemSearch }}"
                                    </small>
                                </div>
                                @endif
                            @endif
                        </div>
                        <small class="text-muted">Click on an item to add it to the purchase order</small>

                        <!-- Loading indicator when adding item -->
                        <div wire:loading wire:target="addItemToList" class="mt-2">
                            <div class="alert alert-info d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2"></div>
                                <span>Adding item...</span>
                            </div>
                        </div>

                        <!-- Debug Info -->
                        <div class="mt-2">
                            <small class="text-muted">
                                Debug: {{ count($purchaseItems) }} item(s) in list | Business: {{ $selectedBusiness ? 'Selected' : 'Not selected' }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 30%;">Item</th>
                                <th style="width: 12%;">Unit</th>
                                <th style="width: 12%;">Quantity <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Unit Cost <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Tax</th>
                                <th style="width: 12%;">Subtotal</th>
                                <th style="width: 10%;"></th>
                            </tr>
                        </thead>
                        <tbody id="purchase-items-body">
                            @forelse($purchaseItems as $index => $item)
                                <tr wire:key="item-{{ $index }}">
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $item['item_name'] }}</div>
                                        <input type="hidden" wire:model="purchaseItems.{{ $index }}.item_id">
                                        @error('purchaseItems.'.$index.'.item_id')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </td>
                                    <td>
                                        <select wire:model="purchaseItems.{{ $index }}.unit_id" class="form-select form-select-sm">
                                            <option value="">Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" wire:model="purchaseItems.{{ $index }}.quantity_ordered" wire:change="calculateItemSubtotal({{ $index }})" class="form-control form-control-sm @error('purchaseItems.'.$index.'.quantity_ordered') is-invalid @enderror" step="0.01" min="0" required>
                                        @error('purchaseItems.'.$index.'.quantity_ordered') <small class="text-danger">Required</small> @enderror
                                    </td>
                                    <td>
                                        <input type="number" wire:model="purchaseItems.{{ $index }}.unit_cost" wire:change="calculateItemSubtotal({{ $index }})" class="form-control form-control-sm @error('purchaseItems.'.$index.'.unit_cost') is-invalid @enderror" step="0.01" min="0" required>
                                        @error('purchaseItems.'.$index.'.unit_cost') <small class="text-danger">Required</small> @enderror
                                    </td>
                                    <td>
                                        <select wire:model="purchaseItems.{{ $index }}.tax_rate_id" wire:change="calculateItemSubtotal({{ $index }})" class="form-select form-select-sm">
                                            <option value="">No Tax</option>
                                            @foreach($taxRates as $taxRate)
                                                <option value="{{ $taxRate->id }}">{{ $taxRate->name }} ({{ $taxRate->rate }}%)</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" value="{{ number_format($item['subtotal'] ?? 0, 2) }}" class="form-control form-control-sm fw-semibold" readonly>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="removePurchaseItemRow({{ $index }})" class="btn btn-sm btn-danger" title="Remove Item">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="ti ti-package fs-1 text-muted"></i>
                                        <p class="text-muted mt-2 mb-0">No items added yet</p>
                                        <small class="text-muted">Use the search box above to add items</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="row">
            <div class="col-md-8"></div>

            <div class="col-md-4">
                <!-- Total Summary Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Order Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-light mb-3">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-shopping-cart me-2 fs-4"></i>
                                <div>
                                    <strong>{{ count($purchaseItems) }}</strong> {{ count($purchaseItems) === 1 ? 'item' : 'items' }} added
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span class="fw-semibold">TSh {{ number_format($this->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span class="text-info">+ TSh {{ number_format($this->totalTax, 2) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total Amount:</strong>
                            <strong class="text-primary fs-5">TSh {{ number_format($this->totalAmount, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" wire:click="cancel" class="btn btn-secondary">
                        <i class="ti ti-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="createPurchase">
                            <i class="ti ti-check me-1"></i>Create Purchase Order
                        </span>
                        <span wire:loading wire:target="createPurchase">
                            <span class="spinner-border spinner-border-sm me-1"></span>
                            Creating...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    <style>
        /* Custom dropdown item styles */
        .dropdown-item-custom {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        .dropdown-item-custom:last-child {
            border-bottom: none !important;
        }

        .dropdown-item-custom:active {
            background-color: #e9ecef !important;
        }

        /* Smooth transitions */
        .form-control, .form-select {
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        /* Custom scrollbar for dropdown */
        .position-absolute.bg-white::-webkit-scrollbar {
            width: 6px;
        }

        .position-absolute.bg-white::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .position-absolute.bg-white::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .position-absolute.bg-white::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Table improvements */
        .table-bordered th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</div>
