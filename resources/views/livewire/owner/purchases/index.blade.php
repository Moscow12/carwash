<div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Purchases</h3>
            <p class="text-muted mb-0">Manage purchase orders from suppliers</p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Purchase
        </button>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        {{-- Purchase Status Cards --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <h6 class="mb-0 text-muted"><i class="ti ti-package me-1"></i> Purchase Status</h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-shopping-cart fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Total</div>
                                    <div class="h5 mb-0">{{ number_format($stats['total']) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-check fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Received</div>
                                    <div class="h5 mb-0">{{ number_format($stats['received']) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-clock fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Pending</div>
                                    <div class="h5 mb-0">{{ number_format($stats['pending']) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-danger-subtle text-danger rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-x fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Canceled</div>
                                    <div class="h5 mb-0">{{ number_format($stats['canceled']) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment Status Cards --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-2">
                    <h6 class="mb-0 text-muted"><i class="ti ti-cash me-1"></i> Payment Status</h6>
                </div>
                <div class="card-body py-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-circle-check fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Paid ({{ number_format($stats['paid']) }})</div>
                                    <div class="h5 mb-0 text-success">TZS {{ number_format($stats['paid_value'], 0) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-danger-subtle text-danger rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-circle-x fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Unpaid ({{ number_format($stats['unpaid']) }})</div>
                                    <div class="h5 mb-0 text-danger">TZS {{ number_format($stats['unpaid_value'], 0) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                    <i class="ti ti-currency-dollar fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-muted small">Total Value (Received)</div>
                                    <div class="h5 mb-0">TZS {{ number_format($stats['total_value'], 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search item or supplier...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($ownerCarwashes as $carwash)
                            <option value="{{ $carwash['id'] }}">{{ $carwash['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="purchaseStatusFilter" class="form-select">
                        <option value="">All Purchase Status</option>
                        <option value="received">Received</option>
                        <option value="pending">Pending</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="paymentStatusFilter" class="form-select">
                        <option value="">All Payment Status</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="pending">Pending</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <span class="text-muted small">{{ $purchases->total() }} purchase(s)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Purchases Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Date</th>
                        <th class="border-0">Item</th>
                        <th class="border-0">Supplier</th>
                        <th class="border-0 text-end">Qty</th>
                        <th class="border-0 text-end">Price</th>
                        <th class="border-0 text-end">Total</th>
                        <th class="border-0 text-center">Purchase</th>
                        <th class="border-0 text-center">Payment</th>
                        <th class="border-0 text-end" style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $purchase->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $purchase->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-info-subtle text-info rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="ti ti-package"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $purchase->item->name ?? '-' }}</div>
                                    @if($purchase->notes)
                                        <small class="text-muted">{{ Str::limit($purchase->notes, 25) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-secondary-subtle text-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="ti ti-building-store fs-6"></i>
                                </div>
                                <span>{{ $purchase->supplier->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="text-end fw-medium">{{ number_format($purchase->quantity, 0) }}</td>
                        <td class="text-end">TZS {{ number_format($purchase->price, 0) }}</td>
                        <td class="text-end">
                            <div class="fw-medium">TZS {{ number_format($purchase->total, 0) }}</div>
                            @if($purchase->discount > 0)
                                <small class="text-success">-{{ number_format($purchase->discount, 0) }} disc</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="badge bg-{{ $purchase->purchase_status_badge_class }}-subtle text-{{ $purchase->purchase_status_badge_class }} border-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    {{ $purchase->purchase_status_label }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePurchaseStatus('{{ $purchase->id }}', 'received')">
                                        <i class="ti ti-check text-success me-2"></i> Received
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePurchaseStatus('{{ $purchase->id }}', 'pending')">
                                        <i class="ti ti-clock text-warning me-2"></i> Pending
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePurchaseStatus('{{ $purchase->id }}', 'canceled')">
                                        <i class="ti ti-x text-danger me-2"></i> Canceled
                                    </a></li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="badge bg-{{ $purchase->payment_status_badge_class }}-subtle text-{{ $purchase->payment_status_badge_class }} border-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    {{ $purchase->payment_status_label }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePaymentStatus('{{ $purchase->id }}', 'paid')">
                                        <i class="ti ti-circle-check text-success me-2"></i> Paid
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePaymentStatus('{{ $purchase->id }}', 'unpaid')">
                                        <i class="ti ti-circle-x text-danger me-2"></i> Unpaid
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePaymentStatus('{{ $purchase->id }}', 'pending')">
                                        <i class="ti ti-clock text-warning me-2"></i> Pending
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updatePaymentStatus('{{ $purchase->id }}', 'refunded')">
                                        <i class="ti ti-arrow-back text-info me-2"></i> Refunded
                                    </a></li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="openEditModal('{{ $purchase->id }}')" class="btn btn-outline-primary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <button wire:click="deletePurchase('{{ $purchase->id }}')" wire:confirm="Are you sure you want to delete this purchase?" class="btn btn-outline-danger" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-shopping-cart fs-1"></i>
                            </div>
                            <h6 class="text-muted">No purchases found</h6>
                            <p class="text-muted small mb-3">Start by creating your first purchase order</p>
                            <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i> New Purchase
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($purchases->hasPages())
            <div class="card-footer bg-transparent">
                {{ $purchases->links() }}
            </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 700px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Purchase' : 'New Purchase' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <form wire:submit="savePurchase">
                        <div class="row g-3">
                            {{-- Item --}}
                            <div class="col-md-6">
                                <label class="form-label">Product <span class="text-danger">*</span></label>
                                <select wire:model="item_id" class="form-select @error('item_id') is-invalid @enderror">
                                    <option value="">Select Product</option>
                                    @foreach($availableItems as $item)
                                        <option value="{{ $item['id'] }}">
                                            {{ $item['name'] }} (Stock: {{ $this->getItemBalance($item['id']) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if(count($availableItems) === 0)
                                    <small class="text-warning">
                                        <i class="ti ti-alert-triangle me-1"></i>
                                        No products found. Only items with type "product" can be purchased.
                                    </small>
                                @endif
                            </div>

                            {{-- Supplier --}}
                            <div class="col-md-6">
                                <label class="form-label">Supplier <span class="text-danger">*</span></label>
                                <select wire:model="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                    <option value="">Select Supplier</option>
                                    @foreach($availableSuppliers as $supplier)
                                        <option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Quantity --}}
                            <div class="col-md-4">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror" placeholder="0" min="0.01" step="0.01">
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Price --}}
                            <div class="col-md-4">
                                <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" wire:model="price" class="form-control @error('price') is-invalid @enderror" placeholder="0" min="0" step="1">
                                </div>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Discount --}}
                            <div class="col-md-4">
                                <label class="form-label">Discount</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" wire:model="discount" class="form-control" placeholder="0" min="0" step="1">
                                </div>
                            </div>

                            {{-- Purchase Status --}}
                            <div class="col-md-6">
                                <label class="form-label">Purchase Status <span class="text-danger">*</span></label>
                                <select wire:model="purchase_status" class="form-select @error('purchase_status') is-invalid @enderror">
                                    <option value="pending">Pending</option>
                                    <option value="received">Received</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                @error('purchase_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">
                                    <i class="ti ti-info-circle me-1"></i>
                                    "Received" updates stock balance
                                </small>
                            </div>

                            {{-- Payment Status --}}
                            <div class="col-md-6">
                                <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                                <select wire:model="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="refunded">Refunded</option>
                                    <option value="canceled">Canceled</option>
                                </select>
                                @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill">
                                <span wire:loading.remove wire:target="savePurchase">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update' : 'Save' }}
                                </span>
                                <span wire:loading wire:target="savePurchase">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
