<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Guest Directory</h4>
            <p class="text-muted mb-0">Manage guest information and profiles</p>
        </div>
        <a href="{{ route('owner.hotel.reservations') }}" class="btn btn-primary">
             Reservations
        </a>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Guest
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
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-users"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Guests</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-crown"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">VIP Guests</h6>
                                <h3 class="mb-0">{{ $stats['vip'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-danger text-white me-3">
                                <i class="ti ti-ban"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Blacklisted</h6>
                                <h3 class="mb-0">{{ $stats['blacklisted'] }}</h3>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, email, or phone...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">VIP Level</label>
                        <select wire:model.live="vipFilter" class="form-select">
                            <option value="">All Levels</option>
                            <option value="standard">Standard</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select wire:model.live="statusFilter" class="form-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Guests Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Guest</th>
                                <th>Contact</th>
                                <th>VIP Level</th>
                                <th>Loyalty Points</th>
                                <th>Stays</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($guests as $guest)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-primary me-2">
                                                <i class="ti ti-user"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $guest->full_name }}</div>
                                                @if($guest->nationality)
                                                    <small class="text-muted">{{ $guest->nationality }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="ti ti-mail me-1"></i>{{ $guest->email }}</div>
                                        <div><i class="ti ti-phone me-1"></i>{{ $guest->phone }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $vipColors = [
                                                'standard' => 'secondary',
                                                'silver' => 'secondary',
                                                'gold' => 'warning',
                                                'platinum' => 'primary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $vipColors[$guest->vip_level] ?? 'secondary' }}">
                                            {{ ucfirst($guest->vip_level) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $guest->loyalty_points }} pts</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $guest->reservations_count }}</span>
                                    </td>
                                    <td>
                                        @if($guest->blacklisted)
                                            <span class="badge bg-danger">Blacklisted</span>
                                        @else
                                            <span class="badge bg-{{ $guest->status === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($guest->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button wire:click="openModalReservation('{{ $guest->id }}')" class="btn btn-sm btn-outline-primary" title="Booking">
                                                <i class="ti ti-plus me-1"></i> Booking
                                            </button>
                                            <button wire:click="editGuest('{{ $guest->id }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button wire:click="toggleBlacklist('{{ $guest->id }}')" class="btn btn-sm btn-outline-{{ $guest->blacklisted ? 'success' : 'danger' }}" title="{{ $guest->blacklisted ? 'Remove from Blacklist' : 'Add to Blacklist' }}">
                                                <i class="ti ti-ban"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="ti ti-users fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No guests found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $guests->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage guests</p>
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
                            <i class="ti ti-user me-2"></i>
                            {{ $editMode ? 'Edit Guest' : 'Add New Guest' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Message in Modal -->
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Basic Information -->
                                <div class="col-12"><h6 class="text-primary">Basic Information</h6></div>

                                <div class="col-md-6">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="first_name" class="form-control @error('first_name') is-invalid @enderror" required>
                                    @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="last_name" class="form-control @error('last_name') is-invalid @enderror" required>
                                    @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model.defer="email" class="form-control @error('email') is-invalid @enderror" placeholder="Optional">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="text" wire:model.defer="phone" class="form-control @error('phone') is-invalid @enderror" required>
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Personal Details -->
                                <div class="col-12 mt-4"><h6 class="text-primary">Personal Details</h6></div>

                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select wire:model="gender" class="form-select">
                                        <option value="">Not Specified</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" wire:model="date_of_birth" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" wire:model="nationality" class="form-control" placeholder="e.g., Tanzanian">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Country</label>
                                    <select wire:model="country" class="form-select">
                                        <option value="">-- Select Country --</option>
                                        @foreach($countries as $countryName)
                                            <option value="{{ $countryName }}">{{ $countryName }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Coming From</label>
                                    <input type="text" wire:model="coming_from" class="form-control" placeholder="e.g., Dar es Salaam">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Going To</label>
                                    <input type="text" wire:model="going_to" class="form-control" placeholder="e.g., Arusha">
                                </div>

                                <!-- ID Information -->
                                <div class="col-md-6">
                                    <label class="form-label">ID Type</label>
                                    <select wire:model.defer="id_type" class="form-select">
                                        <option value="">Select Type</option>
                                        <option value="passport">Passport</option>
                                        <option value="national_id">National ID</option>
                                        <option value="drivers_license">Driver's License</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">ID Number</label>
                                    <input type="text" wire:model.defer="id_number" class="form-control">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <textarea wire:model="address" class="form-control" rows="2"></textarea>
                                </div>

                                <!-- VIP & Status -->
                                <div class="col-12 mt-4"><h6 class="text-primary">VIP & Status</h6></div>

                                <div class="col-md-4">
                                    <label class="form-label">VIP Level</label>
                                    <select wire:model.defer="vip_level" class="form-select">
                                        <option value="standard">Standard</option>
                                        <option value="silver">Silver</option>
                                        <option value="gold">Gold</option>
                                        <option value="platinum">Platinum</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Loyalty Points</label>
                                    <input type="number" wire:model.defer="loyalty_points" class="form-control" min="0" placeholder="0">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select wire:model.defer="status" class="form-select">
                                        <option value="">Active (Default)</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>

                                <!-- Blacklist -->
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" wire:model="blacklisted" id="blacklisted">
                                        <label class="form-check-label text-danger" for="blacklisted">
                                            Blacklist this guest
                                        </label>
                                    </div>
                                </div>

                                @if($blacklisted)
                                    <div class="col-12">
                                        <label class="form-label">Blacklist Reason</label>
                                        <textarea wire:model="blacklist_reason" class="form-control" rows="2" placeholder="Reason for blacklisting..."></textarea>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <i class="ti ti-device-floppy me-1"></i>
                                {{ $editMode ? 'Update Guest' : 'Save Guest' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Reservation Modal -->
    @if($showReservationModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="ti ti-calendar-plus me-2"></i>
                            Quick Reservation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeReservationModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Error Message in Reservation Modal -->
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form wire:submit.prevent="saveReservation">
                            <div class="row g-3">
                                <!-- Guest Info Display -->
                                @php
                                    $selectedGuest = $guests->firstWhere('id', $selectedGuestForReservation);
                                @endphp
                                @if($selectedGuest)
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <strong><i class="ti ti-user me-1"></i> Guest:</strong> {{ $selectedGuest->full_name }}
                                            <br>
                                            <small><i class="ti ti-phone me-1"></i> {{ $selectedGuest->phone }} | <i class="ti ti-mail me-1"></i> {{ $selectedGuest->email }}</small>
                                        </div>
                                    </div>
                                @endif

                                <!-- Room Type -->
                                <div class="col-md-6">
                                    <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                    <select wire:model="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                        <option value="">Select Room Type</option>
                                        @foreach($roomTypes as $roomType)
                                            <option value="{{ $roomType->id }}">{{ $roomType->name }} - {{ $roomType->base_occupancy }} pax</option>
                                        @endforeach
                                    </select>
                                    @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Rate Plan -->
                                <div class="col-md-6">
                                    <label class="form-label">Rate Plan</label>
                                    <select wire:model="rate_plan_id" class="form-select">
                                        <option value="">Default Rate</option>
                                        @foreach($ratePlans as $ratePlan)
                                            <option value="{{ $ratePlan->id }}">{{ $ratePlan->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Check-in Date -->
                                <div class="col-md-6">
                                    <label class="form-label">Check-in Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="check_in_date" class="form-control @error('check_in_date') is-invalid @enderror">
                                    @error('check_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Check-out Date -->
                                <div class="col-md-6">
                                    <label class="form-label">Check-out Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="check_out_date" class="form-control @error('check_out_date') is-invalid @enderror">
                                    @error('check_out_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Adults -->
                                <div class="col-md-4">
                                    <label class="form-label">Adults <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="adults" class="form-control @error('adults') is-invalid @enderror" min="1">
                                    @error('adults')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Children -->
                                <div class="col-md-4">
                                    <label class="form-label">Children</label>
                                    <input type="number" wire:model="children" class="form-control" min="0">
                                </div>

                                <!-- Number of Rooms -->
                                <div class="col-md-4">
                                    <label class="form-label">Rooms <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="number_of_rooms" class="form-control @error('number_of_rooms') is-invalid @enderror" min="1">
                                    @error('number_of_rooms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <!-- Room Rate -->
                                <div class="col-md-6">
                                    <label class="form-label">Room Rate per Night</label>
                                    <input type="number" wire:model="room_rate" class="form-control" min="0" step="0.01">
                                </div>

                                <!-- Deposit Amount -->
                                <div class="col-md-6">
                                    <label class="form-label">Deposit Amount</label>
                                    <input type="number" wire:model="deposit_amount" class="form-control" min="0" step="0.01">
                                </div>

                                <!-- Booking Source -->
                                <div class="col-12">
                                    <label class="form-label">Booking Source</label>
                                    <select wire:model="source_id" class="form-select">
                                        <option value="">Walk-in</option>
                                        @foreach($bookingSources as $source)
                                            <option value="{{ $source->id }}">{{ $source->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Special Requests -->
                                <div class="col-12">
                                    <label class="form-label">Special Requests</label>
                                    <textarea wire:model="special_requests" class="form-control" rows="2" placeholder="Any special requirements..."></textarea>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeReservationModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="saveReservation" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveReservation">
                                <i class="ti ti-check me-1"></i> Create Reservation
                            </span>
                            <span wire:loading wire:target="saveReservation">
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                Creating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
