<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Rooms Management</h4>
            <p class="text-muted mb-0">Manage individual room inventory</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Room
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
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Hotel Selection -->
    @if($hotels->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Select Hotel</label>
                        <select wire:model.live="selectedHotel" class="form-select">
                            <option value="">-- Select Hotel --</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($branches && $branches->count() > 0)
                        <div class="col-md-6">
                            <label class="form-label">Select Branch</label>
                            <select wire:model.live="selectedBranch" class="form-select">
                                <option value="">All Branches</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">
                                        {{ $branch->name }} {{ $branch->is_main ? '(Main)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($selectedHotel)
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Total</h6>
                        <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm border-start border-success border-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Available</h6>
                        <h3 class="mb-0 text-success">{{ $stats['available'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm border-start border-danger border-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Occupied</h6>
                        <h3 class="mb-0 text-danger">{{ $stats['occupied'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm border-start border-warning border-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Cleaning</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['cleaning'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm border-start border-info border-3">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Maintenance</h6>
                        <h3 class="mb-0 text-info">{{ $stats['maintenance'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-1">Occupancy</h6>
                        <h3 class="mb-0">{{ $stats['total'] > 0 ? round(($stats['occupied'] / $stats['total']) * 100) : 0 }}%</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search Room Number</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by room number...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="out_of_order">Out of Order</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Floor</label>
                        <input type="text" wire:model.live="floorFilter" class="form-control" placeholder="e.g., 1, 2, 3...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Rooms Grid -->
        <div class="row g-3">
            @forelse($rooms as $room)
                <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                    <div class="card shadow-sm h-100
                        @if($room->status === 'available') border-success
                        @elseif($room->status === 'occupied') border-danger
                        @elseif($room->status === 'cleaning') border-warning
                        @elseif($room->status === 'maintenance') border-info
                        @else border-dark
                        @endif border-start border-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="mb-0">{{ $room->number }}</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light p-1" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" wire:click="editRoom('{{ $room->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'available')"><i class="ti ti-check me-2"></i>Available</a></li>
                                        <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'cleaning')"><i class="ti ti-vacuum-cleaner me-2"></i>Cleaning</a></li>
                                        <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'maintenance')"><i class="ti ti-tool me-2"></i>Maintenance</a></li>
                                    </ul>
                                </div>
                            </div>

                            @if($room->floor)
                                <small class="text-muted d-block mb-2">Floor {{ $room->floor }}</small>
                            @endif

                            <span class="badge bg-{{ $room->status === 'available' ? 'success' : ($room->status === 'occupied' ? 'danger' : ($room->status === 'cleaning' ? 'warning' : 'info')) }} mb-2">
                                {{ ucfirst(str_replace('_', ' ', $room->status)) }}
                            </span>

                            <div class="small mb-1">
                                <strong>{{ $room->roomType->name }}</strong>
                            </div>

                            @if($room->is_smoking)
                                <span class="badge bg-secondary-subtle text-secondary small">Smoking</span>
                            @else
                                <span class="badge bg-light text-dark small">Non-Smoking</span>
                            @endif

                            @if(!$room->is_active)
                                <div class="mt-2">
                                    <span class="badge bg-dark">Inactive</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="ti ti-door fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No rooms found. Click "Add Room" to create one.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $rooms->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage rooms</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-door me-2"></i>
                            {{ $editMode ? 'Edit Room' : 'Add New Room' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Validation Errors -->
                        @if(!empty($validationErrors))
                            <div class="alert alert-danger">
                                <strong><i class="ti ti-alert-circle me-2"></i>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($validationErrors as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Processing Overlay -->
                        @if($isProcessing)
                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.9); z-index: 1050;">
                                <div class="text-center">
                                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                        <span class="visually-hidden">Processing...</span>
                                    </div>
                                    <h5 class="text-primary">{{ $editMode ? 'Updating' : 'Creating' }} Room...</h5>
                                    <p class="text-muted">Please wait</p>
                                </div>
                            </div>
                        @endif

                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                    <select wire:model="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                        <option value="">-- Select Room Type --</option>
                                        @foreach($roomTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('room_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Room Number <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="number" class="form-control @error('number') is-invalid @enderror" placeholder="e.g., 101">
                                    @error('number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Floor</label>
                                    <input type="text" wire:model="floor" class="form-control" placeholder="e.g., 1">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="available">Available</option>
                                        <option value="occupied">Occupied</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="out_of_order">Out of Order</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Smoking <span class="text-danger">*</span></label>
                                    <select wire:model="is_smoking" class="form-select">
                                        <option value="0">Non-Smoking</option>
                                        <option value="1">Smoking</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Active Status <span class="text-danger">*</span></label>
                                    <select wire:model="is_active" class="form-select">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any special notes about this room..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal" {{ $isProcessing ? 'disabled' : '' }}>
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled" {{ $isProcessing ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="save">
                                <i class="ti ti-device-floppy me-1"></i>
                                {{ $editMode ? 'Update Room' : 'Save Room' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
