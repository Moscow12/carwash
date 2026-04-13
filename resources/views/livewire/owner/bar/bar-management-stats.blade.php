<!-- Bar Statistics Dashboard -->
<div class="row g-3 mb-4">
    <!-- Revenue Today -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="mb-1 opacity-75" style="font-size: 0.875rem;">Revenue Today</p>
                        <h3 class="mb-0 fw-bold">TSh {{ number_format($stats['revenue_today'], 0) }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <i class="ti ti-trending-up" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                @if($stats['revenue_growth'] != 0)
                    <div class="d-flex align-items-center">
                        <i class="ti ti-{{ $stats['revenue_growth'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                        <small class="opacity-90">
                            {{ number_format(abs($stats['revenue_growth']), 1) }}% vs yesterday
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Orders Today -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="mb-1 opacity-75" style="font-size: 0.875rem;">Orders Today</p>
                        <h3 class="mb-0 fw-bold">{{ $stats['orders_today'] }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <i class="ti ti-shopping-cart" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <small class="opacity-90">
                    Avg: TSh {{ number_format($stats['avg_order_value'], 0) }}
                </small>
            </div>
        </div>
    </div>

    <!-- Open Tabs -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="mb-1 opacity-75" style="font-size: 0.875rem;">Open Tabs</p>
                        <h3 class="mb-0 fw-bold">{{ $stats['open_tabs'] }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <i class="ti ti-receipt" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <small class="opacity-90">
                    Balance: TSh {{ number_format($stats['total_tab_balance'], 0) }}
                </small>
            </div>
        </div>
    </div>

    <!-- Session Status -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm border-0 h-100" style="background: linear-gradient(135deg, {{ $stats['session_open'] ? '#43e97b 0%, #38f9d7' : '#fa709a 0%, #fee140' }} 100%);">
            <div class="card-body text-white">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <p class="mb-1 opacity-75" style="font-size: 0.875rem;">POS Session</p>
                        <h3 class="mb-0 fw-bold">{{ $stats['session_open'] ? 'Active' : 'Closed' }}</h3>
                    </div>
                    <div class="bg-white bg-opacity-25 rounded p-2">
                        <i class="ti ti-{{ $stats['session_open'] ? 'lock-open' : 'lock' }}" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                @if($stats['session_open'] && $stats['active_session'])
                    <small class="opacity-90">
                        Opened: {{ $stats['active_session']->opened_at->diffForHumans() }}
                    </small>
                @else
                    <small class="opacity-90">No active session</small>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Secondary Stats Row -->
<div class="row g-3 mb-4">
    <!-- Weekly Revenue -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="ti ti-calendar-week text-primary mb-2" style="font-size: 2rem;"></i>
                <h6 class="text-muted mb-1 small">Week Revenue</h6>
                <h5 class="mb-0">TSh {{ number_format($stats['revenue_week'] / 1000, 0) }}K</h5>
            </div>
        </div>
    </div>

    <!-- Monthly Revenue -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="ti ti-calendar-month text-success mb-2" style="font-size: 2rem;"></i>
                <h6 class="text-muted mb-1 small">Month Revenue</h6>
                <h5 class="mb-0">TSh {{ number_format($stats['revenue_month'] / 1000, 0) }}K</h5>
            </div>
        </div>
    </div>

    <!-- Weekly Orders -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="ti ti-package text-info mb-2" style="font-size: 2rem;"></i>
                <h6 class="text-muted mb-1 small">Week Orders</h6>
                <h5 class="mb-0">{{ $stats['orders_week'] }}</h5>
            </div>
        </div>
    </div>

    <!-- Monthly Orders -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="ti ti-packages text-warning mb-2" style="font-size: 2rem;"></i>
                <h6 class="text-muted mb-1 small">Month Orders</h6>
                <h5 class="mb-0">{{ $stats['orders_month'] }}</h5>
            </div>
        </div>
    </div>

    <!-- Happy Hours -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="ti ti-clock-hour-3 text-danger mb-2" style="font-size: 2rem;"></i>
                <h6 class="text-muted mb-1 small">Happy Hours</h6>
                <h5 class="mb-0">{{ $stats['active_happy_hours'] }}</h5>
            </div>
        </div>
    </div>

    <!-- Tabs Today -->
    <div class="col-lg-2 col-md-4 col-6">
        <div class="card shadow-sm border-0">
            <div class="card-body text-center">
                <i class="ti ti-file-invoice text-secondary mb-2" style="font-size: 2rem;"></i>
                <h6 class="text-muted mb-1 small">Tabs Today</h6>
                <h5 class="mb-0">{{ $stats['tabs_today'] }}</h5>
            </div>
        </div>
    </div>
</div>

<!-- Top Selling Items -->
@if($stats['top_items']->count() > 0)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0"><i class="ti ti-trophy me-2 text-warning"></i>Top Selling Items Today</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th class="text-center">Qty Sold</th>
                            <th class="text-end">Total Sales</th>
                            <th style="width: 200px;">Popularity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['top_items'] as $index => $item)
                            <tr>
                                <td>
                                    @if($index === 0)
                                        <span class="badge bg-warning">🥇</span>
                                    @elseif($index === 1)
                                        <span class="badge bg-secondary">🥈</span>
                                    @elseif($index === 2)
                                        <span class="badge bg-danger">🥉</span>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $item->menuItem->name ?? 'N/A' }}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $item->total_qty }}</span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">TSh {{ number_format($item->total_sales, 0) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $maxSales = $stats['top_items']->first()->total_sales;
                                        $percentage = ($item->total_sales / $maxSales) * 100;
                                    @endphp
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: {{ $percentage }}%"
                                             aria-valuenow="{{ $percentage }}"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ number_format($percentage, 0) }}%</small>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
