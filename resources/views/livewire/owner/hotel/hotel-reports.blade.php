<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Hotel Reports</h4>
            <p class="text-muted mb-0">Analytics and performance reports</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
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
        <!-- Date Range Selector -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">From Date</label>
                        <input type="date" wire:model="dateFrom" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">To Date</label>
                        <input type="date" wire:model="dateTo" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <button wire:click="generateReport" class="btn btn-primary w-100">
                            <i class="ti ti-refresh me-1"></i> Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'occupancy' ? 'active' : '' }}"
                        wire:click="switchTab('occupancy')"
                        type="button">
                    <i class="ti ti-bed me-1"></i> Occupancy
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'revenue' ? 'active' : '' }}"
                        wire:click="switchTab('revenue')"
                        type="button">
                    <i class="ti ti-currency-dollar me-1"></i> Revenue
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'reservations' ? 'active' : '' }}"
                        wire:click="switchTab('reservations')"
                        type="button">
                    <i class="ti ti-calendar-event me-1"></i> Reservations
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'housekeeping' ? 'active' : '' }}"
                        wire:click="switchTab('housekeeping')"
                        type="button">
                    <i class="ti ti-vacuum-cleaner me-1"></i> Housekeeping
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'guest-history' ? 'active' : '' }}"
                        wire:click="switchTab('guest-history')"
                        type="button">
                    <i class="ti ti-users me-1"></i> Guest History
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Occupancy Report -->
            @if($activeTab === 'occupancy')
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Rooms</h6>
                                <h3 class="mb-0">{{ $reportData['total_rooms'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-info border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Period (Days)</h6>
                                <h3 class="mb-0">{{ $reportData['total_days'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-warning border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Room Nights</h6>
                                <h3 class="mb-0">{{ $reportData['occupied_room_nights'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-success border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Occupancy Rate</h6>
                                <h3 class="mb-0">{{ number_format($reportData['occupancy_rate'] ?? 0, 1) }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0">Occupancy by Room Type</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($reportData['by_room_type']) && $reportData['by_room_type']->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Room Type</th>
                                            <th>Total Rooms</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData['by_room_type'] as $type)
                                            <tr>
                                                <td>{{ $type['name'] }}</td>
                                                <td><span class="badge bg-primary">{{ $type['count'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center py-3">No data available</p>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Revenue Report -->
            @if($activeTab === 'revenue')
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Room Revenue</h6>
                                <h3 class="mb-0">{{ number_format($reportData['room_revenue'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-info border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">F&B Revenue</h6>
                                <h3 class="mb-0">{{ number_format($reportData['fb_revenue'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-warning border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Other Revenue</h6>
                                <h3 class="mb-0">{{ number_format($reportData['other_revenue'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-success border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Revenue</h6>
                                <h3 class="mb-0">{{ number_format($reportData['total_revenue'] ?? 0, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3">Revenue Breakdown</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Daily Average:</strong> {{ number_format($reportData['daily_avg'] ?? 0, 2) }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Period:</strong> {{ $dateFrom }} to {{ $dateTo }}</p>
                            </div>
                        </div>
                        <div class="mt-3 text-center py-4 bg-light">
                            <i class="ti ti-chart-pie fs-1 text-muted"></i>
                            <p class="text-muted mt-2">Revenue charts can be displayed here</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reservations Report -->
            @if($activeTab === 'reservations')
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Reservations</h6>
                                <h3 class="mb-0">{{ $reportData['total_reservations'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">By Booking Source</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($reportData['by_source']) && $reportData['by_source']->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Source</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reportData['by_source'] as $source)
                                                <tr>
                                                    <td>{{ $source['source'] }}</td>
                                                    <td><span class="badge bg-info">{{ $source['count'] }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center py-3">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">By Status</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($reportData['by_status']) && $reportData['by_status']->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reportData['by_status'] as $status)
                                                <tr>
                                                    <td>{{ ucfirst($status->status) }}</td>
                                                    <td><span class="badge bg-primary">{{ $status->count }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center py-3">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Housekeeping Report -->
            @if($activeTab === 'housekeeping')
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Tasks</h6>
                                <h3 class="mb-0">{{ $reportData['total_tasks'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-success border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Completed</h6>
                                <h3 class="mb-0">{{ $reportData['completed_tasks'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-warning border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="mb-0">{{ $reportData['pending_tasks'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-start border-info border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Completion Rate</h6>
                                <h3 class="mb-0">{{ number_format($reportData['completion_rate'] ?? 0, 1) }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">By Priority</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($reportData['by_priority']) && $reportData['by_priority']->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Priority</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reportData['by_priority'] as $priority)
                                                <tr>
                                                    <td>{{ ucfirst($priority->priority) }}</td>
                                                    <td><span class="badge bg-secondary">{{ $priority->count }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center py-3">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-3">Performance Metrics</h6>
                                <p><strong>Average Completion Time:</strong>
                                    @if(isset($reportData['avg_completion_time']))
                                        {{ number_format($reportData['avg_completion_time'], 0) }} minutes
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Guest History Report -->
            @if($activeTab === 'guest-history')
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <div class="card shadow-sm border-start border-primary border-4">
                            <div class="card-body">
                                <h6 class="text-muted mb-1">Total Guests</h6>
                                <h3 class="mb-0">{{ $reportData['total_guests'] ?? 0 }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">VIP Level Breakdown</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($reportData['vip_breakdown']) && $reportData['vip_breakdown']->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Level</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reportData['vip_breakdown'] as $vip)
                                                <tr>
                                                    <td>{{ ucfirst($vip->vip_level) }}</td>
                                                    <td><span class="badge bg-warning">{{ $vip->count }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center py-3">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h6 class="mb-0">Top 10 Frequent Guests</h6>
                            </div>
                            <div class="card-body">
                                @if(isset($reportData['top_guests']) && $reportData['top_guests']->count() > 0)
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Guest</th>
                                                <th>Stays</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($reportData['top_guests'] as $guest)
                                                <tr>
                                                    <td>{{ $guest->full_name }}</td>
                                                    <td><span class="badge bg-success">{{ $guest->reservations_count }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-center py-3">No data available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to view reports</p>
            </div>
        </div>
    @endif
</div>
