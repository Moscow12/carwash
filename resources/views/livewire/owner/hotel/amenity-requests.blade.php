<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Amenity Requests</h4>
            <p class="text-muted mb-0">Manage guest amenity requests and room service</p>
        </div>
        @if($selectedBusiness)
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Request
            </button>
        @endif
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

    <!-- Business Selection -->
    @if($businesses->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Select Hotel</label>
                        <select wire:model.live="selectedBusiness" class="form-select">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($selectedBusiness)
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-progress"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">In Progress</h6>
                                <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Delivered Today</h6>
                                <h3 class="mb-0">{{ $stats['delivered_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-currency-dollar"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Charges Today</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_charges'], 2) }}</h3>
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
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Guest name, room number, amenity...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
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
                                <th>Guest & Room</th>
                                <th>Amenity</th>
                                <th>Quantity</th>
                                <th>Charge</th>
                                <th>Requested At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $request->guest->full_name }}</div>
                                        @if($request->room)
                                            <small class="text-muted">
                                                <i class="ti ti-door me-1"></i>Room {{ $request->room->room_number }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $request->amenity }}</div>
                                        @if($request->notes)
                                            <small class="text-muted">{{ $request->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $request->quantity }}</span>
                                    </td>
                                    <td>
                                        @if($request->charge_amount > 0)
                                            {{ number_format($request->charge_amount, 2) }}
                                        @else
                                            <span class="text-muted">Free</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $request->requested_at->format('M d, Y H:i') }}
                                    </td>
                                    <td>
                                        @if($request->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($request->status === 'in_progress')
                                            <span class="badge bg-info">In Progress</span>
                                        @elseif($request->status === 'delivered')
                                            <span class="badge bg-success">Delivered</span>
                                        @else
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($request->status === 'pending')
                                                <button wire:click="markInProgress({{ $request->id }})" class="btn btn-info" title="Mark In Progress">
                                                    <i class="ti ti-progress"></i>
                                                </button>
                                            @endif
                                            @if(in_array($request->status, ['pending', 'in_progress']))
                                                <button wire:click="markDelivered({{ $request->id }})" class="btn btn-success" title="Mark Delivered">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                                <button wire:click="editRequest({{ $request->id }})" class="btn btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button wire:click="cancelRequest({{ $request->id }})" class="btn btn-danger" title="Cancel">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="ti ti-briefcase-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No amenity requests found</p>
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
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-building fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage amenity requests</p>
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
                            <i class="ti ti-briefcase me-2"></i>
                            {{ $editMode ? 'Edit Request' : 'New Amenity Request' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveRequest">
                            <div class="row g-3">
                                <!-- Guest Information -->
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-user me-2"></i>Guest Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Guest <span class="text-danger">*</span></label>
                                    <select wire:model="guest_id" class="form-select @error('guest_id') is-invalid @enderror">
                                        <option value="">-- Select Guest --</option>
                                        @foreach($guests as $guest)
                                            <option value="{{ $guest->id }}">{{ $guest->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('guest_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Room</label>
                                    <select wire:model="room_id" class="form-select">
                                        <option value="">-- Select Room (Optional) --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->room_number }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Reservation</label>
                                    <select wire:model="reservation_id" class="form-select">
                                        <option value="">-- Select Reservation (Optional) --</option>
                                        @foreach($reservations as $reservation)
                                            <option value="{{ $reservation->id }}">
                                                {{ $reservation->reservation_no }} - {{ $reservation->guest->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Folio</label>
                                    <select wire:model="folio_id" class="form-select">
                                        <option value="">-- Select Folio (Optional) --</option>
                                    </select>
                                    <small class="text-muted">For posting charges</small>
                                </div>

                                <!-- Request Details -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-list me-2"></i>Request Details</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Amenity <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="amenity" class="form-control @error('amenity') is-invalid @enderror" placeholder="e.g., Extra towels, Pillow">
                                    @error('amenity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1">
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Charge Amount</label>
                                    <input type="number" step="0.01" wire:model="charge_amount" class="form-control" placeholder="0.00">
                                    <small class="text-muted">Leave 0 for free amenities</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="3" placeholder="Special instructions or notes"></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>{{ $editMode ? 'Update' : 'Create' }} Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
