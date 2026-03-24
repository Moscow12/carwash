<div class="container-fluid px-6 py-4">
    <!-- Header -->
    <div class="row">
        <div class="col-lg-12 col-md-12 col-12">
            <div class="border-bottom pb-4 mb-4 d-flex align-items-center justify-content-between">
                <div class="mb-2 mb-lg-0">
                    <h1 class="mb-1 h2 fw-bold">Purchase Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('owner.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Purchases</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-primary" wire:click="openCreateModal">
                        <i class="ti ti-plus me-2"></i>New Purchase
                    </button>
                </div>
            </div>
        </div>
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

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-3">
            <label class="form-label">Business</label>
            <select wire:model.live="selectedBusiness" class="form-select">
                <option value="">Select Business</option>
                @foreach($businesses as $business)
                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Outlet</label>
            <select wire:model.live="selectedOutlet" class="form-select">
                <option value="">Select Outlet</option>
                @foreach($outlets as $outlet)
                    <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Status</label>
            <select wire:model.live="statusFilter" class="form-select">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="received">Received</option>
                <option value="canceled">Canceled</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Payment Status</label>
            <select wire:model.live="paymentStatusFilter" class="form-select">
                <option value="all">All Payments</option>
                <option value="unpaid">Unpaid</option>
                <option value="partial">Partial</option>
                <option value="paid">Paid</option>
            </select>
        </div>
    </div>

    <!-- Search -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-search"></i></span>
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by reference or supplier...">
            </div>
        </div>
    </div>

    <!-- Purchase List -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reference No</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>
                                    <span class="fw-semibold">{{ $purchase->reference_no }}</span>
                                </td>
                                <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                <td>{{ $purchase->received_date?->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $purchase->purchaseItems->count() }} items</span>
                                </td>
                                <td class="fw-semibold">TSh {{ number_format($purchase->total_amount, 2) }}</td>
                                <td class="text-success">TSh {{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="text-danger">TSh {{ number_format($purchase->balance, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $purchase->purchase_status_badge_class }}">
                                        {{ $purchase->purchase_status_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $purchase->payment_status_badge_class }}">
                                        {{ $purchase->payment_status_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-info" wire:click="viewPurchase({{ $purchase->id }})" title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        @if($purchase->balance > 0)
                                            <button type="button" class="btn btn-success" wire:click="openPaymentModal({{ $purchase->id }})" title="Record Payment">
                                                <i class="ti ti-cash"></i>
                                            </button>
                                        @endif
                                        @if($purchase->purchase_status !== 'received')
                                            <button type="button" class="btn btn-primary" wire:click="updatePurchaseStatus({{ $purchase->id }}, 'received')" title="Mark as Received">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="ti ti-inbox fs-1 text-muted"></i>
                                    <p class="text-muted">No purchases found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchases->hasPages())
                <div class="mt-3">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Create Purchase Modal -->
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Purchase</h5>
                        <button type="button" class="btn-close" wire:click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select wire:model="supplierId" class="form-select @error('supplierId') is-invalid @enderror">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplierId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reference No</label>
                                <input type="text" wire:model="referenceNo" class="form-control" placeholder="Auto-generated if empty">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Received Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="receivedDate" class="form-control @error('receivedDate') is-invalid @enderror">
                                @error('receivedDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Purchase Status</label>
                                <select wire:model="purchaseStatus" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="received">Received</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Purchase Items</h6>

                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Item</th>
                                        <th style="width: 10%;">Unit</th>
                                        <th style="width: 10%;">Qty</th>
                                        <th style="width: 12%;">Unit Cost</th>
                                        <th style="width: 10%;">Tax</th>
                                        <th style="width: 12%;">Discount</th>
                                        <th style="width: 13%;">Subtotal</th>
                                        <th style="width: 8%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseItems as $index => $item)
                                        <tr>
                                            <td>
                                                <div class="position-relative" style="min-width: 200px;">
                                                    <input
                                                        type="text"
                                                        wire:model.live.debounce.300ms="itemSearchTerms.{{ $index }}"
                                                        wire:focus="focusItemSearch({{ $index }})"
                                                        class="form-control form-control-sm @error('purchaseItems.'.$index.'.item_id') is-invalid @enderror"
                                                        placeholder="Type to search items..."
                                                        autocomplete="off"
                                                    >

                                                    @if(!empty($itemSearchTerms[$index]) && isset($searchedItems[$index]) && $searchedItems[$index]->count() > 0)
                                                    <div class="position-absolute w-100 bg-white border rounded shadow-sm" style="z-index: 1000; max-height: 200px; overflow-y: auto; top: 100%; left: 0;">
                                                        @foreach($searchedItems[$index] as $stockItem)
                                                        <div
                                                            wire:click="selectItem({{ $index }}, '{{ $stockItem->id }}')"
                                                            class="px-2 py-2 cursor-pointer hover-bg-light"
                                                            style="cursor: pointer; font-size: 0.875rem; border-bottom: 1px solid #f0f0f0;"
                                                            onmouseover="this.style.backgroundColor='#f8f9fa'"
                                                            onmouseout="this.style.backgroundColor='white'"
                                                        >
                                                            <div class="fw-medium">{{ $stockItem->name }}</div>
                                                            @if($stockItem->code)
                                                            <small class="text-muted">Code: {{ $stockItem->code }}</small>
                                                            @endif
                                                            @if($stockItem->selling_price)
                                                            <small class="text-success ms-2">Price: {{ number_format($stockItem->selling_price, 2) }}</small>
                                                            @endif
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                    @endif

                                                    @if(!empty($itemSearchTerms[$index]) && isset($searchedItems[$index]) && $searchedItems[$index]->count() === 0)
                                                    <div class="position-absolute w-100 bg-white border rounded shadow-sm px-2 py-2" style="z-index: 1000; top: 100%; left: 0;">
                                                        <small class="text-muted">No items found</small>
                                                    </div>
                                                    @endif

                                                    @if(!empty($item['item_name']))
                                                    <input type="hidden" wire:model="purchaseItems.{{ $index }}.item_id">
                                                    <small class="text-success d-block mt-1">
                                                        <i class="ti ti-check-circle"></i> {{ $item['item_name'] }}
                                                    </small>
                                                    @endif
                                                </div>
                                                @error('purchaseItems.'.$index.'.item_id') <small class="text-danger d-block">{{ $message }}</small> @enderror
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
                                                <input type="number" wire:model="purchaseItems.{{ $index }}.quantity" wire:change="calculateItemSubtotal({{ $index }})" class="form-control form-control-sm @error('purchaseItems.'.$index.'.quantity') is-invalid @enderror" step="0.001" min="0">
                                                @error('purchaseItems.'.$index.'.quantity') <small class="text-danger">Required</small> @enderror
                                            </td>
                                            <td>
                                                <input type="number" wire:model="purchaseItems.{{ $index }}.unit_cost" wire:change="calculateItemSubtotal({{ $index }})" class="form-control form-control-sm @error('purchaseItems.'.$index.'.unit_cost') is-invalid @enderror" step="0.01" min="0">
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
                                                <input type="number" wire:model="purchaseItems.{{ $index }}.discount" wire:change="calculateItemSubtotal({{ $index }})" class="form-control form-control-sm" step="0.01" min="0">
                                            </td>
                                            <td>
                                                <input type="text" value="{{ number_format($item['subtotal'] ?? 0, 2) }}" class="form-control form-control-sm" readonly>
                                            </td>
                                            <td>
                                                <button type="button" wire:click="removePurchaseItemRow({{ $index }})" class="btn btn-sm btn-danger" {{ count($purchaseItems) <= 1 ? 'disabled' : '' }}>
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="8">
                                            <button type="button" wire:click="addPurchaseItemRow" class="btn btn-sm btn-outline-primary">
                                                <i class="ti ti-plus me-1"></i>Add Item
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Total Amount:</td>
                                        <td colspan="2" class="fw-bold">TSh {{ number_format($this->totalAmount, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCreateModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createPurchase">Create Purchase</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- View Purchase Modal -->
    @if($showViewModal && $viewingPurchase)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Purchase Details - {{ $viewingPurchase->reference_no }}</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Purchase Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p><strong>Supplier:</strong> {{ $viewingPurchase->supplier->name ?? 'N/A' }}</p>
                                <p><strong>Received Date:</strong> {{ $viewingPurchase->received_date?->format('M d, Y') }}</p>
                                <p><strong>Status:</strong>
                                    <span class="badge bg-{{ $viewingPurchase->purchase_status_badge_class }}">
                                        {{ $viewingPurchase->purchase_status_label }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Amount:</strong> <span class="fw-bold">TSh {{ number_format($viewingPurchase->total_amount, 2) }}</span></p>
                                <p><strong>Paid Amount:</strong> <span class="text-success">TSh {{ number_format($viewingPurchase->paid_amount, 2) }}</span></p>
                                <p><strong>Balance:</strong> <span class="text-danger fw-bold">TSh {{ number_format($viewingPurchase->balance, 2) }}</span></p>
                                <p><strong>Payment Status:</strong>
                                    <span class="badge bg-{{ $viewingPurchase->payment_status_badge_class }}">
                                        {{ $viewingPurchase->payment_status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        @if($viewingPurchase->notes)
                            <div class="alert alert-info">
                                <strong>Notes:</strong> {{ $viewingPurchase->notes }}
                            </div>
                        @endif

                        <!-- Purchase Items -->
                        <h6 class="mb-3">Purchase Items</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th>Tax</th>
                                        <th>Discount</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewingPurchase->purchaseItems as $item)
                                        <tr>
                                            <td>{{ $item->item->name ?? 'N/A' }}</td>
                                            <td>{{ $item->unit->name ?? '-' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>TSh {{ number_format($item->unit_cost, 2) }}</td>
                                            <td>TSh {{ number_format($item->tax_amount, 2) }}</td>
                                            <td>TSh {{ number_format($item->discount, 2) }}</td>
                                            <td class="fw-semibold">TSh {{ number_format($item->subtotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Payment History -->
                        <h6 class="mb-3">Payment History</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Recorded By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($viewingPurchase->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date?->format('M d, Y') }}</td>
                                            <td class="fw-semibold">TSh {{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->paymentMethod->name ?? 'N/A' }}</td>
                                            <td>{{ $payment->reference_no ?: '-' }}</td>
                                            <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                            <td>{{ $payment->notes ?: '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No payments recorded yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                        @if($viewingPurchase->balance > 0)
                            <button type="button" class="btn btn-success" wire:click="openPaymentModal({{ $viewingPurchase->id }})">
                                <i class="ti ti-cash me-2"></i>Record Payment
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Payment Modal -->
    @if($showPaymentModal && $viewingPurchase)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Payment - {{ $viewingPurchase->reference_no }}</h5>
                        <button type="button" class="btn-close" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Remaining Balance:</strong> TSh {{ number_format($viewingPurchase->remaining_balance, 2) }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" wire:model="paymentAmount" class="form-control @error('paymentAmount') is-invalid @enderror" step="0.01" min="0" max="{{ $viewingPurchase->remaining_balance }}">
                            @error('paymentAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select wire:model="paymentMethodId" class="form-select @error('paymentMethodId') is-invalid @enderror">
                                <option value="">Select Payment Method</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endforeach
                            </select>
                            @error('paymentMethodId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" wire:model="paymentDate" class="form-control @error('paymentDate') is-invalid @enderror">
                            @error('paymentDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reference No (Check #, Transaction ID)</label>
                            <input type="text" wire:model="paymentReference" class="form-control" placeholder="Optional">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="paymentNotes" class="form-control" rows="2" placeholder="Optional payment notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="recordPayment">Record Payment</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
