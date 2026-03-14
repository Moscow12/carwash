<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Maintenance Management</h4>
            <p class="text-muted mb-0">Track and manage maintenance requests</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Request
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

    <!-- Hotel Selection -->
    @if($hotels->count() > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Select Hotel</label>
                <select wire:model.live="selectedHotel" class="form-select">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($selectedHotel)
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'requests' ? 'active' : '' }}"
                        wire:click="switchTab('requests')"
                        type="button">
                    <i class="ti ti-tool me-1"></i> Maintenance Requests
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'preventive' ? 'active' : '' }}"
                        wire:click="switchTab('preventive')"
                        type="button">
                    <i class="ti ti-calendar-event me-1"></i> Preventive Maintenance
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Maintenance Requests Tab -->
            @if($activeTab === 'requests')
                <!-- Statistics -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-warning border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-warning text-white me-3">
                                        <i class="ti ti-alert-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Open</h6>
                                        <h3 class="mb-0">{{ $stats['open'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-info border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-info text-white me-3">
                                        <i class="ti ti-loader"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">In Progress</h6>
                                        <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-danger border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-danger text-white me-3">
                                        <i class="ti ti-urgent"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Urgent</h6>
                                        <h3 class="mb-0">{{ $stats['urgent'] }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-success border-4">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-success text-white me-3">
                                        <i class="ti ti-currency-dollar"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Total Cost</h6>
                                        <h3 class="mb-0">{{ number_format($stats['total_cost'], 2) }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search...">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select wire:model.live="categoryFilter" class="form-select">
                                    <option value="">All Categories</option>
                                    <option value="plumbing">Plumbing</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="AC">AC</option>
                                    <option value="furniture">Furniture</option>
                                    <option value="network">Network</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Priority</label>
                                <select wire:model.live="priorityFilter" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="high">High</option>
                                    <option value="normal">Normal</option>
                                    <option value="low">Low</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requests Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Room/Area</th>
                                        <th>Category</th>
                                        <th>Description</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assigned To</th>
                                        <th>Cost</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $request)
                                        <tr>
                                            <td>
                                                @if($request->room)
                                                    <span class="badge bg-primary">Room {{ $request->room->number }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Common Area</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $categoryColors = [
                                                        'plumbing' => 'info',
                                                        'electrical' => 'warning',
                                                        'AC' => 'primary',
                                                        'furniture' => 'secondary',
                                                        'network' => 'success',
                                                        'other' => 'dark',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $categoryColors[$request->category] ?? 'secondary' }}">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ Str::limit($request->description, 50) }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $priorityColors = [
                                                        'urgent' => 'danger',
                                                        'high' => 'warning',
                                                        'normal' => 'info',
                                                        'low' => 'secondary',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $priorityColors[$request->priority] ?? 'secondary' }}">
                                                    {{ ucfirst($request->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'open' => 'warning',
                                                        'in_progress' => 'info',
                                                        'resolved' => 'success',
                                                        'closed' => 'secondary',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$request->status] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($request->assignedTo)
                                                    <small>{{ $request->assignedTo->name }}</small>
                                                @else
                                                    <span class="text-muted">Unassigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($request->actual_cost > 0)
                                                    <span class="text-success">{{ number_format($request->actual_cost, 2) }}</span>
                                                @elseif($request->estimated_cost > 0)
                                                    <span class="text-muted">~{{ number_format($request->estimated_cost, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @if($request->status === 'open')
                                                        <button wire:click="updateStatus('{{ $request->id }}', 'in_progress')"
                                                                class="btn btn-sm btn-outline-info"
                                                                title="Start Work">
                                                            <i class="ti ti-player-play"></i>
                                                        </button>
                                                    @elseif($request->status === 'in_progress')
                                                        <button wire:click="updateStatus('{{ $request->id }}', 'resolved')"
                                                                class="btn btn-sm btn-outline-success"
                                                                title="Mark Resolved">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                    @endif
                                                    <button wire:click="editRequest('{{ $request->id }}')"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="delete('{{ $request->id }}')"
                                                            wire:confirm="Are you sure you want to delete this request?"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <i class="ti ti-tool fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No maintenance requests found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $requests->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Preventive Maintenance Tab -->
            @if($activeTab === 'preventive')
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="ti ti-calendar-event fs-1 text-muted"></i>
                        <h5 class="mt-3">Preventive Maintenance Schedule</h5>
                        <p class="text-muted">Schedule and track preventive maintenance tasks for equipment and facilities</p>
                        <p class="text-info"><small>This feature will include scheduled maintenance tasks, equipment tracking, and automated reminders</small></p>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage maintenance</p>
            </div>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-tool me-2"></i>
                            {{ $editMode ? 'Edit Maintenance Request' : 'New Maintenance Request' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Room/Area</label>
                                    <select wire:model="room_id" class="form-select @error('room_id') is-invalid @enderror">
                                        <option value="">Common Area</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">Room {{ $room->number }}</option>
                                        @endforeach
                                    </select>
                                    @error('room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select wire:model="category" class="form-select @error('category') is-invalid @enderror">
                                        <option value="plumbing">Plumbing</option>
                                        <option value="electrical">Electrical</option>
                                        <option value="AC">AC</option>
                                        <option value="furniture">Furniture</option>
                                        <option value="network">Network</option>
                                        <option value="other">Other</option>
                                    </select>
                                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Describe the issue..."></textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select wire:model="priority" class="form-select @error('priority') is-invalid @enderror">
                                        <option value="low">Low</option>
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Assigned To</label>
                                    <select wire:model="assigned_to" class="form-select">
                                        <option value="">Unassigned</option>
                                        @foreach($staff as $member)
                                            <option value="{{ $member->id }}">{{ $member->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Estimated Cost</label>
                                    <input type="number" wire:model="estimated_cost" class="form-control" step="0.01" min="0">
                                </div>

                                @if($editMode)
                                    <div class="col-md-12">
                                        <label class="form-label">Actual Cost</label>
                                        <input type="number" wire:model="actual_cost" class="form-control" step="0.01" min="0">
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Request' : 'Create Request' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
