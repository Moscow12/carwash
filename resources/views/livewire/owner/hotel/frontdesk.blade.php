<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Front Desk Dashboard</h4>
            <p class="text-muted mb-0">Overview of hotel operations</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="$refresh" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-refresh me-1"></i> Refresh
            </button>
            <a href="{{ route('owner.hotel.reservations') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-plus me-1"></i> New Reservation
            </a>
        </div>
    </div>

    <!-- Hotel & Branch Selection -->
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
        <!-- KPI Metrics -->
        <div class="row g-3 mb-4">
            <!-- Occupancy Rate -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-gradient-primary text-white rounded-circle">
                                    <i class="ti ti-percentage fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Occupancy Rate</h6>
                                <h2 class="mb-0">{{ $occupancyRate }}%</h2>
                                <small class="text-muted">{{ $occupiedRooms }}/{{ $totalRooms }} rooms</small>
                            </div>
                        </div>
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $occupancyRate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Rooms -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-gradient-success text-white rounded-circle">
                                    <i class="ti ti-door fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Available Rooms</h6>
                                <h2 class="mb-0">{{ $availableRooms }}</h2>
                                <small class="text-success"><i class="ti ti-check-circle"></i> Ready to assign</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-ins Today -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-gradient-info text-white rounded-circle">
                                    <i class="ti ti-login fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Arrivals Today</h6>
                                <h2 class="mb-0">{{ $checkingInToday }}</h2>
                                <small class="text-info"><i class="ti ti-calendar-event"></i> Expected check-ins</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-outs Today -->
            <div class="col-xl-3 col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-lg bg-gradient-warning text-white rounded-circle">
                                    <i class="ti ti-logout fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="text-muted mb-1">Departures Today</h6>
                                <h2 class="mb-0">{{ $checkingOutToday }}</h2>
                                <small class="text-warning"><i class="ti ti-calendar-x"></i> Expected check-outs</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Status Grid -->
        <div class="row g-3 mb-4">
            <div class="col-xl-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="ti ti-layout-grid me-2"></i>Room Status Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @php
                                $statusConfig = [
                                    'available' => ['color' => 'success', 'icon' => 'ti-check-circle', 'label' => 'Available'],
                                    'occupied' => ['color' => 'danger', 'icon' => 'ti-user-check', 'label' => 'Occupied'],
                                    'cleaning' => ['color' => 'warning', 'icon' => 'ti-vacuum-cleaner', 'label' => 'Cleaning'],
                                    'maintenance' => ['color' => 'info', 'icon' => 'ti-tool', 'label' => 'Maintenance'],
                                    'out_of_order' => ['color' => 'dark', 'icon' => 'ti-x-circle', 'label' => 'Out of Order'],
                                ];
                            @endphp
                            @foreach($statusConfig as $status => $config)
                                <div class="col-md-4 col-sm-6">
                                    <div class="d-flex align-items-center p-3 border rounded bg-light">
                                        <div class="flex-shrink-0">
                                            <div class="avatar bg-{{ $config['color'] }} text-white">
                                                <i class="{{ $config['icon'] }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-0">{{ $config['label'] }}</h6>
                                            <h4 class="mb-0 text-{{ $config['color'] }}">
                                                {{ $roomsByStatus[$status] ?? 0 }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-3 text-center">
                            <a href="{{ route('owner.hotel.room-status') }}" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-eye me-1"></i> View Detailed Room Status
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="col-xl-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="ti ti-chart-bar me-2"></i>Quick Stats</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <p class="text-muted mb-0">Pending Reservations</p>
                                <h4 class="mb-0">{{ $pendingReservations }}</h4>
                            </div>
                            <div class="avatar bg-warning text-white">
                                <i class="ti ti-clock"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <p class="text-muted mb-0">Total Rooms</p>
                                <h4 class="mb-0">{{ $totalRooms }}</h4>
                            </div>
                            <div class="avatar bg-primary text-white">
                                <i class="ti ti-bed"></i>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-0">Occupied Rooms</p>
                                <h4 class="mb-0">{{ $occupiedRooms }}</h4>
                            </div>
                            <div class="avatar bg-danger text-white">
                                <i class="ti ti-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Sections -->
        <div class="row g-3">
            <!-- Upcoming Arrivals -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ti ti-login text-info me-2"></i>Today's Arrivals</h5>
                        <span class="badge bg-info">{{ count($upcomingArrivals) }}</span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($upcomingArrivals as $arrival)
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-light text-primary">
                                        <i class="ti ti-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $arrival['guest']['full_name'] ?? 'N/A' }}</h6>
                                    <small class="text-muted">
                                        <i class="ti ti-bed me-1"></i>{{ $arrival['room_type']['name'] ?? 'N/A' }}
                                        | <i class="ti ti-calendar me-1"></i>{{ $arrival['total_nights'] ?? 0 }} night(s)
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button wire:click="quickCheckIn('{{ $arrival['id'] }}')"
                                            class="btn btn-sm btn-outline-info">
                                        <i class="ti ti-login"></i> Check-In
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="ti ti-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No arrivals today</p>
                            </div>
                        @endforelse
                    </div>
                    @if(count($upcomingArrivals) > 0)
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('owner.hotel.reservations') }}" class="text-decoration-none">
                                View All Reservations <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upcoming Departures -->
            <div class="col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ti ti-logout text-warning me-2"></i>Today's Departures</h5>
                        <span class="badge bg-warning">{{ count($upcomingDepartures) }}</span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($upcomingDepartures as $departure)
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-light text-warning">
                                        <i class="ti ti-user"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $departure['guest']['full_name'] ?? 'N/A' }}</h6>
                                    <small class="text-muted">
                                        <i class="ti ti-door me-1"></i>Room {{ $departure['room_allocation']['room']['number'] ?? 'N/A' }}
                                        | <i class="ti ti-bed me-1"></i>{{ $departure['room_type']['name'] ?? 'N/A' }}
                                    </small>
                                </div>
                                <div class="flex-shrink-0">
                                    <button wire:click="quickCheckOut('{{ $departure['id'] }}')"
                                            class="btn btn-sm btn-outline-warning">
                                        <i class="ti ti-logout"></i> Check-Out
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="ti ti-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No departures today</p>
                            </div>
                        @endforelse
                    </div>
                    @if(count($upcomingDepartures) > 0)
                        <div class="card-footer bg-white text-center">
                            <a href="{{ route('owner.hotel.checkout') }}" class="text-decoration-none">
                                View All Check-Outs <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Check-ins -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ti ti-history text-success me-2"></i>Recent Check-Ins (Last 24h)</h5>
                        <span class="badge bg-success">{{ count($recentCheckIns) }}</span>
                    </div>
                    <div class="card-body p-0">
                        @forelse($recentCheckIns as $checkIn)
                            <div class="d-flex align-items-center p-3 border-bottom">
                                <div class="flex-shrink-0">
                                    <div class="avatar bg-light text-success">
                                        <i class="ti ti-user-check"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-0">{{ $checkIn['guest']['full_name'] ?? 'N/A' }}</h6>
                                            <small class="text-muted">
                                                <i class="ti ti-bed me-1"></i>{{ $checkIn['room_type']['name'] ?? 'N/A' }}
                                                | <i class="ti ti-calendar me-1"></i>{{ $checkIn['total_nights'] ?? 0 }} night(s)
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="ti ti-clock"></i>
                                            {{ \Carbon\Carbon::parse($checkIn['updated_at'])->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="badge bg-success-subtle text-success">Checked In</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <i class="ti ti-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No recent check-ins</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- No Hotel Selected -->
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to view the front desk dashboard</p>
                <a href="{{ route('owner.hotels') }}" class="btn btn-primary">
                    <i class="ti ti-plus me-1"></i> Add Hotel
                </a>
            </div>
        </div>
    @endif
</div>
