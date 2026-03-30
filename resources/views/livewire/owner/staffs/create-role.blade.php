<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Add Role</h4>
            <p class="text-muted mb-0">Create a new role and assign permissions</p>
        </div>
        <div>
            <button wire:click="cancel" class="btn btn-secondary me-2">
                <i class="ti ti-arrow-left me-1"></i> Back
            </button>
            <button wire:click="save" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Save Role
            </button>
        </div>
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

    <!-- Role Form -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Role Name: <span class="text-danger">*</span></label>
                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., store_manager">
                    <small class="text-muted">Use lowercase with underscores (e.g., store_manager)</small>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Display Name: <span class="text-danger">*</span></label>
                    <input type="text" wire:model="display_name" class="form-control @error('display_name') is-invalid @enderror" placeholder="e.g., Store Manager">
                    @error('display_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Description:</label>
                    <textarea wire:model="description" class="form-control" rows="2" placeholder="Brief description of this role's responsibilities"></textarea>
                </div>
                @if(!empty($ownerBusinesses))
                <div class="col-md-6">
                    <label class="form-label fw-bold">Business:</label>
                    <select wire:model="business_id" class="form-select">
                        <option value="">System-wide (All Businesses)</option>
                        @foreach($ownerBusinesses as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Leave as system-wide to use across all businesses</small>
                </div>
                @endif
            </div>

            <hr class="my-4">

            <h5 class="mb-3">Permissions:</h5>

            @foreach($permissionsByCategory as $category => $permissions)
                <div class="permission-section mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ $category }}</h6>
                        <div class="form-check">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                id="select-all-{{ Str::slug($category) }}"
                                wire:click="toggleCategoryPermissions('{{ $category }}')"
                                @if(count(array_intersect(collect($permissions)->pluck('id')->toArray(), $selectedPermissions)) === count($permissions))
                                    checked
                                @endif
                            >
                            <label class="form-check-label text-primary fw-bold" for="select-all-{{ Str::slug($category) }}">
                                Select all
                            </label>
                        </div>
                    </div>
                    <div class="row g-3">
                        @foreach($permissions as $permission)
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        class="form-check-input"
                                        id="perm-{{ $permission['id'] }}"
                                        value="{{ $permission['id'] }}"
                                        wire:model="selectedPermissions"
                                    >
                                    <label class="form-check-label" for="perm-{{ $permission['id'] }}">
                                        {{ $permission['display_name'] }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @if(!$loop->last)
                    <hr class="my-3">
                @endif
            @endforeach
        </div>
    </div>

    <style>
        .permission-section {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .permission-section h6 {
            color: #2d3748;
            font-weight: 700;
        }

        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        .form-check-label {
            color: #4a5568;
            cursor: pointer;
        }
    </style>
</div>
