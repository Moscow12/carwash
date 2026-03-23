<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="ti ti-chart-bar me-2"></i>Bar Reports</h4>
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
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <select wire:model.live="reportType" class="form-select">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            @if($reportType === 'custom')
                                <div class="col-md-3">
                                    <input type="date" wire:model.live="dateFrom" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <input type="date" wire:model.live="dateTo" class="form-control">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Total Orders</h6>
                        <h3 class="mb-0">{{ $salesData['total_orders'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Total Revenue</h6>
                        <h3 class="mb-0">TSh {{ number_format($salesData['total_revenue'] ?? 0, 0) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Avg Order Value</h6>
                        <h3 class="mb-0">TSh {{ number_format($salesData['avg_order_value'] ?? 0, 0) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Completed Orders</h6>
                        <h3 class="mb-0">{{ $salesData['completed_orders'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>

        @if($reportType === 'daily' && $hourlyData->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Hourly Sales</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Hour</th>
                                            <th>Orders</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hourlyData as $data)
                                            <tr>
                                                <td>{{ str_pad($data->hour, 2, '0', STR_PAD_LEFT) }}:00</td>
                                                <td>{{ $data->orders }}</td>
                                                <td>TSh {{ number_format($data->revenue, 0) }}</td>
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

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Category Performance</h5>
                    </div>
                    <div class="card-body">
                        @if($categoryData->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                            <th>Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categoryData as $cat)
                                            <tr>
                                                <td><strong>{{ $cat->category }}</strong></td>
                                                <td>{{ $cat->quantity }}</td>
                                                <td>TSh {{ number_format($cat->revenue, 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No data available</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Methods</h5>
                    </div>
                    <div class="card-body">
                        @if($paymentMethods->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Method</th>
                                            <th>Count</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paymentMethods as $method)
                                            <tr>
                                                <td><strong>{{ $method->method }}</strong></td>
                                                <td>{{ $method->count }}</td>
                                                <td>TSh {{ number_format($method->total, 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No payment data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top 10 Selling Items</h5>
                    </div>
                    <div class="card-body">
                        @if($topItems->count() > 0)
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Quantity Sold</th>
                                            <th>Total Sales</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($topItems as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $item->menuItem?->name ?? 'Unknown Item' }}</strong></td>
                                                <td>{{ $item->total_qty }}</td>
                                                <td>TSh {{ number_format($item->total_sales, 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No sales data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Please select a business and outlet to view reports.
                </div>
            </div>
        </div>
    @endif
</div>
