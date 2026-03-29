<div class="restaurant-dashboard">
    <style>
        .restaurant-dashboard {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-header {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .kpi-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .kpi-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .kpi-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 12px;
        }

        .kpi-icon.revenue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
        }

        .kpi-icon.orders {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
        }

        .kpi-icon.average {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: #fff;
        }

        .kpi-icon.covers {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: #fff;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .kpi-label {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .dashboard-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        @media (max-width: 1024px) {
            .dashboard-row {
                grid-template-columns: 1fr;
            }
        }

        .dashboard-card {
            background: #fff;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }

        .card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            padding: 16px;
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: #2d3748;
            transition: all 0.3s;
        }

        .quick-link:hover {
            border-color: #667eea;
            background: #f7fafc;
            transform: translateX(4px);
        }

        .quick-link-icon {
            width: 40px;
            height: 40px;
            background: #edf2f7;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #667eea;
            margin-right: 12px;
        }

        .quick-link-text {
            font-weight: 600;
            font-size: 14px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            text-align: left;
            padding: 12px;
            font-size: 12px;
            font-weight: 700;
            color: #718096;
            text-transform: uppercase;
            border-bottom: 2px solid #e2e8f0;
        }

        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #2d3748;
        }

        .data-table tr:hover {
            background: #f7fafc;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-badge.open {
            background: #fef5e7;
            color: #d68910;
        }

        .status-badge.preparing {
            background: #ebf5fb;
            color: #2874a6;
        }

        .status-badge.ready {
            background: #d5f4e6;
            color: #229954;
        }

        .status-badge.paid {
            background: #e8f8f5;
            color: #148f77;
        }

        .kitchen-stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .kitchen-stat {
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .kitchen-stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2d3748;
        }

        .kitchen-stat-label {
            font-size: 13px;
            color: #718096;
            margin-top: 4px;
        }

        .top-items-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .top-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 8px;
            background: #f7fafc;
        }

        .top-item-info {
            flex: 1;
        }

        .top-item-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }

        .top-item-quantity {
            font-size: 12px;
            color: #718096;
        }

        .top-item-revenue {
            font-weight: 700;
            color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #a0aec0;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 12px;
            display: block;
        }
    </style>

    {{-- Header with Business & Outlet Selection --}}
    <div class="dashboard-header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">
                    <i class="ti ti-dashboard me-2"></i>
                    Restaurant Dashboard
                </h4>
                <p class="text-muted mb-0 small">Track your restaurant performance and insights</p>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                {{-- Business Selection --}}
                @if(!empty($ownerBusinesses) && count($ownerBusinesses) > 1)
                <select wire:model.live="selectedBusiness" class="form-select form-select-sm">
                    @foreach($ownerBusinesses as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Outlet Selection --}}
                @if(!empty($availableOutlets) && count($availableOutlets) > 1)
                <select wire:model.live="selectedOutlet" class="form-select form-select-sm">
                    @foreach($availableOutlets as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                @endif

                {{-- Date Range --}}
                <select wire:model.live="dateRange" class="form-select form-select-sm">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-cards">
        <div class="kpi-card">
            <div class="kpi-icon revenue">
                <i class="ti ti-cash"></i>
            </div>
            <div class="kpi-value">TZS {{ number_format($todayStats['total_revenue'] ?? 0, 0) }}</div>
            <div class="kpi-label">Total Revenue</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon orders">
                <i class="ti ti-shopping-cart"></i>
            </div>
            <div class="kpi-value">{{ $todayStats['total_orders'] ?? 0 }}</div>
            <div class="kpi-label">Total Orders</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon average">
                <i class="ti ti-chart-bar"></i>
            </div>
            <div class="kpi-value">TZS {{ number_format($todayStats['average_order_value'] ?? 0, 0) }}</div>
            <div class="kpi-label">Average Order Value</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon covers">
                <i class="ti ti-users"></i>
            </div>
            <div class="kpi-value">{{ $todayStats['total_covers'] ?? 0 }}</div>
            <div class="kpi-label">Total Covers</div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="dashboard-card mb-4">
        <div class="card-header">
            <h5 class="card-title">
                <i class="ti ti-link me-2"></i>Quick Actions
            </h5>
        </div>
        <div class="quick-links">
            <a href="{{ route('owner.restaurant.pos') }}" class="quick-link">
                <div class="quick-link-icon">
                    <i class="ti ti-device-tablet"></i>
                </div>
                <div class="quick-link-text">Restaurant POS</div>
            </a>
            <a href="{{ route('owner.restaurant.kitchenscreen') }}" class="quick-link">
                <div class="quick-link-icon">
                    <i class="ti ti-tools-kitchen-2"></i>
                </div>
                <div class="quick-link-text">Kitchen Display</div>
            </a>
            <a href="{{ route('owner.restaurant.settings') }}" class="quick-link">
                <div class="quick-link-icon">
                    <i class="ti ti-settings"></i>
                </div>
                <div class="quick-link-text">Settings</div>
            </a>
            <a href="#" class="quick-link">
                <div class="quick-link-icon">
                    <i class="ti ti-file-invoice"></i>
                </div>
                <div class="quick-link-text">Reports</div>
            </a>
            <a href="#" class="quick-link">
                <div class="quick-link-icon">
                    <i class="ti ti-users"></i>
                </div>
                <div class="quick-link-text">Customers</div>
            </a>
            <a href="#" class="quick-link">
                <div class="quick-link-icon">
                    <i class="ti ti-calendar"></i>
                </div>
                <div class="quick-link-text">Reservations</div>
            </a>
        </div>
    </div>

    {{-- Main Dashboard Row --}}
    <div class="dashboard-row">
        {{-- Top Selling Items --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="ti ti-flame me-2"></i>Top Selling Items
                </h5>
            </div>
            @if(!empty($topSellingItems))
            <div class="top-items-list">
                @foreach($topSellingItems as $item)
                <div class="top-item">
                    <div class="top-item-info">
                        <div class="top-item-name">{{ $item['name'] }}</div>
                        <div class="top-item-quantity">
                            <i class="ti ti-shopping-cart"></i> {{ $item['quantity'] }} sold
                        </div>
                    </div>
                    <div class="top-item-revenue">
                        TZS {{ number_format($item['revenue'], 0) }}
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="empty-state">
                <i class="ti ti-package"></i>
                <div>No sales data available</div>
            </div>
            @endif
        </div>

        {{-- Kitchen Stats --}}
        <div class="dashboard-card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="ti ti-chef-hat me-2"></i>Kitchen Performance
                </h5>
            </div>
            <div class="kitchen-stats-grid">
                <div class="kitchen-stat">
                    <div class="kitchen-stat-value">{{ $kitchenStats['queued'] ?? 0 }}</div>
                    <div class="kitchen-stat-label">Queued</div>
                </div>
                <div class="kitchen-stat">
                    <div class="kitchen-stat-value">{{ $kitchenStats['preparing'] ?? 0 }}</div>
                    <div class="kitchen-stat-label">Preparing</div>
                </div>
                <div class="kitchen-stat">
                    <div class="kitchen-stat-value">{{ $kitchenStats['ready'] ?? 0 }}</div>
                    <div class="kitchen-stat-label">Ready</div>
                </div>
                <div class="kitchen-stat">
                    <div class="kitchen-stat-value">{{ $kitchenStats['avg_turnaround'] ?? 0 }} min</div>
                    <div class="kitchen-stat-label">Avg Turnaround</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders --}}
    <div class="dashboard-card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="ti ti-clock-hour-4 me-2"></i>Recent Orders
            </h5>
        </div>
        @if(!empty($recentOrders))
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order No</th>
                        <th>Type</th>
                        <th>Table</th>
                        <th>Covers</th>
                        <th>Waiter</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentOrders as $order)
                    <tr>
                        <td><strong>{{ $order['order_no'] }}</strong></td>
                        <td>
                            @if($order['order_type'] === 'dine_in')
                                <i class="ti ti-armchair"></i> Dine In
                            @elseif($order['order_type'] === 'takeaway')
                                <i class="ti ti-package"></i> Takeaway
                            @else
                                <i class="ti ti-truck-delivery"></i> Delivery
                            @endif
                        </td>
                        <td>{{ $order['table'] }}</td>
                        <td>{{ $order['covers'] }}</td>
                        <td>{{ $order['waiter'] }}</td>
                        <td><strong>TZS {{ number_format($order['total'], 0) }}</strong></td>
                        <td>
                            <span class="status-badge {{ $order['status'] }}">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $order['created_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <i class="ti ti-receipt"></i>
            <div>No recent orders</div>
        </div>
        @endif
    </div>
</div>
