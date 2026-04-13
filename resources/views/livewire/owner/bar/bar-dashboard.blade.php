<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="ti ti-dashboard me-2"></i>Bar Dashboard</h4>
                <div class="d-flex gap-2">
                    <select wire:model.live="selectedBusiness" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Select Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @if($outlets->count() > 0)
                        <select wire:model.live="selectedOutlet" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Select Outlet</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($selectedOutlet)
        <!-- Session Status Alert -->
        @if(isset($stats['session_open']) && $stats['session_open'])
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                        <span><i class="ti ti-check-circle me-2"></i>POS Session is Open</span>
                        <span class="badge bg-success">Started: {{ $stats['active_session']->created_at->format('h:i A') }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>No active POS session. Open a session from the Bar POS to start accepting orders.
                    </div>
                </div>
            </div>
        @endif

        <!-- Key Metrics - Today -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Revenue Today</h6>
                                <h3 class="mb-0">TSh {{ number_format($stats['revenue_today'] ?? 0, 0) }}</h3>
                                @if(isset($stats['revenue_growth']))
                                    <small class="{{ $stats['revenue_growth'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="ti ti-arrow-{{ $stats['revenue_growth'] >= 0 ? 'up' : 'down' }}"></i>
                                        {{ number_format(abs($stats['revenue_growth']), 1) }}% vs yesterday
                                    </small>
                                @endif
                            </div>
                            <div class="text-primary">
                                <i class="ti ti-currency-dollar fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Orders Today</h6>
                                <h3 class="mb-0">{{ $stats['orders_today'] ?? 0 }}</h3>
                                <small class="text-muted">{{ $stats['orders_week'] ?? 0 }} this week</small>
                            </div>
                            <div class="text-success">
                                <i class="ti ti-shopping-cart fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Avg Order Value</h6>
                                <h3 class="mb-0">TSh {{ number_format($stats['avg_order_value'] ?? 0, 0) }}</h3>
                                <small class="text-muted">Per transaction</small>
                            </div>
                            <div class="text-info">
                                <i class="ti ti-chart-line fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Open Tabs</h6>
                                <h3 class="mb-0">{{ $stats['open_tabs'] ?? 0 }}</h3>
                                <small class="text-muted">{{ $stats['tabs_today'] ?? 0 }} created today</small>
                            </div>
                            <div class="text-warning">
                                <i class="ti ti-receipt fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Summary -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Weekly Revenue</h6>
                        <h2 class="text-primary">TSh {{ number_format($stats['revenue_week'] ?? 0, 0) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Monthly Revenue</h6>
                        <h2 class="text-success">TSh {{ number_format($stats['revenue_month'] ?? 0, 0) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Total Tab Balance</h6>
                        <h2 class="text-warning">TSh {{ number_format($stats['total_tab_balance'] ?? 0, 0) }}</h2>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Items Today -->
        @if(isset($stats['top_items']) && $stats['top_items']->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0"><i class="ti ti-trophy me-2"></i>Top 5 Selling Items Today</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['top_items'] as $index => $item)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'primary') }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $item->menuItem?->name ?? 'Unknown Item' }}</strong></td>
                                                <td>{{ $item->total_qty }}</td>
                                                <td><strong>TSh {{ number_format($item->total_sales, 0) }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="ti ti-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <a href="{{ route('owner.bar.pos') }}" class="btn btn-primary w-100">
                                    <i class="ti ti-cash-register me-2"></i>Open Bar POS
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('owner.bar.tabs') }}" class="btn btn-warning w-100">
                                    <i class="ti ti-receipt me-2"></i>Manage Tabs
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('owner.bar.stock') }}" class="btn btn-info w-100">
                                    <i class="ti ti-package me-2"></i>Check Stock
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('owner.bar.reports') }}" class="btn btn-success w-100">
                                    <i class="ti ti-chart-bar me-2"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($stats['active_happy_hours']) && $stats['active_happy_hours'] > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="alert alert-info">
                        <i class="ti ti-clock me-2"></i>
                        <strong>{{ $stats['active_happy_hours'] }}</strong> Happy Hour(s) currently active
                    </div>
                </div>
            </div>
        @endif

    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Please select a business and outlet to view the dashboard.
                </div>
            </div>
        </div>
    @endif
</div>
