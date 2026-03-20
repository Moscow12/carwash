<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Table Reservations</h4>
            <p class="text-muted mb-0">Manage restaurant table reservations</p>
        </div>
        @if($selectedOutlet)
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Reservation
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

    <!-- Business & Outlet Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                @if($businesses->count() > 1)
                    <div class="col-md-6">
                        <label class="form-label">Business</label>
                        <select wire:model.live="selectedBusiness" class="form-select">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">Outlet</label>
                    <select wire:model.live="selectedOutlet" class="form-select">
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if($selectedOutlet)
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-calendar-event"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Today's Reservations</h6>
                                <h3 class="mb-0">{{ $stats['today'] }}</h3>
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
                                <h6 class="text-muted mb-1">Confirmed</h6>
                                <h3 class="mb-0">{{ $stats['confirmed'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                                <i class="ti ti-users"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Covers Today</h6>
                                <h3 class="mb-0">{{ $stats['total_covers'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs & Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('all')">All</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'pending' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('pending')">Pending</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'confirmed' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('confirmed')">Confirmed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'seated' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('seated')">Seated</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'completed' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('completed')">Completed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'cancelled' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('cancelled')">Cancelled</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'no_show' ? 'active' : '' }}" href="#" wire:click.prevent="switchTab('no_show')">No Show</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by reservation #, guest name, or phone...">
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Reservation #</th>
                                <th>Guest</th>
                                <th>Table</th>
                                <th>Date & Time</th>
                                <th>Covers</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reservations as $reservation)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $reservation->reservation_no }}</div>
                                        @if($reservation->occasion)
                                            <small class="text-muted"><i class="ti ti-gift me-1"></i>{{ $reservation->occasion }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $reservation->guest_name }}</div>
                                        @if($reservation->guest_phone)
                                            <small class="text-muted"><i class="ti ti-phone me-1"></i>{{ $reservation->guest_phone }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservation->table)
                                            <span class="badge bg-secondary">Table {{ $reservation->table->table_number }}</span>
                                        @elseif($reservation->section)
                                            <span class="text-muted">{{ $reservation->section }}</span>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="ti ti-calendar me-1"></i>{{ \Carbon\Carbon::parse($reservation->reservation_date)->format('M d, Y') }}</div>
                                        <small class="text-muted"><i class="ti ti-clock me-1"></i>{{ \Carbon\Carbon::parse($reservation->reservation_time)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $reservation->covers }} {{ Str::plural('person', $reservation->covers) }}</span>
                                    </td>
                                    <td>{{ $reservation->duration_mins }} mins</td>
                                    <td>
                                        @if($reservation->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($reservation->status === 'confirmed')
                                            <span class="badge bg-success">Confirmed</span>
                                        @elseif($reservation->status === 'seated')
                                            <span class="badge bg-primary">Seated</span>
                                        @elseif($reservation->status === 'completed')
                                            <span class="badge bg-secondary">Completed</span>
                                        @elseif($reservation->status === 'cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                        @else
                                            <span class="badge bg-dark">No Show</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($reservation->status === 'pending')
                                                <button wire:click="confirmReservation('{{ $reservation->id }}')" class="btn btn-success" title="Confirm">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            @endif
                                            @if(in_array($reservation->status, ['pending', 'confirmed']))
                                                <button wire:click="seatReservation('{{ $reservation->id }}')" class="btn btn-primary" title="Seat">
                                                    <i class="ti ti-armchair"></i>
                                                </button>
                                                <button wire:click="editReservation('{{ $reservation->id }}')" class="btn btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button wire:click="cancelReservation('{{ $reservation->id }}')" class="btn btn-danger" title="Cancel">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                                <button wire:click="markNoShow('{{ $reservation->id }}')" class="btn btn-secondary" title="No Show">
                                                    <i class="ti ti-user-off"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
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
                <i class="ti ti-building fs-1 text-muted"></i>
                <h5 class="mt-3">Select Outlet</h5>
                <p class="text-muted">Please select an outlet to manage table reservations</p>
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
                            <i class="ti ti-calendar-event me-2"></i>
                            {{ $editMode ? 'Edit Reservation' : 'New Reservation' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveReservation">
                            <div class="row g-3">
                                <!-- Guest Information -->
                                <div class="col-12">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-user me-2"></i>Guest Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Guest Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="guest_name" class="form-control @error('guest_name') is-invalid @enderror" placeholder="John Doe">
                                    @error('guest_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Guest Phone</label>
                                    <input type="text" wire:model="guest_phone" class="form-control @error('guest_phone') is-invalid @enderror" placeholder="+255 712 345 678">
                                    @error('guest_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Linked Customer</label>
                                    <select wire:model="customer_id" class="form-select">
                                        <option value="">-- Link to existing customer (optional) --</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Reservation Details -->
                                <div class="col-12 mt-4">
                                    <h6 class="mb-3 text-primary"><i class="ti ti-list me-2"></i>Reservation Details</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Table</label>
                                    <select wire:model="table_id" class="form-select">
                                        <option value="">-- Select Table (optional) --</option>
                                        @foreach($tables as $table)
                                            <option value="{{ $table->id }}">Table {{ $table->table_number }} ({{ $table->capacity }} seats)</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Section</label>
                                    <input type="text" wire:model="section" class="form-control" placeholder="e.g., Patio, Main Dining">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Number of Covers <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="covers" class="form-control @error('covers') is-invalid @enderror" min="1">
                                    @error('covers')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="reservation_date" class="form-control @error('reservation_date') is-invalid @enderror">
                                    @error('reservation_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Time <span class="text-danger">*</span></label>
                                    <input type="time" wire:model="reservation_time" class="form-control @error('reservation_time') is-invalid @enderror">
                                    @error('reservation_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Duration (minutes) <span class="text-danger">*</span></label>
                                    <select wire:model="duration_mins" class="form-select @error('duration_mins') is-invalid @enderror">
                                        <option value="30">30 minutes</option>
                                        <option value="60">1 hour</option>
                                        <option value="90">1.5 hours</option>
                                        <option value="120">2 hours</option>
                                        <option value="180">3 hours</option>
                                        <option value="240">4 hours</option>
                                    </select>
                                    @error('duration_mins')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Occasion</label>
                                    <input type="text" wire:model="occasion" class="form-control" placeholder="Birthday, Anniversary, etc.">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Deposit Amount</label>
                                    <input type="number" step="0.01" wire:model="deposit_amount" class="form-control" placeholder="0.00">
                                    <small class="text-muted">Optional deposit to secure reservation</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="3" placeholder="Special requests, dietary restrictions, etc."></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>{{ $editMode ? 'Update' : 'Create' }} Reservation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
