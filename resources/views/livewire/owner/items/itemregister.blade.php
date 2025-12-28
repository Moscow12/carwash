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
            <h3 class="mb-1">Items / Services</h3>
            <p class="text-muted mb-0">Manage products and services for your carwashes</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Item
        </button>
    </div>

    {{-- Filters Card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search items...">
                    </div>
                </div>
                @if($ownerCarwashes->count() > 1)
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Carwash</label>
                    <select wire:model.live="filterCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($ownerCarwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Category</label>
                    <select wire:model.live="filterCategory" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($filterCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Type</label>
                    <select wire:model.live="filterType" class="form-select">
                        <option value="">All Types</option>
                        <option value="Service">Service</option>
                        <option value="product">Product</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Status</label>
                    <select wire:model.live="filterStatus" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <span class="badge bg-primary">{{ $items->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Grid --}}
    <div class="row g-3">
        @forelse($items as $item)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            @if($item->image)
                                <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}"
                                     class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="avatar avatar-md bg-{{ $item->type === 'Service' ? 'primary' : 'info' }}-subtle text-{{ $item->type === 'Service' ? 'primary' : 'info' }} rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="ti ti-{{ $item->type === 'Service' ? 'wash' : 'package' }} fs-4"></i>
                                </div>
                            @endif
                            <div>
                                <h6 class="mb-0">{{ $item->name }}</h6>
                                <span class="badge bg-{{ $item->type === 'Service' ? 'primary' : 'info' }}-subtle text-{{ $item->type === 'Service' ? 'primary' : 'info' }}">
                                    {{ $item->type }}
                                </span>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button wire:click="editItem('{{ $item->id }}')" class="dropdown-item">
                                        <i class="ti ti-edit me-2"></i> Edit
                                    </button>
                                </li>
                                <li>
                                    <button wire:click="toggleStatus('{{ $item->id }}')" class="dropdown-item">
                                        <i class="ti ti-{{ $item->status === 'active' ? 'eye-off' : 'eye' }} me-2"></i>
                                        {{ $item->status === 'active' ? 'Disable' : 'Enable' }}
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button wire:click="confirmDelete('{{ $item->id }}')" class="dropdown-item text-danger">
                                        <i class="ti ti-trash me-2"></i> Delete
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <p class="text-muted small mb-3">{{ Str::limit($item->description, 60) }}</p>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="text-muted small">Selling Price</span>
                            <h5 class="mb-0 text-success">TZS {{ number_format($item->selling_price, 0) }}</h5>
                        </div>
                        <div class="text-end">
                            <span class="text-muted small">Cost</span>
                            <div class="fw-medium">TZS {{ number_format($item->cost_price, 0) }}</div>
                        </div>
                    </div>

                    <hr class="my-2">

                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted">Category:</span>
                            <span class="fw-medium d-block">{{ $item->category->name ?? '-' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Unit:</span>
                            <span class="fw-medium d-block">{{ $item->unit->name ?? '-' }}</span>
                        </div>
                    </div>

                    @if($ownerCarwashes->count() > 1)
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <i class="ti ti-building-store me-1"></i>{{ $item->carwash->name ?? '-' }}
                        </small>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-transparent border-top py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge rounded-pill bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                            <i class="ti ti-{{ $item->status === 'active' ? 'check' : 'x' }} me-1"></i>
                            {{ ucfirst($item->status) }}
                        </span>
                        <div class="d-flex gap-2">
                            @if($item->require_plate_number === 'yes')
                                <span class="badge bg-warning-subtle text-warning" title="Requires Plate Number">
                                    <i class="ti ti-car"></i>
                                </span>
                            @endif
                            @if($item->product_stock === 'yes')
                                <span class="badge bg-info-subtle text-info" title="Track Stock">
                                    <i class="ti ti-package"></i>
                                </span>
                            @endif
                            @if($item->commission)
                                <span class="badge bg-purple-subtle text-purple" title="Commission: {{ $item->commission }}{{ $item->commission_type === 'percentage' ? '%' : ' TZS' }}">
                                    <i class="ti ti-percentage"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="ti ti-package display-4 text-muted opacity-50 d-block mb-3"></i>
                    <h5>No items found</h5>
                    <p class="text-muted mb-3">Get started by adding your first item or service</p>
                    <button wire:click="openModal" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i> Add Item
                    </button>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($items->hasPages())
    <div class="mt-4">
        {{ $items->links() }}
    </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeModal">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
            <div class="modal-content border-0 shadow" style="max-height: 90vh;">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Item' : 'Add New Item' }}
                    </h5>
                    <button type="button" wire:click="closeModal" class="btn-close"></button>
                </div>

                <form wire:submit="save">
                    <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 130px);">

                        {{-- Basic Information --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-info-circle me-1"></i> Basic Information
                        </h6>
                        <div class="row g-3 mb-4">
                            @if($ownerCarwashes->count() > 1)
                            <div class="col-md-6">
                                <label class="form-label">Carwash <span class="text-danger">*</span></label>
                                <select wire:model.live="carwash_id" class="form-select @error('carwash_id') is-invalid @enderror">
                                    <option value="">Select Carwash</option>
                                    @foreach($ownerCarwashes as $carwash)
                                        <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                                    @endforeach
                                </select>
                                @error('carwash_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror" {{ empty($availableCategories) ? 'disabled' : '' }}>
                                    <option value="">Select Category</option>
                                    @foreach($availableCategories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Full Car Wash, Interior Cleaning">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="Service">Service</option>
                                    <option value="product">Product</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Brief description of the item/service"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Pricing --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-currency-dollar me-1"></i> Pricing
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Cost Price (TZS) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="cost_price" class="form-control @error('cost_price') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Selling Price (TZS) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="selling_price" class="form-control @error('selling_price') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Market Price (TZS)</label>
                                <input type="number" wire:model="market_price" class="form-control @error('market_price') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('market_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Unit & Commission --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-settings me-1"></i> Settings
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                    <option value="">Select Unit</option>
                                    @foreach($availableUnits as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->symbol }})</option>
                                    @endforeach
                                </select>
                                @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commission</label>
                                <input type="number" wire:model="commission" class="form-control @error('commission') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commission Type</label>
                                <select wire:model="commission_type" class="form-select @error('commission_type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                                @error('commission_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Options --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-toggle-left me-1"></i> Options
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Track Stock?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="product_stock" value="yes" id="stockYes">
                                        <label class="form-check-label" for="stockYes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="product_stock" value="no" id="stockNo">
                                        <label class="form-check-label" for="stockNo">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Require Plate Number?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="require_plate_number" value="yes" id="plateYes">
                                        <label class="form-check-label" for="plateYes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="require_plate_number" value="no" id="plateNo">
                                        <label class="form-check-label" for="plateNo">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="status" value="active" id="statusActive">
                                        <label class="form-check-label" for="statusActive">
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="status" value="inactive" id="statusInactive">
                                        <label class="form-check-label" for="statusInactive">
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Image --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-photo me-1"></i> Image
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Item Image</label>
                                <input type="file" wire:model="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror

                                <div class="mt-2 d-flex gap-3">
                                    @if($image)
                                        <div>
                                            <small class="text-muted d-block mb-1">New Image Preview:</small>
                                            <img src="{{ $image->temporaryUrl() }}" class="rounded" style="max-height: 80px;">
                                        </div>
                                    @endif
                                    @if($existingImage && !$image)
                                        <div>
                                            <small class="text-muted d-block mb-1">Current Image:</small>
                                            <img src="{{ Storage::url($existingImage) }}" class="rounded" style="max-height: 80px;">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-light">
                        <button type="button" wire:click="closeModal" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save">
                                <i class="ti ti-{{ $editMode ? 'check' : 'plus' }} me-1"></i>
                                {{ $editMode ? 'Update Item' : 'Create Item' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeDeleteModal">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center py-4">
                    <div class="avatar avatar-lg bg-danger-subtle text-danger rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="ti ti-trash fs-2"></i>
                    </div>
                    <h5 class="mb-2">Delete Item?</h5>
                    <p class="text-muted mb-0">Are you sure you want to delete this item? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top justify-content-center gap-2">
                    <button type="button" wire:click="closeDeleteModal" class="btn btn-outline-secondary">
                        <i class="ti ti-x me-1"></i> Cancel
                    </button>
                    <button type="button" wire:click="deleteItem" class="btn btn-danger">
                        <span wire:loading.remove wire:target="deleteItem">
                            <i class="ti ti-trash me-1"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteItem">
                            <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
