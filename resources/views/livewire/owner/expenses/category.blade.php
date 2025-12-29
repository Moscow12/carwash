<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Expense Categories</h4>
            <p class="text-muted mb-0">Manage your expense categories</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add
        </button>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Categories</p>
                            <h3 class="mb-0">{{ number_format($totalCategories) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-category fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Parent Categories</p>
                            <h3 class="mb-0">{{ number_format($parentCount) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-folder fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Sub-categories</p>
                            <h3 class="mb-0">{{ number_format($subcategoryCount) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-folder-minus fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">All your expense categories</h5>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label small">Carwash</label>
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">Select Carwash</option>
                        @foreach($carwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Show</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label small">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search...">
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Category name</th>
                            <th>Code</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    @if($category->parent_id)
                                        <span class="text-muted">--</span>{{ $category->name }}
                                    @else
                                        <span class="fw-medium">{{ $category->name }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($category->code)
                                        <span class="badge bg-secondary">{{ $category->code }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $category->status_badge_class }}">
                                        {{ ucfirst($category->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="edit('{{ $category->id }}')" class="btn btn-info btn-sm">
                                            <i class="ti ti-edit me-1"></i> Edit
                                        </button>
                                        <button wire:click="delete('{{ $category->id }}')" wire:confirm="Are you sure you want to delete this category?" class="btn btn-danger btn-sm">
                                            <i class="ti ti-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-category-off fs-1 d-block mb-2"></i>
                                        No categories found
                                        <br>
                                        <button wire:click="openModal" class="btn btn-primary btn-sm mt-2">
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

        <!-- Footer with Pagination -->
        @if($total > 0)
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $from }} to {{ $to }} of {{ $total }} entries
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                            <button wire:click="previousPage" class="page-link" {{ $currentPage <= 1 ? 'disabled' : '' }}>Previous</button>
                        </li>
                        @for($i = 1; $i <= $lastPage; $i++)
                            <li class="page-item {{ $currentPage == $i ? 'active' : '' }}">
                                <button wire:click="gotoPage({{ $i }})" class="page-link">{{ $i }}</button>
                            </li>
                        @endfor
                        <li class="page-item {{ $currentPage >= $lastPage ? 'disabled' : '' }}">
                            <button wire:click="nextPage" class="page-link" {{ $currentPage >= $lastPage ? 'disabled' : '' }}>Next</button>
                        </li>
                    </ul>
                </nav>
            </div>
        @endif
    </div>

    <!-- Add/Edit Category Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $editingId ? 'Edit Expense Category' : 'Add Expense Category' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category name:<span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Category name">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Category code:</label>
                            <input type="text" wire:model="code" class="form-control @error('code') is-invalid @enderror" placeholder="Category code">
                            @error('code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" wire:model.live="isSubcategory" class="form-check-input" id="isSubcategory">
                                <label class="form-check-label" for="isSubcategory">Add as sub-category</label>
                            </div>
                        </div>

                        @if($isSubcategory)
                            <div class="mb-3">
                                <label class="form-label">Select parent category:</label>
                                <select wire:model="parent_id" class="form-select">
                                    <option value="">None</option>
                                    @foreach($parentCategoriesList as $parentCat)
                                        <option value="{{ $parentCat->id }}">{{ $parentCat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($editingId)
                            <div class="mb-3">
                                <label class="form-label">Status:</label>
                                <select wire:model="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" wire:click="save">Save</button>
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
