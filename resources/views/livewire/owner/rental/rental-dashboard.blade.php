<div>
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Rental Dashboard</h3>
            <p class="text-muted mb-0">{{ now()->format('l, F j, Y') }} · Snapshot of your rental business</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.rental.agreements') }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-file-plus me-1"></i>New Agreement
            </a>
            <a href="{{ route('owner.rental.rent-payments') }}" class="btn btn-primary btn-sm">
                <i class="ti ti-cash me-1"></i>Record Payment
            </a>
        </div>
    </div>

    {{-- Business Selector --}}
    @if(count($businesses) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label mb-1 small text-muted">Rental Business</label>
                    <select wire:model.live="selectedBusiness" class="form-select">
                        <option value="">Choose business...</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    @if($selectedBusiness)
                    <div class="alert alert-info mb-0 py-2">
                        <i class="ti ti-info-circle me-2"></i>
                        <small>Viewing <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            No rental businesses yet. Create one with type <strong>rental</strong> under
            <a href="{{ route('owner.businesses') }}" class="alert-link">My Businesses</a> first.
        </div>
    @endif

    @if($selectedBusiness)

    {{-- Alerts strip --}}
    @if($alerts['overdue_rent'] + $alerts['unpaid_bills'] + $alerts['open_tickets'] + $alerts['units_in_maintenance'] > 0)
    <div class="row g-3 mb-4">
        @if($alerts['overdue_rent'] > 0)
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('owner.rental.rent-payments') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti ti-alert-circle text-danger fs-2 me-3"></i>
                        <div>
                            <div class="h5 mb-0 text-danger">{{ $alerts['overdue_rent'] }}</div>
                            <small class="text-muted">tenants haven't paid this month</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif
        @if($alerts['unpaid_bills'] > 0)
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('owner.rental.utility-bills') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti ti-bolt text-warning fs-2 me-3"></i>
                        <div>
                            <div class="h5 mb-0 text-warning">{{ $alerts['unpaid_bills'] }}</div>
                            <small class="text-muted">unpaid utility bills</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif
        @if($alerts['open_tickets'] > 0)
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('owner.rental.maintenance') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti ti-tool text-warning fs-2 me-3"></i>
                        <div>
                            <div class="h5 mb-0 text-warning">{{ $alerts['open_tickets'] }}</div>
                            <small class="text-muted">open maintenance tickets</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif
        @if($alerts['units_in_maintenance'] > 0)
        <div class="col-md-3 col-sm-6">
            <a href="{{ route('owner.rental.units') }}" class="text-decoration-none">
                <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
                    <div class="card-body d-flex align-items-center">
                        <i class="ti ti-home-cog text-info fs-2 me-3"></i>
                        <div>
                            <div class="h5 mb-0 text-info">{{ $alerts['units_in_maintenance'] }}</div>
                            <small class="text-muted">units out of service</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- KPI strip --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-building fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Properties</div>
                        <div class="h4 mb-0">{{ number_format($kpis['properties']) }}</div>
                        <small class="text-muted">{{ $kpis['landlords'] }} landlord(s)</small>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-home-2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Rental Units</div>
                        <div class="h4 mb-0">{{ number_format($kpis['units']) }}</div>
                        <small class="text-muted">{{ $kpis['active_agreements'] }} active lease(s)</small>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-percentage fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Occupancy</div>
                        <div class="h4 mb-0">{{ $kpis['occupancy_pct'] }}%</div>
                        <small class="text-muted">{{ $occupancy['occupied'] }} of {{ $occupancy['total'] }} units</small>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Monthly Revenue</div>
                        <div class="h5 mb-0">TZS {{ number_format($kpis['monthly_revenue'], 0) }}</div>
                        <small class="text-muted">from active leases</small>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Cashflow snapshot --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="ti ti-trending-up text-success me-2"></i>{{ now()->format('M Y') }} Cashflow</h6>
                        <a href="{{ route('owner.rental.rent-payments') }}" class="small text-decoration-none">View all <i class="ti ti-arrow-right"></i></a>
                    </div>

                    {{-- Rent collection progress --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Rent collection</span>
                            <span class="fw-medium">{{ $cashflow['rent_collection_pct'] }}% (TZS {{ number_format($cashflow['rent_collected'], 0) }} / {{ number_format($cashflow['rent_expected'], 0) }})</span>
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, $cashflow['rent_collection_pct']) }}%;"></div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-sm-3 col-6">
                            <div class="small text-muted">Rent collected</div>
                            <div class="fw-bold text-success">TZS {{ number_format($cashflow['rent_collected'], 0) }}</div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="small text-muted">Outstanding rent</div>
                            <div class="fw-bold text-{{ $cashflow['outstanding_rent'] > 0 ? 'danger' : 'muted' }}">TZS {{ number_format($cashflow['outstanding_rent'], 0) }}</div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="small text-muted">Utilities collected</div>
                            <div class="fw-bold text-success">TZS {{ number_format($cashflow['util_collected'], 0) }}</div>
                        </div>
                        <div class="col-sm-3 col-6">
                            <div class="small text-muted">Utilities outstanding</div>
                            <div class="fw-bold text-{{ $cashflow['outstanding_utils'] > 0 ? 'danger' : 'muted' }}">TZS {{ number_format($cashflow['outstanding_utils'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Occupancy breakdown --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="mb-3"><i class="ti ti-chart-pie text-primary me-2"></i>Unit Status</h6>

                    @if($occupancy['total'] === 0)
                        <div class="text-center text-muted small py-4">
                            No units yet. <a href="{{ route('owner.rental.units') }}">Add some</a>.
                        </div>
                    @else
                        {{-- Stacked bar --}}
                        @php
                            $pct = fn($n) => $occupancy['total'] > 0 ? round(($n / $occupancy['total']) * 100, 1) : 0;
                        @endphp
                        <div class="progress mb-3" style="height:24px;">
                            @if($occupancy['occupied'] > 0)
                                <div class="progress-bar bg-info" style="width:{{ $pct($occupancy['occupied']) }}%;" title="Occupied">{{ $occupancy['occupied'] }}</div>
                            @endif
                            @if($occupancy['vacant'] > 0)
                                <div class="progress-bar bg-success" style="width:{{ $pct($occupancy['vacant']) }}%;" title="Vacant">{{ $occupancy['vacant'] }}</div>
                            @endif
                            @if($occupancy['reserved'] > 0)
                                <div class="progress-bar bg-primary" style="width:{{ $pct($occupancy['reserved']) }}%;" title="Reserved">{{ $occupancy['reserved'] }}</div>
                            @endif
                            @if($occupancy['maintenance'] > 0)
                                <div class="progress-bar bg-warning" style="width:{{ $pct($occupancy['maintenance']) }}%;" title="Maintenance">{{ $occupancy['maintenance'] }}</div>
                            @endif
                        </div>

                        <div class="d-flex flex-wrap gap-2 small">
                            <span><span class="d-inline-block bg-info rounded-circle me-1" style="width:8px;height:8px;"></span>Occupied {{ $occupancy['occupied'] }}</span>
                            <span><span class="d-inline-block bg-success rounded-circle me-1" style="width:8px;height:8px;"></span>Vacant {{ $occupancy['vacant'] }}</span>
                            <span><span class="d-inline-block bg-primary rounded-circle me-1" style="width:8px;height:8px;"></span>Reserved {{ $occupancy['reserved'] }}</span>
                            <span><span class="d-inline-block bg-warning rounded-circle me-1" style="width:8px;height:8px;"></span>Maintenance {{ $occupancy['maintenance'] }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent payments + Open tickets --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="ti ti-receipt text-success me-2"></i>Recent Payments</h6>
                        <a href="{{ route('owner.rental.rent-payments') }}" class="small text-decoration-none">View all <i class="ti ti-arrow-right"></i></a>
                    </div>

                    @forelse($recentPayments as $p)
                    <div class="d-flex justify-content-between align-items-start py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div>
                            <div class="fw-medium small">{{ $p->agreement?->customer?->name ?? '—' }}</div>
                            <small class="text-muted">Unit {{ $p->agreement?->unit?->unit_number ?? '—' }} · {{ $p->payment_date?->format('M d, Y') }}</small>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold small text-success">TZS {{ number_format($p->amount_paid, 0) }}</div>
                            <small class="text-muted">for {{ $p->payment_for_month?->format('M Y') }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted small py-4">
                        <i class="ti ti-receipt-off fs-2 d-block mb-1"></i>
                        No payments yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="ti ti-tool text-warning me-2"></i>Open Tickets</h6>
                        <a href="{{ route('owner.rental.maintenance') }}" class="small text-decoration-none">View all <i class="ti ti-arrow-right"></i></a>
                    </div>

                    @forelse($openTickets as $t)
                    @php
                        $statusColor = $t->status === 'open' ? 'danger' : 'warning';
                    @endphp
                    <div class="d-flex justify-content-between align-items-start py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-grow-1 me-2">
                            <div class="fw-medium small">{{ ucwords(str_replace('_',' ',$t->maintenance_type)) }}</div>
                            <small class="text-muted">{{ $t->agreement?->customer?->name }} · Unit {{ $t->agreement?->unit?->unit_number }}</small>
                            @if($t->description)
                                <div class="small text-muted text-truncate" style="max-width:280px;">{{ $t->description }}</div>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucwords(str_replace('_',' ',$t->status)) }}</span>
                            <div><small class="text-muted">{{ $t->assignee?->name ?? 'Unassigned' }}</small></div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted small py-4">
                        <i class="ti ti-circle-check fs-2 d-block mb-1"></i>
                        No open tickets.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Top properties --}}
    @if($topProperties->isNotEmpty())
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="ti ti-buildings text-primary me-2"></i>Properties</h6>
                <a href="{{ route('owner.rental.properties') }}" class="small text-decoration-none">Manage <i class="ti ti-arrow-right"></i></a>
            </div>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="text-muted small">
                        <tr>
                            <th>Property</th>
                            <th>Type</th>
                            <th class="text-center">Units</th>
                            <th class="text-center">Occupied</th>
                            <th>Occupancy</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topProperties as $p)
                        @php
                            $occPct = $p->units_count > 0 ? round(($p->occupied_count / $p->units_count) * 100) : 0;
                        @endphp
                        <tr>
                            <td class="fw-medium">{{ $p->property_name }}</td>
                            <td><small class="text-muted">{{ ucfirst($p->property_type) }}</small></td>
                            <td class="text-center">{{ $p->units_count }}</td>
                            <td class="text-center">{{ $p->occupied_count }}</td>
                            <td style="width:180px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;">
                                        <div class="progress-bar bg-info" style="width:{{ $occPct }}%;"></div>
                                    </div>
                                    <small class="text-muted">{{ $occPct }}%</small>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Quick actions --}}
    <div class="row g-3">
        <div class="col-md-3 col-6">
            <a href="{{ route('owner.rental.landlords') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body text-center py-3">
                    <i class="ti ti-users text-primary fs-2 d-block mb-1"></i>
                    <small class="fw-medium">Landlords</small>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('owner.rental.properties') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body text-center py-3">
                    <i class="ti ti-building text-primary fs-2 d-block mb-1"></i>
                    <small class="fw-medium">Properties</small>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('owner.rental.units') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body text-center py-3">
                    <i class="ti ti-home-2 text-info fs-2 d-block mb-1"></i>
                    <small class="fw-medium">Units</small>
                </div>
            </a>
        </div>
        <div class="col-md-3 col-6">
            <a href="{{ route('owner.rental.agreements') }}" class="card border-0 shadow-sm text-decoration-none h-100">
                <div class="card-body text-center py-3">
                    <i class="ti ti-file-text text-success fs-2 d-block mb-1"></i>
                    <small class="fw-medium">Agreements</small>
                </div>
            </a>
        </div>
    </div>

    @endif
</div>
