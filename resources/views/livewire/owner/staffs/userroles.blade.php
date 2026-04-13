<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">User Roles & Permissions</h4>
            <p class="text-muted mb-0">Manage user roles and access levels across your business</p>
        </div>
        <div>
            <a href="{{ route('owner.people.roles') }}" class="btn btn-outline-primary me-2">
                <i class="ti ti-shield-check me-1"></i> Manage Roles
            </a>
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Assign Role
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

    <!-- Role Summary Cards -->
    <div class="row g-3 mb-4">
        @foreach($availableRoles as $roleKey => $roleInfo)
            <div class="col-md-3 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="mb-2">
                            <span class="badge bg-{{ $roleInfo['color'] }} bg-opacity-10 text-{{ $roleInfo['color'] }} p-2">
                                <i class="ti ti-user-shield fs-4"></i>
                            </span>
                        </div>
                        <h6 class="mb-1">{{ $roleInfo['name'] }}</h6>
                        <h3 class="mb-0 fw-bold">{{ $roleStats[$roleKey] ?? 0 }}</h3>
                        <small class="text-muted">Active Users</small>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Available Roles Info -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="ti ti-info-circle me-2"></i>Available Roles</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($availableRoles as $roleKey => $roleInfo)
                    <div class="col-md-6 col-lg-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <span class="badge bg-{{ $roleInfo['color'] }} p-2">
                                    <i class="ti ti-shield-check"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1">{{ $roleInfo['name'] }}</h6>
                                <small class="text-muted">{{ $roleInfo['description'] }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Business</label>
                    <x-forms.select2
                        name="selectedBusiness"
                        :options="collect($ownerBusinesses)"
                        wire:model.live="selectedBusiness"
                        placeholder="Select Business"
                        wrapper="false"
                    />
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Filter by Role</label>
                    <select wire:model.live="selectedRole" class="form-select">
                        <option value="">All Roles</option>
                        @foreach($availableRoles as $roleKey => $roleInfo)
                            <option value="{{ $roleKey }}">{{ $roleInfo['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by user name or email...">
                </div>
            </div>
        </div>
    </div>

    <!-- User Roles Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Scope</th>
                            <th>Outlet</th>
                            <th class="text-center">Status</th>
                            <th>Assigned Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userRoles as $userRole)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">{{ strtoupper(substr($userRole->user->name ?? 'U', 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $userRole->user->name ?? 'Unknown' }}</span>
                                            <br>
                                            <small class="text-muted">{{ $userRole->user->email ?? '' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $availableRoles[$userRole->role]['color'] ?? 'secondary' }} bg-opacity-10 text-{{ $availableRoles[$userRole->role]['color'] ?? 'secondary' }}">
                                        <i class="ti ti-shield-check me-1"></i>
                                        {{ $availableRoles[$userRole->role]['name'] ?? ucfirst($userRole->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($userRole->outlet_id)
                                        <span class="badge bg-info bg-opacity-10 text-info">
                                            <i class="ti ti-building-store me-1"></i>Outlet Level
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success">
                                            <i class="ti ti-building me-1"></i>Business Level
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($userRole->outlet)
                                        <span class="text-muted">{{ $userRole->outlet->name }}</span>
                                    @else
                                        <span class="text-muted">All Outlets</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $userRole->is_active ? 'success' : 'secondary' }}">
                                        {{ $userRole->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $userRole->created_at->format('M d, Y') }}</small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="edit('{{ $userRole->id }}')" class="btn btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button wire:click="toggleStatus('{{ $userRole->id }}')" class="btn btn-outline-{{ $userRole->is_active ? 'warning' : 'success' }}" title="{{ $userRole->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="ti ti-{{ $userRole->is_active ? 'user-off' : 'user-check' }}"></i>
                                        </button>
                                        <button wire:click="delete('{{ $userRole->id }}')" wire:confirm="Are you sure you want to remove this role assignment?" class="btn btn-outline-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-shield-off fs-1 d-block mb-2"></i>
                                        No user roles assigned yet
                                        <br>
                                        <button wire:click="openModal" class="btn btn-primary btn-sm mt-2">
                                            <i class="ti ti-plus me-1"></i> Assign Role
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($userRoles->hasPages())
            <div class="card-footer bg-transparent">
                {{ $userRoles->links() }}
            </div>
        @endif
    </div>

    <!-- Assign/Edit Role Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-shield-check me-2"></i>
                            {{ $editingId ? 'Edit User Role' : 'Assign Role to User' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">User <span class="text-danger">*</span></label>
                                <select wire:model="user_id" class="form-select @error('user_id') is-invalid @enderror" @if($editingId) disabled @endif>
                                    <option value="">Select User</option>
                                    @foreach($availableUsers as $userId => $userName)
                                        <option value="{{ $userId }}">{{ $userName }}</option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Role <span class="text-danger">*</span></label>
                                <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                    @foreach($availableRoles as $roleKey => $roleInfo)
                                        <option value="{{ $roleKey }}">{{ $roleInfo['name'] }} - {{ $roleInfo['description'] }}</option>
                                    @endforeach
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Business <span class="text-danger">*</span></label>
                                <select wire:model="business_id" class="form-select @error('business_id') is-invalid @enderror" disabled>
                                    @foreach($ownerBusinesses as $busId => $busName)
                                        <option value="{{ $busId }}">{{ $busName }}</option>
                                    @endforeach
                                </select>
                                @error('business_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Outlet (Optional)</label>
                                <select wire:model="outlet_id" class="form-select @error('outlet_id') is-invalid @enderror">
                                    <option value="">All Outlets (Business-wide access)</option>
                                    @foreach($availableOutlets as $outletId => $outletName)
                                        <option value="{{ $outletId }}">{{ $outletName }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave blank to grant access to all outlets in this business</small>
                                @error('outlet_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActiveSwitch">
                                    <label class="form-check-label" for="isActiveSwitch">
                                        Active
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingId ? 'Update' : 'Assign Role' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
