<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Reservations Management</h4>
            <p class="text-muted mb-0">Manage hotel reservations and bookings</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Reservation
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
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-calendar-event"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Reservations</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
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
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Confirmed</h6>
                                <h3 class="mb-0">{{ $stats['confirmed'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-user-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Checked In</h6>
                                <h3 class="mb-0">{{ $stats['checked_in'] }}</h3>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Reservation No, Guest name...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="checked_in">Checked In</option>
                            <option value="checked_out">Checked Out</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="no_show">No Show</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" wire:model.live="dateFrom" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" wire:model.live="dateTo" class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Reservation No</th>
                                <th>Guest</th>
                                <th>Room Type</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Nights</th>
                                <th>Guests</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $reservation)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $reservation->reservation_no }}</span>
                                        @if($reservation->source)
                                            <br><small class="text-muted"><i class="ti ti-source me-1"></i>{{ $reservation->source->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $reservation->guest->full_name }}</div>
                                        @if($reservation->guest->phone)
                                            <small class="text-muted"><i class="ti ti-phone me-1"></i>{{ $reservation->guest->phone }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $reservation->roomType->name }}</td>
                                    <td>{{ $reservation->check_in_date->format('M d, Y') }}</td>
                                    <td>{{ $reservation->check_out_date->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $reservation->total_nights }}</span>
                                    </td>
                                    <td>
                                        <i class="ti ti-users me-1"></i>
                                        {{ $reservation->adults }}
                                        @if($reservation->children > 0)
                                            + {{ $reservation->children }} <small>(kids)</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ number_format($reservation->total_amount, 2) }}</div>
                                        @if($reservation->deposit_amount > 0)
                                            <small class="text-success">Deposit: {{ number_format($reservation->deposit_amount, 2) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'confirmed' => 'success',
                                                'checked_in' => 'info',
                                                'checked_out' => 'secondary',
                                                'cancelled' => 'danger',
                                                'no_show' => 'dark',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$reservation->status] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($reservation->status === 'pending')
                                                <button wire:click="confirmReservation('{{ $reservation->id }}')"
                                                        class="btn btn-sm btn-outline-success"
                                                        title="Confirm">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            @endif
                                            <button wire:click="editReservation('{{ $reservation->id }}')"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            @if(in_array($reservation->status, ['pending', 'confirmed']))
                                                <button wire:click="cancelReservation('{{ $reservation->id }}')"
                                                        wire:confirm="Are you sure you want to cancel this reservation?"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Cancel">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="ti ti-calendar-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No reservations found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $reservations->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage reservations</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-calendar-event me-2"></i>
                            {{ $editMode ? 'Edit Reservation' : 'New Reservation' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Guest Selection -->
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-user me-2"></i>Guest Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Guest <span class="text-danger">*</span></label>
                                    <select wire:model="guest_id" class="form-select @error('guest_id') is-invalid @enderror">
                                        <option value="">-- Select Guest --</option>
                                        @foreach($guests as $guest)
                                            <option value="{{ $guest->id }}">{{ $guest->full_name }} - {{ $guest->phone }}</option>
                                        @endforeach
                                    </select>
                                    @error('guest_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Don't see the guest? <a href="{{ route('owner.hotel.guests') }}" target="_blank">Add new guest</a></small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Booking Source</label>
                                    <select wire:model="source_id" class="form-select">
                                        <option value="">-- Walk-in --</option>
                                        @foreach($bookingSources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Room Details -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-bed me-2"></i>Room Details</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                    <select wire:model.live="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                        <option value="">-- Select Room Type --</option>
                                        @foreach($roomTypes as $roomType)
                                            <option value="{{ $roomType->id }}">
                                                {{ $roomType->name }} - {{ number_format($roomType->base_price, 2) }}/night
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('room_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Rate Plan</label>
                                    <select wire:model.live="rate_plan_id" class="form-select">
                                        <option value="">-- Select Rate Plan (Optional) --</option>
                                        @foreach($ratePlans as $ratePlan)
                                            <option value="{{ $ratePlan->id }}">
                                                {{ $ratePlan->name }} - {{ number_format($ratePlan->rate_amount, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Stay Dates -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-calendar me-2"></i>Stay Details</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Check-In Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model.live="check_in_date" class="form-control @error('check_in_date') is-invalid @enderror">
                                    @error('check_in_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Check-Out Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model.live="check_out_date" class="form-control @error('check_out_date') is-invalid @enderror">
                                    @error('check_out_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Total Nights</label>
                                    <input type="text" value="{{ $total_nights }}" class="form-control" readonly>
                                </div>

                                <!-- Guest Count -->
                                <div class="col-md-3">
                                    <label class="form-label">Adults <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="adults" class="form-control @error('adults') is-invalid @enderror" min="1" max="10">
                                    @error('adults')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Children</label>
                                    <input type="number" wire:model="children" class="form-control @error('children') is-invalid @enderror" min="0" max="10">
                                    @error('children')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Pricing -->
                                <div class="col-md-3">
                                    <label class="form-label">Room Rate/Night <span class="text-danger">*</span></label>
                                    <input type="number" wire:model.live="room_rate" class="form-control @error('room_rate') is-invalid @enderror" step="0.01" min="0">
                                    @error('room_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Total Amount</label>
                                    <input type="text" value="{{ number_format($total_amount, 2) }}" class="form-control fw-bold" readonly>
                                </div>

                                <!-- Payment & Status -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-currency-dollar me-2"></i>Payment & Status</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Deposit Amount</label>
                                    <input type="number" wire:model="deposit_amount" class="form-control" step="0.01" min="0">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Balance Due</label>
                                    <input type="text" value="{{ number_format(max(0, $total_amount - $deposit_amount), 2) }}" class="form-control text-danger fw-bold" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="checked_in">Checked In</option>
                                        <option value="checked_out">Checked Out</option>
                                        <option value="cancelled">Cancelled</option>
                                        <option value="no_show">No Show</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Notes -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-note me-2"></i>Additional Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Special Requests</label>
                                    <textarea wire:model="special_requests" class="form-control" rows="3" placeholder="Guest special requests (e.g., early check-in, extra bed)"></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Internal Notes</label>
                                    <textarea wire:model="internal_notes" class="form-control" rows="3" placeholder="Internal staff notes"></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Reservation' : 'Create Reservation' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
