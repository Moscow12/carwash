<div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Staff Commissions Report</h4>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <!-- Location Selector -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="ti ti-map-pin me-1"></i>
                    @php
                        $selectedCarwash = collect($carwashes)->firstWhere('id', $carwash_id);
                    @endphp
                    {{ $selectedCarwash['name'] ?? 'Select Location' }}
                </button>
                <ul class="dropdown-menu">
                    @foreach($carwashes as $carwash)
                        <li>
                            <a class="dropdown-item {{ $carwash_id == $carwash['id'] ? 'active' : '' }}"
                               href="#" wire:click.prevent="$set('carwash_id', '{{ $carwash['id'] }}')">
                                {{ $carwash['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <button class="btn btn-outline-secondary" wire:click="toggleFilters">
                <i class="ti ti-filter me-1"></i> Filters
            </button>

            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="ti ti-printer me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    @if($showFilters)
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <!-- Staff Filter -->
                <div class="col-md-3">
                    <label class="form-label">Staff:</label>
                    <select class="form-select" wire:model.live="staff_id">
                        <option value="">All Staff</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff['id'] }}">
                                {{ $staff['name'] }} - {{ $staff['position'] ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Filter Presets -->
                <div class="col-md-3">
                    <label class="form-label">Period:</label>
                    <select class="form-select" wire:model.live="date_filter">
                        <option value="day">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>

                <!-- Date Range (shown when custom) -->
                @if($date_filter === 'custom')
                <div class="col-md-3">
                    <label class="form-label">Start Date:</label>
                    <input type="date" class="form-control" wire:model.live="start_date">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date:</label>
                    <input type="date" class="form-control" wire:model.live="end_date">
                </div>
                @else
                <div class="col-md-6">
                    <label class="form-label">Date Range:</label>
                    <div class="form-control bg-light">
                        {{ \Carbon\Carbon::parse($start_date)->format('M d, Y') }} - {{ \Carbon\Carbon::parse($end_date)->format('M d, Y') }}
                    </div>
                </div>
                @endif
            </div>

            <div class="mt-3">
                <button class="btn btn-secondary btn-sm" wire:click="resetFilters">
                    <i class="ti ti-refresh me-1"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ti ti-users fs-1 opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Total Staff</h6>
                            <h4 class="mb-0">{{ $summary['total_staff'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ti ti-car-wash fs-1 opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Total Services</h6>
                            <h4 class="mb-0">{{ number_format($summary['total_services']) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ti ti-cash fs-1 opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Total Revenue</h6>
                            <h4 class="mb-0">TSh {{ number_format($summary['total_amount'], 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="ti ti-percentage fs-1 opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title mb-1">Total Commission</h6>
                            <h4 class="mb-0">TSh {{ number_format($summary['total_commission'], 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Performance Table -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="ti ti-chart-bar me-2"></i>Staff Performance Summary</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff</th>
                            <th>Position</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Commission Setting</th>
                            <th class="text-end">Services</th>
                            <th class="text-end">Revenue Generated</th>
                            <th class="text-end">Commission Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($staffPerformance as $staff)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="ti ti-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $staff['name'] }}</div>
                                        @if($staff['phone'])
                                            <small class="text-muted">{{ $staff['phone'] }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $staff['position'] ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $staff['status'] === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $staff['status'] === 'active' ? 'success' : 'secondary' }}">
                                    {{ ucfirst($staff['status']) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($staff['commission_type'] === 'percentage')
                                    <span class="badge bg-info-subtle text-info">
                                        <i class="ti ti-percentage me-1"></i>{{ number_format($staff['commission_rate'], 1) }}%
                                    </span>
                                @elseif($staff['commission_type'] === 'fixed')
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="ti ti-cash me-1"></i>TSh {{ number_format($staff['commission_rate'], 0) }}/service
                                    </span>
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="badge bg-secondary">{{ number_format($staff['services_count']) }}</span>
                            </td>
                            <td class="text-end fw-semibold text-success">
                                TSh {{ number_format($staff['total_amount'], 0) }}
                            </td>
                            <td class="text-end fw-bold text-warning">
                                TSh {{ number_format($staff['calculated_commission'], 0) }}
                                @if($staff['recorded_commission'] > 0 && $staff['recorded_commission'] != $staff['calculated_commission'])
                                    <br><small class="text-muted">(Recorded: TSh {{ number_format($staff['recorded_commission'], 0) }})</small>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="ti ti-users-off display-4 d-block mb-3 opacity-50"></i>
                                <h5>No staff found</h5>
                                <p class="mb-0">Add staff members to see their commission report</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($staffPerformance->count() > 0)
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Totals:</td>
                            <td class="text-end">{{ number_format($summary['total_services']) }}</td>
                            <td class="text-end text-success">TSh {{ number_format($summary['total_amount'], 0) }}</td>
                            <td class="text-end text-warning">TSh {{ number_format($summary['total_commission'], 0) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Transactions -->
    @if($detailedData && $detailedData->count() > 0)
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-list-details me-2"></i>Detailed Transactions</h5>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0">Show</label>
                <select class="form-select form-select-sm" style="width: auto;" wire:model.live="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <label class="mb-0">entries</label>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Staff</th>
                            <th>Service/Item</th>
                            <th>Plate No.</th>
                            <th class="text-end">Price</th>
                            <th class="text-end">Qty</th>
                            <th class="text-end">Commission</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedData as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item->date)->format('M d, Y H:i') }}</td>
                            <td>{{ $item->staff_name }}</td>
                            <td>
                                {{ $item->item_name }}
                                <span class="badge bg-{{ $item->item_type === 'Service' ? 'info' : 'secondary' }}-subtle text-{{ $item->item_type === 'Service' ? 'info' : 'secondary' }} ms-1">
                                    {{ $item->item_type }}
                                </span>
                            </td>
                            <td>{{ $item->plate_number ?? '-' }}</td>
                            <td class="text-end">TSh {{ number_format($item->price, 0) }}</td>
                            <td class="text-end">{{ $item->quantity ?? 1 }}</td>
                            <td class="text-end fw-semibold text-warning">
                                TSh {{ number_format($item->commission ?? 0, 0) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($detailedData->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $detailedData->firstItem() ?? 0 }} to {{ $detailedData->lastItem() ?? 0 }}
                    of {{ $detailedData->total() }} entries
                </div>
                {{ $detailedData->links() }}
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Loading Indicator -->
    <div wire:loading.delay wire:loading.class="opacity-100" class="opacity-0 position-fixed top-0 end-0 m-3 p-2 bg-primary text-white rounded shadow" style="z-index: 1050; transition: opacity 0.2s;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>Loading...</span>
        </div>
    </div>
</div>
