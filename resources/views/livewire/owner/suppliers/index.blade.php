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
            <h3 class="mb-1">Suppliers</h3>
            <p class="text-muted mb-0">Manage your product suppliers</p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Supplier
        </button>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-truck fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Suppliers</div>
                            <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-circle-check fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Active</div>
                            <div class="h4 mb-0">{{ number_format($stats['active']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-secondary-subtle text-secondary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-circle-x fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Inactive</div>
                            <div class="h4 mb-0">{{ number_format($stats['inactive']) }}</div>
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
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search by name, phone or email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 text-end">
                    <span class="text-muted small">{{ $suppliers->total() }} supplier(s)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Suppliers Grid --}}
    <div class="row g-3">
        @forelse($suppliers as $supplier)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="ti ti-building-store fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $supplier->name }}</h6>
                                <small class="text-muted">{{ $supplier->purchases_count }} purchase(s)</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $supplier->id }}')">
                                        <i class="ti ti-eye me-2"></i> View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $supplier->id }}')">
                                        <i class="ti ti-edit me-2"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="#" wire:click.prevent="toggleStatus('{{ $supplier->id }}')">
                                        <i class="ti ti-{{ $supplier->status === 'active' ? 'circle-x' : 'circle-check' }} me-2"></i>
                                        {{ $supplier->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteSupplier('{{ $supplier->id }}')" wire:confirm="Are you sure you want to delete this supplier?">
                                        <i class="ti ti-trash me-2"></i> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-phone text-muted me-2"></i>
                            <span>{{ $supplier->phone }}</span>
                        </div>
                        @if($supplier->email)
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-mail text-muted me-2"></i>
                            <span class="text-truncate">{{ $supplier->email }}</span>
                        </div>
                        @endif
                        @if($supplier->address)
                        <div class="d-flex align-items-start">
                            <i class="ti ti-map-pin text-muted me-2 mt-1"></i>
                            <span class="text-muted small">{{ Str::limit($supplier->address, 50) }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="badge bg-{{ $supplier->status_badge_class }}-subtle text-{{ $supplier->status_badge_class }}">
                            {{ ucfirst($supplier->status) }}
                        </span>
                        <small class="text-muted">Added {{ $supplier->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="ti ti-truck fs-1"></i>
                    </div>
                    <h6 class="text-muted">No suppliers found</h6>
                    <p class="text-muted small mb-3">Start by adding your first supplier</p>
                    <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i> Add Supplier
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($suppliers->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $suppliers->links() }}
    </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Supplier' : 'Add Supplier' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="saveSupplier">
                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter supplier name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Phone --}}
                        <div class="mb-3">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="e.g., +255123456789">
                            </div>
                            @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="supplier@example.com">
                            </div>
                            @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        {{-- Address --}}
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" rows="2" placeholder="Enter supplier address"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2 pt-3">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill">
                                <span wire:loading.remove wire:target="saveSupplier">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update' : 'Save' }}
                                </span>
                                <span wire:loading wire:target="saveSupplier">
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

    {{-- View Details Modal --}}
    @if($showViewModal && $viewSupplier)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-building-store me-2"></i> Supplier Details
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                            <i class="ti ti-building-store fs-1"></i>
                        </div>
                        <h5 class="mb-1">{{ $viewSupplier->name }}</h5>
                        <span class="badge bg-{{ $viewSupplier->status_badge_class }}-subtle text-{{ $viewSupplier->status_badge_class }}">
                            {{ ucfirst($viewSupplier->status) }}
                        </span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="ti ti-phone me-2"></i> Phone</span>
                            <span class="fw-medium">{{ $viewSupplier->phone }}</span>
                        </div>
                        @if($viewSupplier->email)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="ti ti-mail me-2"></i> Email</span>
                            <span class="fw-medium">{{ $viewSupplier->email }}</span>
                        </div>
                        @endif
                        @if($viewSupplier->address)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-map-pin me-2"></i> Address</span>
                            <span>{{ $viewSupplier->address }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="ti ti-shopping-cart me-2"></i> Total Purchases</span>
                            <span class="fw-medium">{{ $viewSupplier->purchases_count }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="ti ti-currency-dollar me-2"></i> Total Value</span>
                            <span class="fw-medium">TZS {{ number_format($viewSupplier->total_purchase_value, 0) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="ti ti-alert-circle me-2"></i> Unpaid Balance</span>
                            <span class="fw-medium text-danger">TZS {{ number_format($viewSupplier->unpaid_balance, 0) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i> Added On</span>
                            <span class="fw-medium">{{ $viewSupplier->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        <button wire:click="openEditModal('{{ $viewSupplier->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
