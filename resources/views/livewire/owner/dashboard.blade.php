<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Dashboard</h4>
            <p class="text-muted mb-0">Welcome back! Here's your business overview</p>
        </div>
        <a href="{{ route('owner.posscreen') }}" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Sale
        </a>
    </div>

    <!-- Today's Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Today's Sales</p>
                            <h2 class="mb-0">{{ number_format($todaySales) }}</h2>
                            <small class="opacity-75">transactions</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-receipt fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm bg-gradient-success text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Today's Revenue</p>
                            <h2 class="mb-0">TZS {{ number_format($todayRevenue) }}</h2>
                            <small class="opacity-75">collected today</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-currency-dollar fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overview Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="ti ti-car-garage fs-3 text-primary"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Carwashes</h6>
                            <h3 class="mb-0">{{ $totalCarwashes }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="ti ti-receipt fs-3 text-success"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Sales</h6>
                            <h3 class="mb-0">{{ number_format($totalSales) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="ti ti-users fs-3 text-info"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Staff</h6>
                            <h3 class="mb-0">{{ $totalStaff }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="ti ti-user-check fs-3 text-warning"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Customers</h6>
                            <h3 class="mb-0">{{ $totalCustomers }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue Card -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Revenue (All Time)</h6>
                            <h2 class="mb-0 text-success">TZS {{ number_format($totalRevenue) }}</h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-4 rounded">
                            <i class="ti ti-trending-up fs-1 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Sales -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Sales</h5>
            <a href="{{ route('owner.sales') }}" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="text-end">Amount</th>
                            <th>Date</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <span class="fw-medium text-primary">INV-{{ strtoupper(substr($sale['id'], 0, 8)) }}</span>
                            </td>
                            <td>{{ $sale['customer']['name'] ?? 'Walk-in' }}</td>
                            <td>
                                @php $itemCount = count($sale['items'] ?? []); @endphp
                                <span class="badge bg-secondary">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
                            </td>
                            <td class="text-end fw-bold">TZS {{ number_format($sale['total_amount']) }}</td>
                            <td>{{ \Carbon\Carbon::parse($sale['sale_date'])->format('M d, Y') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $sale['payment_status'] === 'paid' ? 'success' : ($sale['payment_status'] === 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($sale['payment_status']) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-receipt-off fs-1 d-block mb-2"></i>
                                    No sales yet
                                    <br>
                                    <a href="{{ route('owner.posscreen') }}" class="btn btn-primary btn-sm mt-2">
                                        <i class="ti ti-plus me-1"></i> Make First Sale
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
