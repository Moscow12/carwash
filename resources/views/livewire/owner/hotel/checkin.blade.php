<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Guest Check-In</h4>
            <p class="text-muted mb-0">Process guest arrivals and room assignments</p>
        </div>
        <a href="{{ route('owner.hotel.reservations') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-calendar-event me-1"></i> View All Reservations
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
                    @if($branches->count() > 0)
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
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-calendar-event"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Arrivals Today</h6>
                                <h3 class="mb-0">{{ $stats['today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-danger text-white me-3">
                                <i class="ti ti-alert-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Overdue Check-Ins</h6>
                                <h3 class="mb-0">{{ $stats['overdue'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-calendar-time"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Upcoming (7 days)</h6>
                                <h3 class="mb-0">{{ $stats['upcoming'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label">Search Reservations</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by reservation number or guest name...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservations Ready for Check-In -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti ti-login me-2"></i>Reservations Ready for Check-In</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reservation No</th>
                                <th>Guest</th>
                                <th>Room Type</th>
                                <th>Check-In Date</th>
                                <th>Nights</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $reservation)
                                <tr class="{{ $reservation->check_in_date->isToday() ? 'table-info' : '' }}">
                                    <td>
                                        <span class="fw-bold">{{ $reservation->reservation_no }}</span>
                                        @if($reservation->source)
                                            <br><small class="text-muted"><i class="ti ti-source me-1"></i>{{ $reservation->source->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-primary me-2">
                                                <i class="ti ti-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $reservation->guest->full_name }}</div>
                                                @if($reservation->guest->phone)
                                                    <small class="text-muted"><i class="ti ti-phone me-1"></i>{{ $reservation->guest->phone }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $reservation->roomType->name }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $reservation->check_in_date->format('M d, Y') }}</div>
                                        @if($reservation->check_in_date->isToday())
                                            <span class="badge bg-success">Today</span>
                                        @elseif($reservation->check_in_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $reservation->total_nights }}</span>
                                    </td>
                                    <td>
                                        <i class="ti ti-users me-1"></i>{{ $reservation->adults }}
                                        @if($reservation->children > 0)
                                            + {{ $reservation->children }}
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ number_format($reservation->total_amount, 2) }}</div>
                                        @if($reservation->deposit_amount > 0)
                                            <small class="text-success">Paid: {{ number_format($reservation->deposit_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $reservation->status === 'confirmed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($reservation->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button wire:click="openCheckInModal('{{ $reservation->id }}')"
                                                class="btn btn-sm btn-primary">
                                            <i class="ti ti-login me-1"></i> Check-In
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="ti ti-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No reservations ready for check-in</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($reservations->hasPages())
                    <div class="card-footer bg-white">
                        {{ $reservations->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to process check-ins</p>
            </div>
        </div>
    @endif

    <!-- Check-In Modal -->
    @if($showModal && $selectedReservation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="ti ti-login me-2"></i>Guest Check-In
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Guest Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-user me-2"></i>Guest Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Name:</strong> {{ $selectedReservation['guest']['full_name'] }}</p>
                                        <p class="mb-2"><strong>Phone:</strong> {{ $selectedReservation['guest']['phone'] ?? 'N/A' }}</p>
                                        <p class="mb-2"><strong>Email:</strong> {{ $selectedReservation['guest']['email'] ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>ID Type:</strong> {{ $selectedReservation['guest']['id_type'] ?? 'N/A' }}</p>
                                        <p class="mb-2"><strong>ID Number:</strong> {{ $selectedReservation['guest']['id_number'] ?? 'N/A' }}</p>
                                        <p class="mb-2"><strong>Nationality:</strong> {{ $selectedReservation['guest']['nationality'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reservation Details -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Reservation Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Reservation No:</strong> {{ $selectedReservation['reservation_no'] }}</p>
                                        <p class="mb-2"><strong>Room Type:</strong> {{ $selectedReservation['room_type']['name'] }}</p>
                                        <p class="mb-2"><strong>Guests:</strong> {{ $selectedReservation['adults'] }} Adults, {{ $selectedReservation['children'] }} Children</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Check-In:</strong> {{ \Carbon\Carbon::parse($selectedReservation['check_in_date'])->format('M d, Y') }}</p>
                                        <p class="mb-2"><strong>Check-Out:</strong> {{ \Carbon\Carbon::parse($selectedReservation['check_out_date'])->format('M d, Y') }}</p>
                                        <p class="mb-2"><strong>Nights:</strong> {{ $selectedReservation['total_nights'] }}</p>
                                    </div>
                                </div>
                                @if($selectedReservation['special_requests'])
                                    <div class="alert alert-info mt-2 mb-0">
                                        <strong>Special Requests:</strong> {{ $selectedReservation['special_requests'] }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Room Selection -->
                        <div class="mb-3">
                            <label class="form-label">Select Room <span class="text-danger">*</span></label>
                            <select wire:model="selectedRoom" class="form-select @error('selectedRoom') is-invalid @enderror">
                                <option value="">-- Select Available Room --</option>
                                @foreach($availableRooms as $room)
                                    <option value="{{ $room['id'] }}">
                                        Room {{ $room['number'] }}
                                        @if($room['floor'])
                                            - Floor {{ $room['floor'] }}
                                        @endif
                                        ({{ $room['is_smoking'] ? 'Smoking' : 'Non-Smoking' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedRoom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if(count($availableRooms) === 0)
                                <div class="text-danger small mt-1">
                                    <i class="ti ti-alert-circle me-1"></i>No rooms available of this type. Please check room status or change room type.
                                </div>
                            @endif
                        </div>

                        <!-- Check-In Time -->
                        <div class="mb-3">
                            <label class="form-label">Actual Check-In Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" wire:model="actualCheckInTime" class="form-control @error('actualCheckInTime') is-invalid @enderror">
                            @error('actualCheckInTime')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Payment Information -->
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="ti ti-currency-dollar me-2"></i>Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <p class="mb-0 text-muted">Total Amount:</p>
                                        <h5 class="mb-0">{{ number_format($selectedReservation['total_amount'], 2) }}</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-0 text-muted">Previous Deposit:</p>
                                        <h5 class="mb-0 text-success">{{ number_format($selectedReservation['deposit_amount'], 2) }}</h5>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-0 text-muted">Balance Due:</p>
                                        <h5 class="mb-0 text-danger">{{ number_format($selectedReservation['total_amount'] - $selectedReservation['deposit_amount'], 2) }}</h5>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Deposit Paid <span class="text-danger">*</span></label>
                                        <input type="number" wire:model="depositPaid" class="form-control @error('depositPaid') is-invalid @enderror" step="0.01" min="0">
                                        @error('depositPaid')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select wire:model="paymentMethod" class="form-select @error('paymentMethod') is-invalid @enderror">
                                            <option value="cash">Cash</option>
                                            <option value="card">Credit/Debit Card</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="mobile_money">Mobile Money</option>
                                        </select>
                                        @error('paymentMethod')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Key Card Number</label>
                                <input type="text" wire:model="keyCardNumber" class="form-control" placeholder="e.g., KC-001">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Check-In Notes</label>
                                <input type="text" wire:model="checkInNotes" class="form-control" placeholder="Any special notes">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="processCheckIn" {{ count($availableRooms) === 0 ? 'disabled' : '' }}>
                            <i class="ti ti-check me-1"></i> Complete Check-In
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
