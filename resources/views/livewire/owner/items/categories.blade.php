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
            <h3 class="mb-1">Categories</h3>
            <p class="text-muted mb-0">Manage service categories for your carwashes</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Category
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <div class="row align-items-center g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search categories...">
                    </div>
                </div>
                @if($ownerCarwashes->count() > 1)
                <div class="col-md-4">
                    <select wire:model.live="filterCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($ownerCarwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-4 text-end">
                    <span class="text-muted">{{ $categories->total() }} category(ies) found</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 ps-4">Category</th>
                            <th class="border-0">Carwash</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-md bg-primary-subtle text-primary rounded me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="ti ti-category fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $category->name }}</h6>
                                        @if($category->description)
                                            <small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                        @else
                                            <small class="text-muted fst-italic">No description</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-secondary-subtle text-secondary rounded me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                        <i class="ti ti-car-wash"></i>
                                    </div>
                                    <span>{{ $category->carwash->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-{{ $category->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $category->status === 'active' ? 'success' : 'secondary' }} px-3 py-2">
                                    <i class="ti ti-{{ $category->status === 'active' ? 'check' : 'x' }} me-1"></i>
                                    {{ ucfirst($category->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button wire:click="editCategory('{{ $category->id }}')"
                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete('{{ $category->id }}')"
                                            class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-category display-4 d-block mb-3 opacity-50"></i>
                                    <h5>No categories found</h5>
                                    <p class="mb-3">Get started by adding your first category</p>
                                    <button wire:click="openModal" class="btn btn-primary btn-sm">
                                        <i class="ti ti-plus me-1"></i> Add Category
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($categories->hasPages())
        <div class="card-footer bg-transparent border-top">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeModal">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 550px;">
            <div class="modal-content border-0 shadow" style="max-height: 90vh;">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Category' : 'Add New Category' }}
                    </h5>
                    <button type="button" wire:click="closeModal" class="btn-close"></button>
                </div>

                <form wire:submit="save">
                    <div class="modal-body" style="overflow-y: auto;">
                        <div class="row g-3">
                            @if($ownerCarwashes->count() > 1)
                            <div class="col-12">
                                <label class="form-label">Carwash <span class="text-danger">*</span></label>
                                <select wire:model="carwash_id" class="form-select @error('carwash_id') is-invalid @enderror">
                                    <option value="">Select Carwash</option>
                                    @foreach($ownerCarwashes as $carwash)
                                        <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                                    @endforeach
                                </select>
                                @error('carwash_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Premium Wash, Interior Cleaning">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Brief description of this category"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
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
                                @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
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
                                {{ $editMode ? 'Update Category' : 'Create Category' }}
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
                    <h5 class="mb-2">Delete Category?</h5>
                    <p class="text-muted mb-0">Are you sure you want to delete this category? This action cannot be undone.</p>
                </div>
                <div class="modal-footer border-top justify-content-center gap-2">
                    <button type="button" wire:click="closeDeleteModal" class="btn btn-outline-secondary">
                        <i class="ti ti-x me-1"></i> Cancel
                    </button>
                    <button type="button" wire:click="deleteCategory" class="btn btn-danger">
                        <span wire:loading.remove wire:target="deleteCategory">
                            <i class="ti ti-trash me-1"></i> Delete
                        </span>
                        <span wire:loading wire:target="deleteCategory">
                            <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
