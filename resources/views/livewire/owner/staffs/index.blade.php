<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Staff Management</h4>
            <p class="text-muted mb-0">Manage your carwash staff members</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Staff
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
                            <p class="mb-1 opacity-75">Total Staff</p>
                            <h3 class="mb-0">{{ number_format($totalStaff) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-users fs-4"></i>
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
                            <p class="mb-1 opacity-75">Active Staff</p>
                            <h3 class="mb-0">{{ number_format($activeStaff) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-user-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Inactive Staff</p>
                            <h3 class="mb-0">{{ number_format($inactiveStaff) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-user-off fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Carwash</label>
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($carwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, phone, email, position...">
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Member</th>
                            <th>Position</th>
                            <th>Contact</th>
                            <th>Payment</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffs as $staff)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm bg-primary bg-opacity-10 text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <span class="fw-bold">{{ strtoupper(substr($staff->name, 0, 2)) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-medium">{{ $staff->name }}</span>
                                            @if($staff->carwash)
                                                <br>
                                                <small class="text-muted">{{ $staff->carwash->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $staff->position ?? '-' }}</td>
                                <td>
                                    <div>
                                        <i class="ti ti-phone me-1 text-muted"></i>{{ $staff->phone }}
                                    </div>
                                    @if($staff->email)
                                        <small class="text-muted">
                                            <i class="ti ti-mail me-1"></i>{{ $staff->email }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info">{{ $staff->payment_mode_display }}</span>
                                    @if($staff->amount)
                                        <br>
                                        <small class="text-muted">{{ $staff->commission_display }}</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $staff->status_badge_class }}">
                                        {{ ucfirst($staff->status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="edit('{{ $staff->id }}')" class="btn btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button wire:click="toggleStatus('{{ $staff->id }}')" class="btn btn-outline-{{ $staff->status === 'active' ? 'warning' : 'success' }}" title="{{ $staff->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                            <i class="ti ti-{{ $staff->status === 'active' ? 'user-off' : 'user-check' }}"></i>
                                        </button>
                                        <button wire:click="delete('{{ $staff->id }}')" wire:confirm="Are you sure you want to delete this staff member?" class="btn btn-outline-danger" title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-users-group fs-1 d-block mb-2"></i>
                                        No staff members found
                                        <br>
                                        <button wire:click="openModal" class="btn btn-primary btn-sm mt-2">
                                            <i class="ti ti-plus me-1"></i> Add Staff
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($staffs->hasPages())
            <div class="card-footer bg-transparent">
                {{ $staffs->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Staff Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-user me-2"></i>
                            {{ $editingId ? 'Edit Staff' : 'Add Staff' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- Basic Info -->
                            <div class="col-md-6">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter full name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <input type="text" wire:model="position" class="form-control" placeholder="e.g., Car Washer, Cashier">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+255 xxx xxx xxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@example.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payment Settings -->
                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="mb-3">Payment Settings</h6>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                                <select wire:model="payment_mode" class="form-select">
                                    <option value="commission">Commission Based</option>
                                    <option value="salary">Monthly Salary</option>
                                    <option value="hourly">Hourly Rate</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commission Type</label>
                                <select wire:model="commission_type" class="form-select">
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">{{ $commission_type === 'percentage' ? '%' : 'TZS' }}</span>
                                    <input type="number" wire:model="amount" class="form-control" placeholder="0" step="0.01">
                                </div>
                            </div>

                            <!-- Assignment -->
                            <div class="col-12">
                                <hr class="my-2">
                                <h6 class="mb-3">Assignment</h6>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Carwash <span class="text-danger">*</span></label>
                                <select wire:model="carwash_id" class="form-select @error('carwash_id') is-invalid @enderror">
                                    <option value="">Select Carwash</option>
                                    @foreach($carwashes as $carwash)
                                        <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                                    @endforeach
                                </select>
                                @error('carwash_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select wire:model="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
