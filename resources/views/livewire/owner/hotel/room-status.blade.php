<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Room Status Dashboard</h4>
            <p class="text-muted mb-0">Visual overview of all room statuses</p>
        </div>
        <div>
            <span class="badge bg-light text-dark me-1">
                <i class="ti ti-refresh"></i> Auto-refresh
            </span>
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
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-2">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="ti ti-building fs-2 text-primary"></i>
                        <h3 class="mb-0 mt-2">{{ $stats['total'] }}</h3>
                        <small class="text-muted">Total Rooms</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm text-center border-start border-success border-4">
                    <div class="card-body">
                        <i class="ti ti-circle-check fs-2 text-success"></i>
                        <h3 class="mb-0 mt-2">{{ $stats['available'] }}</h3>
                        <small class="text-muted">Available</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm text-center border-start border-danger border-4">
                    <div class="card-body">
                        <i class="ti ti-user fs-2 text-danger"></i>
                        <h3 class="mb-0 mt-2">{{ $stats['occupied'] }}</h3>
                        <small class="text-muted">Occupied</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm text-center border-start border-info border-4">
                    <div class="card-body">
                        <i class="ti ti-vacuum-cleaner fs-2 text-info"></i>
                        <h3 class="mb-0 mt-2">{{ $stats['cleaning'] }}</h3>
                        <small class="text-muted">Cleaning</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm text-center border-start border-warning border-4">
                    <div class="card-body">
                        <i class="ti ti-tool fs-2 text-warning"></i>
                        <h3 class="mb-0 mt-2">{{ $stats['maintenance'] }}</h3>
                        <small class="text-muted">Maintenance</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm text-center border-start border-secondary border-4">
                    <div class="card-body">
                        <i class="ti ti-x fs-2 text-secondary"></i>
                        <h3 class="mb-0 mt-2">{{ $stats['out_of_order'] }}</h3>
                        <small class="text-muted">Out of Order</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search Room Number</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search room number...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                            <option value="cleaning">Cleaning</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="out_of_order">Out of Order</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Room Type</label>
                        <select wire:model.live="filterRoomType" class="form-select">
                            <option value="">All Types</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Legend -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h6 class="mb-3">Status Legend</h6>
                <div class="d-flex flex-wrap gap-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-success rounded me-2" style="width: 20px; height: 20px;"></div>
                        <small>Available</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-danger rounded me-2" style="width: 20px; height: 20px;"></div>
                        <small>Occupied</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-info rounded me-2" style="width: 20px; height: 20px;"></div>
                        <small>Cleaning</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-warning rounded me-2" style="width: 20px; height: 20px;"></div>
                        <small>Maintenance</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary rounded me-2" style="width: 20px; height: 20px;"></div>
                        <small>Out of Order</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Grid -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    @forelse($rooms as $room)
                        <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                            <div class="card h-100 shadow-sm border-start border-{{
                                $room->status === 'available' ? 'success' :
                                ($room->status === 'occupied' ? 'danger' :
                                ($room->status === 'cleaning' ? 'info' :
                                ($room->status === 'maintenance' ? 'warning' : 'secondary')))
                            }} border-4">
                                <div class="card-body p-3">
                                    <!-- Room Number -->
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="mb-0">{{ $room->number }}</h5>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                @if($room->status === 'available')
                                                    <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'cleaning')">
                                                        <i class="ti ti-vacuum-cleaner me-1"></i> Mark Cleaning
                                                    </a></li>
                                                    <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'maintenance')">
                                                        <i class="ti ti-tool me-1"></i> Maintenance
                                                    </a></li>
                                                @elseif($room->status === 'cleaning')
                                                    <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'available')">
                                                        <i class="ti ti-check me-1"></i> Mark Available
                                                    </a></li>
                                                @elseif($room->status === 'maintenance')
                                                    <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'available')">
                                                        <i class="ti ti-check me-1"></i> Mark Available
                                                    </a></li>
                                                    <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'out_of_order')">
                                                        <i class="ti ti-x me-1"></i> Out of Order
                                                    </a></li>
                                                @elseif($room->status === 'out_of_order')
                                                    <li><a class="dropdown-item" wire:click="changeRoomStatus('{{ $room->id }}', 'maintenance')">
                                                        <i class="ti ti-tool me-1"></i> To Maintenance
                                                    </a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Room Type -->
                                    <div class="mb-2">
                                        <small class="text-muted">{{ $room->roomType->name }}</small>
                                    </div>

                                    <!-- Status Badge -->
                                    <div class="mb-2">
                                        @php
                                            $statusColors = [
                                                'available' => 'success',
                                                'occupied' => 'danger',
                                                'cleaning' => 'info',
                                                'maintenance' => 'warning',
                                                'out_of_order' => 'secondary',
                                            ];
                                            $statusIcons = [
                                                'available' => 'ti-circle-check',
                                                'occupied' => 'ti-user',
                                                'cleaning' => 'ti-vacuum-cleaner',
                                                'maintenance' => 'ti-tool',
                                                'out_of_order' => 'ti-x',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$room->status] ?? 'secondary' }} w-100">
                                            <i class="ti {{ $statusIcons[$room->status] ?? 'ti-circle' }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $room->status)) }}
                                        </span>
                                    </div>

                                    <!-- Guest Info (if occupied) -->
                                    @if($room->status === 'occupied' && $room->currentReservation)
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-muted d-block">
                                                <i class="ti ti-user me-1"></i>
                                                {{ $room->currentReservation->guest->full_name ?? 'Guest' }}
                                            </small>
                                            @if($room->currentReservation->check_out)
                                                <small class="text-muted d-block">
                                                    <i class="ti ti-calendar me-1"></i>
                                                    Out: {{ $room->currentReservation->check_out->format('M d') }}
                                                </small>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Floor Info -->
                                    @if($room->floor)
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="ti ti-building me-1"></i>Floor {{ $room->floor }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="ti ti-bed fs-1 text-muted"></i>
                                <p class="text-muted mt-2">No rooms found</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Quick Actions Panel -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="mb-3"><i class="ti ti-bolt me-2"></i>Quick Actions</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-success" wire:click="$set('filterStatus', 'available')">
                        <i class="ti ti-filter me-1"></i> Show Available
                    </button>
                    <button class="btn btn-sm btn-outline-danger" wire:click="$set('filterStatus', 'occupied')">
                        <i class="ti ti-filter me-1"></i> Show Occupied
                    </button>
                    <button class="btn btn-sm btn-outline-info" wire:click="$set('filterStatus', 'cleaning')">
                        <i class="ti ti-filter me-1"></i> Show Cleaning
                    </button>
                    <button class="btn btn-sm btn-outline-warning" wire:click="$set('filterStatus', 'maintenance')">
                        <i class="ti ti-filter me-1"></i> Show Maintenance
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$set('filterStatus', '')">
                        <i class="ti ti-x me-1"></i> Clear Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Occupancy Percentage -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="mb-3">Occupancy Overview</h6>
                <div class="row">
                    <div class="col-md-6">
                        @php
                            $occupancyRate = $stats['total'] > 0 ? ($stats['occupied'] / $stats['total']) * 100 : 0;
                        @endphp
                        <h3 class="text-primary">{{ number_format($occupancyRate, 1) }}%</h3>
                        <p class="text-muted mb-0">Current Occupancy Rate</p>
                    </div>
                    <div class="col-md-6">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $occupancyRate }}%">
                                {{ $stats['occupied'] }} / {{ $stats['total'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to view room status</p>
            </div>
        </div>
    @endif
</div>
