<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Guest Check-Out</h4>
            <p class="text-muted mb-0">Process guest departures and final billing</p>
        </div>
        <a href="{{ route('owner.hotel.frontdesk') }}" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-dashboard me-1"></i> Front Desk
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
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-calendar-event"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Departures Today</h6>
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
                                <h6 class="text-muted mb-1">Overdue Check-Outs</h6>
                                <h3 class="mb-0">{{ $stats['overdue'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
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
                        <label class="form-label">Search Guests</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by room number, guest name, phone, or reservation number...">
                    </div>
                </div>
            </div>
        </div>

        <!-- Checked-In Guests -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="ti ti-logout me-2"></i>Guests Ready for Check-Out</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Room</th>
                                <th>Guest</th>
                                <th>Room Type</th>
                                <th>Check-Out Date</th>
                                <th>Nights Stayed</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $reservation)
                                <tr class="{{ $reservation->check_out_date->isToday() ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-primary text-white me-2">
                                                <i class="ti ti-door"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold">Room {{ $reservation->roomAllocation->room->number }}</span>
                                                @if($reservation->roomAllocation->room->floor)
                                                    <br><small class="text-muted">Floor {{ $reservation->roomAllocation->room->floor }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-bold">{{ $reservation->guest->full_name }}</div>
                                            @if($reservation->guest->phone)
                                                <small class="text-muted"><i class="ti ti-phone me-1"></i>{{ $reservation->guest->phone }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">{{ $reservation->roomType->name }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $reservation->check_out_date->format('M d, Y') }}</div>
                                        @if($reservation->check_out_date->isToday())
                                            <span class="badge bg-warning">Today</span>
                                        @elseif($reservation->check_out_date->isPast())
                                            <span class="badge bg-danger">Overdue</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $reservation->total_nights }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ number_format($reservation->total_amount, 2) }}</div>
                                        @if($reservation->deposit_amount > 0)
                                            <small class="text-success">Paid: {{ number_format($reservation->deposit_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Checked In</span>
                                    </td>
                                    <td>
                                        <button wire:click="openCheckOutModal('{{ $reservation->id }}')"
                                                class="btn btn-sm btn-warning">
                                            <i class="ti ti-logout me-1"></i> Check-Out
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="ti ti-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No guests ready for check-out</p>
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
                <p class="text-muted">Please select a hotel to process check-outs</p>
            </div>
        </div>
    @endif

    <!-- Check-Out Modal -->
    @if($showModal && $selectedReservation)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">
                            <i class="ti ti-logout me-2"></i>Guest Check-Out - Room {{ $roomAllocation['room']['number'] }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <!-- Guest Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-user me-2"></i>Guest Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2"><strong>Name:</strong> {{ $selectedReservation['guest']['full_name'] }}</p>
                                        <p class="mb-2"><strong>Phone:</strong> {{ $selectedReservation['guest']['phone'] ?? 'N/A' }}</p>
                                        <p class="mb-2"><strong>Email:</strong> {{ $selectedReservation['guest']['email'] ?? 'N/A' }}</p>
                                        <p class="mb-0"><strong>Room:</strong> {{ $roomAllocation['room']['number'] }}</p>
                                    </div>
                                </div>

                                <!-- Stay Summary -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-calendar-event me-2"></i>Stay Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2"><strong>Check-In:</strong> {{ \Carbon\Carbon::parse($roomAllocation['actual_check_in'])->format('M d, Y H:i') }}</p>
                                        <p class="mb-2"><strong>Expected Check-Out:</strong> {{ \Carbon\Carbon::parse($selectedReservation['check_out_date'])->format('M d, Y') }}</p>
                                        <p class="mb-0"><strong>Total Nights:</strong> {{ $selectedReservation['total_nights'] }}</p>
                                    </div>
                                </div>

                                <!-- Room Condition -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-clipboard-check me-2"></i>Room Inspection</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Room Condition <span class="text-danger">*</span></label>
                                            <select wire:model="roomCondition" class="form-select @error('roomCondition') is-invalid @enderror">
                                                <option value="good">Good Condition</option>
                                                <option value="needs_deep_clean">Needs Deep Cleaning</option>
                                                <option value="damaged">Damaged</option>
                                            </select>
                                            @error('roomCondition')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        @if($roomCondition === 'damaged')
                                            <div class="mb-3">
                                                <label class="form-label">Damage Remarks</label>
                                                <textarea wire:model="damageRemarks" class="form-control" rows="2" placeholder="Describe the damage..."></textarea>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Guest Feedback -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-star me-2"></i>Guest Feedback</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Rating <span class="text-danger">*</span></label>
                                            <select wire:model="guestRating" class="form-select">
                                                <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                                                <option value="4">⭐⭐⭐⭐ Very Good</option>
                                                <option value="3">⭐⭐⭐ Good</option>
                                                <option value="2">⭐⭐ Fair</option>
                                                <option value="1">⭐ Poor</option>
                                            </select>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Comments</label>
                                            <textarea wire:model="guestFeedback" class="form-control" rows="2" placeholder="Guest feedback or comments..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <!-- Billing Summary -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="ti ti-receipt me-2"></i>Billing Summary</h6>
                                        @if($folioData)
                                            <span class="badge bg-info">Folio: {{ $folioData['folio_no'] }}</span>
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <tr>
                                                    <td>Room Charges:</td>
                                                    <td class="text-end fw-bold">{{ number_format($selectedReservation['total_amount'], 2) }}</td>
                                                </tr>
                                                @if($folioData && isset($folioData['charges']))
                                                    @foreach($folioData['charges'] as $charge)
                                                        @if($charge['charge_type'] === 'additional')
                                                            <tr>
                                                                <td>{{ $charge['description'] }}:</td>
                                                                <td class="text-end">{{ number_format($charge['amount'], 2) }}</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                <tr class="border-top">
                                                    <td class="fw-bold">Total Charges:</td>
                                                    <td class="text-end fw-bold">{{ number_format($folioData['total_charges'] ?? $selectedReservation['total_amount'], 2) }}</td>
                                                </tr>
                                                <tr class="text-success">
                                                    <td>Previous Payments:</td>
                                                    <td class="text-end">({{ number_format($folioData['total_payments'] ?? $selectedReservation['deposit_amount'], 2) }})</td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td class="fw-bold text-danger">Balance Due:</td>
                                                    <td class="text-end fw-bold text-danger h5 mb-0">
                                                        {{ number_format($folioData['balance'] ?? ($selectedReservation['total_amount'] - $selectedReservation['deposit_amount']), 2) }}
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Charges -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-plus-circle me-2"></i>Add Additional Charges</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Amount</label>
                                                <input type="number" wire:model="additionalCharges" class="form-control form-control-sm" step="0.01" placeholder="0.00">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="form-label small">Description</label>
                                                <input type="text" wire:model="chargeDescription" class="form-control form-control-sm" placeholder="e.g., Mini-bar">
                                            </div>
                                            <div class="col-12">
                                                <button wire:click="addAdditionalCharge" class="btn btn-sm btn-outline-primary w-100">
                                                    <i class="ti ti-plus me-1"></i> Add Charge
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Collection -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="ti ti-currency-dollar me-2"></i>Collect Payment</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="paymentAmount" class="form-control @error('paymentAmount') is-invalid @enderror" step="0.01" min="0">
                                            @error('paymentAmount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
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

                                        <div class="mb-0">
                                            <label class="form-label">Actual Check-Out Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" wire:model="actualCheckOutTime" class="form-control @error('actualCheckOutTime') is-invalid @enderror">
                                            @error('actualCheckOutTime')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-warning" wire:click="processCheckOut">
                            <i class="ti ti-check me-1"></i> Complete Check-Out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
