<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Roles & Permissions Management</h4>
            <p class="text-muted mb-0">Create and manage custom roles with specific permissions</p>
        </div>
        <a href="{{ route('owner.people.roles.create') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Role
        </a>
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

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label small">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name or description...">
                </div>
            </div>
        </div>
    </div>

    <!-- Roles Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Role</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Scope</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>
                                    <div>
                                        <span class="fw-bold">{{ $role->display_name }}</span>
                                        @if($role->is_system)
                                            <span class="badge bg-warning bg-opacity-10 text-warning ms-2">
                                                <i class="ti ti-lock"></i> System
                                            </span>
                                        @endif
                                        <br>
                                        <small class="text-muted">{{ $role->name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $role->description ?: 'No description' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary">
                                        <i class="ti ti-shield-check me-1"></i>
                                        {{ $role->permissions->count() }} permissions
                                    </span>
                                </td>
                                <td>
                                    @if($role->business_id)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-building me-1"></i>{{ $role->business->name ?? 'Business' }}
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-world me-1"></i>System-wide
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $role->is_active ? 'success' : 'secondary' }}">
                                        {{ $role->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('owner.people.roles.edit', $role->id) }}" class="btn btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        @if(!$role->is_system)
                                            <button wire:click="toggleStatus('{{ $role->id }}')" class="btn btn-outline-{{ $role->is_active ? 'warning' : 'success' }}" title="{{ $role->is_active ? 'Deactivate' : 'Activate' }}">
                                                <i class="ti ti-{{ $role->is_active ? 'toggle-right' : 'toggle-left' }}"></i>
                                            </button>
                                            <button wire:click="delete('{{ $role->id }}')" wire:confirm="Are you sure you want to delete this role?" class="btn btn-outline-danger" title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-shield-off fs-1 d-block mb-2"></i>
                                        No roles found
                                        <br>
                                        <a href="{{ route('owner.people.roles.create') }}" class="btn btn-primary btn-sm mt-2">
                                            <i class="ti ti-plus me-1"></i> Add Role
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($roles->hasPages())
            <div class="card-footer bg-transparent">
                {{ $roles->links() }}
            </div>
        @endif
    </div>
</div>
