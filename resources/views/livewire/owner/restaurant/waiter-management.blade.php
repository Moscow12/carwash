<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Waiter Management</h4>
            <p class="text-muted mb-0">Assign waiters to tables per session</p>
        </div>
        @if($selectedSession)
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Assign Waiter
            </button>
        @endif
    </div>

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

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                @if($businesses->count() > 1)
                    <div class="col-md-4">
                        <label class="form-label">Business</label>
                        <select wire:model.live="selectedBusiness" class="form-select">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-4">
                    <label class="form-label">Outlet</label>
                    <select wire:model.live="selectedOutlet" class="form-select">
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if($selectedOutlet)
                    <div class="col-md-4">
                        <label class="form-label">Session</label>
                        <select wire:model.live="selectedSession" class="form-select">
                            <option value="">Select Session</option>
                            @foreach($sessions as $session)
                                <option value="{{ $session->id }}">{{ $session->opened_at->format('M d, Y h:i A') }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($selectedSession)
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3"><i class="ti ti-users"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Active Waiters</h6>
                                <h3 class="mb-0">{{ $stats['active_waiters'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3"><i class="ti ti-check"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Assigned Tables</h6>
                                <h3 class="mb-0">{{ $stats['assigned_tables'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3"><i class="ti ti-armchair"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Unassigned</h6>
                                <h3 class="mb-0">{{ $stats['unassigned_tables'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3"><i class="ti ti-list"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Total Tables</h6>
                                <h3 class="mb-0">{{ $stats['total_tables'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search waiter or table...">
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Waiter</th>
                                <th>Table</th>
                                <th>Assigned At</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td><i class="ti ti-user me-2"></i>{{ $assignment->staff->name }}</td>
                                    <td><i class="ti ti-armchair me-2"></i>Table {{ $assignment->table->table_number }}</td>
                                    <td>{{ $assignment->assigned_at->format('h:i A') }}</td>
                                    <td>{{ $assignment->assigned_at->diffForHumans(null, true) }}</td>
                                    <td>
                                        <button wire:click="releaseAssignment('{{ $assignment->id }}')" class="btn btn-sm btn-danger">
                                            <i class="ti ti-x me-1"></i>Release
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="ti ti-users-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No waiter assignments found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $assignments->links() }}</div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-click fs-1 text-muted"></i>
                <h5 class="mt-3">Select Session</h5>
                <p class="text-muted">Please select an outlet and session to manage waiters</p>
            </div>
        </div>
    @endif

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-user-plus me-2"></i>Assign Waiter</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="assignWaiter">
                            <div class="mb-3">
                                <label class="form-label">Waiter <span class="text-danger">*</span></label>
                                <select wire:model="staff_id" class="form-select @error('staff_id') is-invalid @enderror">
                                    <option value="">Select Waiter</option>
                                    @foreach($waiters as $waiter)
                                        <option value="{{ $waiter->id }}">{{ $waiter->name }}</option>
                                    @endforeach
                                </select>
                                @error('staff_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Table <span class="text-danger">*</span></label>
                                <select wire:model="table_id" class="form-select @error('table_id') is-invalid @enderror">
                                    <option value="">Select Table</option>
                                    @foreach($tables as $table)
                                        <option value="{{ $table->id }}">Table {{ $table->table_number }} ({{ $table->status }})</option>
                                    @endforeach
                                </select>
                                @error('table_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary"><i class="ti ti-check me-1"></i>Assign</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
