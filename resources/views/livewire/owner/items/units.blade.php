<div>
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="ti ti-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Units</h3>
            <p class="text-muted mb-0">Manage measurement units for your items and services</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary d-flex align-items-center gap-2">
            <i class="ti ti-plus fs-5"></i>
            <span>Add Unit</span>
        </button>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search text-muted"></i>
                        </span>
                        <input type="text"
                               wire:model.live.debounce.300ms="search"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search units...">
                    </div>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <span class="text-muted">Total: {{ $units->total() }} units</span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="width: 40%">Name</th>
                            <th style="width: 15%">Symbol</th>
                            <th style="width: 25%">Description</th>
                            <th style="width: 10%" class="text-center">Status</th>
                            <th style="width: 10%" class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                        <tr wire:key="unit-{{ $unit->id }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary bg-opacity-10 rounded me-3">
                                        <span class="text-primary fw-semibold">{{ strtoupper(substr($unit->name, 0, 2)) }}</span>
                                    </div>
                                    <span class="fw-medium">{{ $unit->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($unit->symbol)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">{{ $unit->symbol }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ Str::limit($unit->description, 40) ?? '-' }}</span>
                            </td>
                            <td class="text-center">
                                <button wire:click="toggleStatus('{{ $unit->id }}')"
                                        class="btn btn-sm border-0 p-0"
                                        title="Click to toggle status">
                                    @if($unit->status === 'active')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2">
                                        <i class="ti ti-check me-1"></i>Active
                                    </span>
                                    @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2">
                                        <i class="ti ti-x me-1"></i>Inactive
                                    </span>
                                    @endif
                                </button>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex justify-content-end gap-1">
                                    <button wire:click="edit('{{ $unit->id }}')"
                                            class="btn btn-sm btn-light btn-icon"
                                            title="Edit">
                                        <i class="ti ti-edit text-primary"></i>
                                    </button>
                                    <button wire:click="confirmDelete('{{ $unit->id }}')"
                                            class="btn btn-sm btn-light btn-icon"
                                            title="Delete">
                                        <i class="ti ti-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <div class="avatar avatar-xl bg-light rounded-circle mb-3">
                                        <i class="ti ti-ruler-2 fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="text-muted">No units found</h5>
                                    <p class="text-muted mb-3">Get started by adding your first unit</p>
                                    <button wire:click="openModal" class="btn btn-primary btn-sm">
                                        <i class="ti ti-plus me-1"></i> Add Unit
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($units->hasPages())
        <div class="card-footer bg-transparent border-top">
            {{ $units->links() }}
        </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom bg-light">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary bg-opacity-10 rounded me-3">
                            <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} text-primary"></i>
                        </div>
                        <h5 class="modal-title mb-0">{{ $editMode ? 'Edit Unit' : 'Add New Unit' }}</h5>
                    </div>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <form wire:submit="save">
                    <div class="modal-body p-4">
                        <!-- Name Field -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">
                                Unit Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="name"
                                   class="form-control form-control-lg @error('name') is-invalid @enderror"
                                   placeholder="e.g., Kilogram, Liter, Piece">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Symbol Field -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Symbol</label>
                            <input type="text"
                                   wire:model="symbol"
                                   class="form-control @error('symbol') is-invalid @enderror"
                                   placeholder="e.g., kg, L, pcs">
                            @error('symbol')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Short abbreviation for the unit</small>
                        </div>

                        <!-- Description Field -->
                        <div class="mb-4">
                            <label class="form-label fw-medium">Description</label>
                            <textarea wire:model="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="3"
                                      placeholder="Optional description..."></textarea>
                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Field -->
                        <div class="mb-0">
                            <label class="form-label fw-medium">Status</label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input type="radio"
                                           wire:model="status"
                                           value="active"
                                           class="form-check-input"
                                           id="statusActive">
                                    <label class="form-check-label" for="statusActive">
                                        <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio"
                                           wire:model="status"
                                           value="inactive"
                                           class="form-check-input"
                                           id="statusInactive">
                                    <label class="form-check-label" for="statusInactive">
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-light" wire:click="closeModal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-2">
                            <i class="ti ti-{{ $editMode ? 'check' : 'plus' }}"></i>
                            <span>{{ $editMode ? 'Update Unit' : 'Add Unit' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-4">
                    <div class="avatar avatar-lg bg-danger bg-opacity-10 rounded-circle mb-3">
                        <i class="ti ti-trash fs-2 text-danger"></i>
                    </div>
                    <h5 class="mb-2">Delete Unit?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to delete this unit?</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-light px-4" wire:click="closeDeleteModal">
                            Cancel
                        </button>
                        <button type="button" class="btn btn-danger px-4 d-flex align-items-center gap-2" wire:click="delete">
                            <i class="ti ti-trash"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
    .btn-icon {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
    }
    .btn-icon:hover {
        background-color: #f1f5f9;
    }
    .avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-sm {
        width: 36px;
        height: 36px;
    }
    .avatar-lg {
        width: 64px;
        height: 64px;
    }
    .avatar-xl {
        width: 80px;
        height: 80px;
    }
</style>
</div>


