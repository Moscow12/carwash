<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Housekeeping Status</h4>
            <p class="text-muted mb-0">Monitor and update room cleanliness status in real-time</p>
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
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Clean</h6>
                                <h3 class="mb-0">{{ $stats['clean'] }}</h3>
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
                                <i class="ti ti-trash"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Dirty</h6>
                                <h3 class="mb-0">{{ $stats['dirty'] }}</h3>
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
                                <i class="ti ti-eye-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Inspected</h6>
                                <h3 class="mb-0">{{ $stats['inspected'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-tool"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Out of Order</h6>
                                <h3 class="mb-0">{{ $stats['out_of_order'] }}</h3>
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
                    <div class="col-md-4">
                        <label class="form-label">Search Room</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by room number...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Floor</label>
                        <select wire:model.live="filterFloor" class="form-select">
                            <option value="">All Floors</option>
                            @foreach($floors as $floor)
                                <option value="{{ $floor }}">Floor {{ $floor }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="clean">Clean</option>
                            <option value="dirty">Dirty</option>
                            <option value="inspected">Inspected</option>
                            <option value="out_of_order">Out of Order</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Status Grid -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Housekeeping Status Overview ({{ $rooms->count() }} rooms)</h6>
            </div>
            <div class="card-body">
                @if($rooms->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Type</th>
                                    <th>Floor</th>
                                    <th>Current Status</th>
                                    <th>Room Status</th>
                                    <th>Quick Actions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rooms as $room)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $room->number }}</div>
                                            @if($room->name)
                                                <small class="text-muted">{{ $room->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($room->roomType)
                                                <span class="badge bg-light text-dark">{{ $room->roomType->name }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">Floor {{ $room->floor }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'clean' => 'success',
                                                    'dirty' => 'danger',
                                                    'inspected' => 'info',
                                                    'out_of_order' => 'warning',
                                                ];
                                                $statusIcons = [
                                                    'clean' => 'check',
                                                    'dirty' => 'trash',
                                                    'inspected' => 'eye-check',
                                                    'out_of_order' => 'tool',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$room->housekeeping_status ?? 'clean'] }}">
                                                <i class="ti ti-{{ $statusIcons[$room->housekeeping_status ?? 'clean'] }} me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $room->housekeeping_status ?? 'clean')) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $roomStatusColors = [
                                                    'available' => 'success',
                                                    'occupied' => 'danger',
                                                    'cleaning' => 'info',
                                                    'maintenance' => 'warning',
                                                    'reserved' => 'primary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $roomStatusColors[$room->status] ?? 'secondary' }}">
                                                {{ ucfirst($room->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button wire:click="quickUpdateStatus('{{ $room->id }}', 'clean')"
                                                        class="btn btn-outline-success"
                                                        title="Mark as Clean">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                                <button wire:click="quickUpdateStatus('{{ $room->id }}', 'dirty')"
                                                        class="btn btn-outline-danger"
                                                        title="Mark as Dirty">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                                <button wire:click="quickUpdateStatus('{{ $room->id }}', 'inspected')"
                                                        class="btn btn-outline-info"
                                                        title="Mark as Inspected">
                                                    <i class="ti ti-eye-check"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <button wire:click="openModal('{{ $room->id }}')"
                                                    class="btn btn-sm btn-primary">
                                                <i class="ti ti-edit"></i> Update
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-bed-off fs-1 text-muted"></i>
                        <h5 class="mt-3">No Rooms Found</h5>
                        <p class="text-muted">Try adjusting your filters or add rooms first</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Status Legend -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Housekeeping Status Guide</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li><strong class="text-success">Clean:</strong> Room is ready for guest occupancy</li>
                            <li><strong class="text-danger">Dirty:</strong> Room needs cleaning after checkout</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li><strong class="text-info">Inspected:</strong> Room has been cleaned and inspected</li>
                            <li><strong class="text-warning">Out of Order:</strong> Room unavailable due to maintenance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to view housekeeping status</p>
            </div>
        </div>
    @endif

    <!-- Update Status Modal -->
    @if($showModal && $selectedRoom)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-edit me-2"></i>
                            Update Housekeeping Status - Room {{ $selectedRoom->number }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Current Status:</strong>
                            {{ ucfirst(str_replace('_', ' ', $selectedRoom->housekeeping_status ?? 'clean')) }}
                        </div>

                        <form wire:submit.prevent="updateStatus">
                            <div class="mb-3">
                                <label class="form-label">New Status <span class="text-danger">*</span></label>
                                <select wire:model="housekeeping_status" class="form-select @error('housekeeping_status') is-invalid @enderror">
                                    <option value="clean">✅ Clean - Ready for guests</option>
                                    <option value="dirty">🗑️ Dirty - Needs cleaning</option>
                                    <option value="inspected">👁️ Inspected - Cleaned and verified</option>
                                    <option value="out_of_order">🔧 Out of Order - Maintenance required</option>
                                </select>
                                @error('housekeeping_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea wire:model="notes" class="form-control" rows="3" placeholder="Add any notes about the room condition..."></textarea>
                            </div>

                            <div class="alert alert-warning">
                                <small>
                                    <i class="ti ti-info-circle me-1"></i>
                                    Changing status to "Dirty" or "Inspected" will automatically create a housekeeping task.
                                </small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="updateStatus">
                            <i class="ti ti-device-floppy me-1"></i> Update Status
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
