<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Room Availability Calendar</h4>
            <p class="text-muted mb-0">View and manage room availability by date</p>
        </div>
        <button wire:click="openBulkUpdateModal" class="btn btn-primary">
            <i class="ti ti-calendar-plus me-1"></i> Bulk Update
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
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-building"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Rooms</h6>
                                <h3 class="mb-0">{{ $stats['total_rooms'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-circle-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Available Today</h6>
                                <h3 class="mb-0">{{ $stats['available_today'] }}</h3>
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
                                <i class="ti ti-calendar-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Booked Today</h6>
                                <h3 class="mb-0">{{ $stats['booked_today'] }}</h3>
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
                                <i class="ti ti-chart-line"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Occupancy Today</h6>
                                <h3 class="mb-0">{{ number_format($stats['avg_occupancy'], 1) }}%</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Navigation -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Filter by Room Type</label>
                        <select wire:model.live="selectedRoomType" class="form-select">
                            <option value="">All Room Types</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} ({{ $type->rooms_count }} rooms)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <button wire:click="previousMonth" class="btn btn-outline-primary">
                                <i class="ti ti-chevron-left"></i> Previous
                            </button>
                            <h5 class="mb-0">{{ $monthName }}</h5>
                            <button wire:click="nextMonth" class="btn btn-outline-primary">
                                Next <i class="ti ti-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3">Availability Legend</h6>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded me-2" style="width: 20px; height: 20px; opacity: 0.8;"></div>
                        <small>High Availability (80%+)</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-warning rounded me-2" style="width: 20px; height: 20px; opacity: 0.8;"></div>
                        <small>Medium Availability (40-79%)</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-danger rounded me-2" style="width: 20px; height: 20px; opacity: 0.8;"></div>
                        <small>Low Availability (0-39%)</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary rounded me-2" style="width: 20px; height: 20px; opacity: 0.3;"></div>
                        <small>Past Dates</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Availability Calendar -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0" style="font-size: 0.85rem;">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="min-width: 150px; position: sticky; left: 0; z-index: 10; background: #f8f9fa;">Room Type</th>
                                @for($day = 1; $day <= 31; $day++)
                                    @if($day <= \Carbon\Carbon::create($currentYear, $currentMonth, 1)->daysInMonth)
                                        <th class="text-center" style="min-width: 60px;">
                                            <div>{{ $day }}</div>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::create($currentYear, $currentMonth, $day)->format('D') }}
                                            </small>
                                        </th>
                                    @endif
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($calendarData as $roomTypeData)
                                <tr>
                                    <td style="position: sticky; left: 0; z-index: 5; background: white;">
                                        <strong>{{ $roomTypeData['name'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $roomTypeData['total_rooms'] }} rooms</small>
                                    </td>
                                    @foreach($roomTypeData['days'] as $dayData)
                                        @php
                                            $availabilityPct = $roomTypeData['total_rooms'] > 0
                                                ? ($dayData['available'] / $roomTypeData['total_rooms']) * 100
                                                : 0;

                                            if ($dayData['is_past']) {
                                                $bgClass = 'bg-secondary';
                                                $opacity = 'opacity-25';
                                            } elseif ($availabilityPct >= 80) {
                                                $bgClass = 'bg-success';
                                                $opacity = 'opacity-75';
                                            } elseif ($availabilityPct >= 40) {
                                                $bgClass = 'bg-warning';
                                                $opacity = 'opacity-75';
                                            } else {
                                                $bgClass = 'bg-danger';
                                                $opacity = 'opacity-75';
                                            }

                                            $borderClass = $dayData['is_today'] ? 'border-primary border-3' : '';
                                        @endphp
                                        <td class="text-center {{ $bgClass }} {{ $opacity }} {{ $borderClass }} p-2"
                                            title="Date: {{ $dayData['date'] }}, Available: {{ $dayData['available'] }}, Occupied: {{ $dayData['occupied'] }}">
                                            <div class="fw-bold" style="font-size: 0.9rem;">
                                                {{ $dayData['available'] }}
                                            </div>
                                            <small class="d-block" style="font-size: 0.7rem;">
                                                @if(!$dayData['is_past'])
                                                    {{ number_format($availabilityPct, 0) }}%
                                                @else
                                                    -
                                                @endif
                                            </small>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="32" class="text-center py-5">
                                        <i class="ti ti-calendar-x fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No room types found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mt-4">
            @foreach($calendarData as $roomTypeData)
                @php
                    $futureDays = collect($roomTypeData['days'])->filter(fn($d) => !$d['is_past']);
                    $avgAvailability = $futureDays->avg(fn($d) => $roomTypeData['total_rooms'] > 0 ? ($d['available'] / $roomTypeData['total_rooms']) * 100 : 0);
                    $totalAvailable = $futureDays->sum('available');
                    $totalOccupied = $futureDays->sum('occupied');
                @endphp
                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h6 class="text-primary mb-3">{{ $roomTypeData['name'] }}</h6>
                            <div class="row">
                                <div class="col-6">
                                    <p class="mb-1"><small class="text-muted">Total Rooms</small></p>
                                    <h5>{{ $roomTypeData['total_rooms'] }}</h5>
                                </div>
                                <div class="col-6">
                                    <p class="mb-1"><small class="text-muted">Avg Availability</small></p>
                                    <h5>{{ number_format($avgAvailability, 0) }}%</h5>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $avgAvailability }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Information Panel -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>How to Use</h6>
                <ul class="mb-0">
                    <li>The calendar shows availability for each room type across the month</li>
                    <li>Numbers in cells represent available rooms for that day</li>
                    <li>Colors indicate availability percentage: Green (high), Yellow (medium), Red (low)</li>
                    <li>Click on cells to view detailed information (future enhancement)</li>
                    <li>Use "Bulk Update" to adjust availability or pricing for multiple dates</li>
                    <li>Today's date is highlighted with a blue border</li>
                </ul>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to view availability</p>
            </div>
        </div>
    @endif

    <!-- Bulk Update Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-calendar-plus me-2"></i>
                            Bulk Availability Update
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeBulkUpdateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="bulkUpdate">
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <small>Update availability and pricing for multiple dates at once</small>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">From Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="dateFrom" class="form-control @error('dateFrom') is-invalid @enderror">
                                    @error('dateFrom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">To Date <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="dateTo" class="form-control @error('dateTo') is-invalid @enderror">
                                    @error('dateTo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Available Rooms</label>
                                    <input type="number" wire:model="bulkRooms" class="form-control" min="0" placeholder="Leave empty to skip">
                                    <small class="text-muted">Number of rooms to make available</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Price Override</label>
                                    <input type="number" wire:model="bulkPrice" class="form-control" step="0.01" min="0" placeholder="Leave empty to skip">
                                    <small class="text-muted">Special price for this period</small>
                                </div>
                            </div>

                            <div class="alert alert-warning mt-3">
                                <small><strong>Note:</strong> This feature requires additional implementation for inventory management. Currently shows UI only.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeBulkUpdateModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="bulkUpdate">
                            <i class="ti ti-device-floppy me-1"></i> Update Availability
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .table-bordered td, .table-bordered th {
            border: 1px solid #dee2e6 !important;
        }
    </style>
</div>
