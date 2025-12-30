<div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Profit / Loss Report</h4>
        <div class="d-flex gap-2 flex-wrap">
            <!-- Location Selector -->
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    @php
                        $selectedCarwash = collect($carwashes)->firstWhere('id', $carwash_id);
                    @endphp
                    {{ $selectedCarwash['name'] ?? 'Select Location' }}
                </button>
                <ul class="dropdown-menu">
                    @foreach($carwashes as $carwash)
                        <li>
                            <a class="dropdown-item {{ $carwash_id == $carwash['id'] ? 'active' : '' }}"
                               href="#" wire:click.prevent="$set('carwash_id', '{{ $carwash['id'] }}')" wire:loading.attr="disabled">
                                {{ $carwash['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Date Filter -->
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar me-1"></i> Filter by date
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item {{ $date_filter == 'today' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'today')">Today</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'yesterday' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'yesterday')">Yesterday</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'last_7_days' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'last_7_days')">Last 7 Days</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'last_30_days' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'last_30_days')">Last 30 Days</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item {{ $date_filter == 'this_month' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'this_month')">This Month</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'last_month' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'last_month')">Last Month</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'this_month_last_year' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'this_month_last_year')">This month last year</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item {{ $date_filter == 'this_year' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'this_year')">This Year</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'last_year' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'last_year')">Last Year</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'current_fy' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'current_fy')">Current financial year</a></li>
                    <li><a class="dropdown-item {{ $date_filter == 'last_fy' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'last_fy')">Last financial year</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item {{ $date_filter == 'custom' ? 'active' : '' }}" href="#" wire:click.prevent="$set('date_filter', 'custom')">Custom Range</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Custom Date Range -->
    @if($date_filter == 'custom')
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" wire:model.live="start_date">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" wire:model.live="end_date">
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Current Filter Badge -->
    <div class="mb-3">
        <span class="badge bg-info fs-6">
            <i class="fas fa-filter me-1"></i> {{ $this->dateFilterLabel }}
        </span>
    </div>

    <!-- Two Column Report Section -->
    <div class="row mb-4">
        <!-- Left Column -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Opening Stock</strong>
                                    <br><small class="text-muted">(By purchase price):</small>
                                </td>
                                <td class="text-end">TSh {{ number_format($openingStockPurchase, 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Opening Stock</strong>
                                    <br><small class="text-muted">(By sale price):</small>
                                </td>
                                <td class="text-end">TSh {{ number_format($openingStockSale, 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Total purchase:</strong>
                                    <br><small class="text-muted">(Exc. tax, Discount)</small>
                                </td>
                                <td class="text-end text-primary">TSh {{ number_format($totalPurchase, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Stock Adjustment:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalStockAdjustment, 2) }}</td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Total Expense:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalExpense, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total purchase shipping charge:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalPurchaseShipping, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Purchase additional expenses:</strong></td>
                                <td class="text-end">TSh {{ number_format($purchaseAdditionalExpenses, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total transfer shipping charge:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalTransferShipping, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Sell discount:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalSellDiscount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total customer reward:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalCustomerReward, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Sell Return:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalSellReturn, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td>
                                    <strong>Closing stock</strong>
                                    <br><small class="text-muted">(By purchase price):</small>
                                </td>
                                <td class="text-end text-primary">TSh {{ number_format($closingStockPurchase, 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Closing stock</strong>
                                    <br><small class="text-muted">(By sale price):</small>
                                </td>
                                <td class="text-end text-primary">TSh {{ number_format($closingStockSale, 2) }}</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong>Total Sales:</strong>
                                    <br><small class="text-muted">(Exc. tax, Discount)</small>
                                </td>
                                <td class="text-end text-primary">TSh {{ number_format($totalSales, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total sell shipping charge:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalSellShipping, 2) }}</td>
                            </tr>
                            <tr class="table-warning">
                                <td><strong>Sell additional expenses:</strong></td>
                                <td class="text-end">TSh {{ number_format($sellAdditionalExpenses, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Stock Recovered:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalStockRecovered, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Purchase Return:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalPurchaseReturn, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total Purchase discount:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalPurchaseDiscount, 2) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Total sell round off:</strong></td>
                                <td class="text-end">TSh {{ number_format($totalSellRoundOff, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Summary Section -->
    <div class="card mb-4 border-0 bg-light">
        <div class="card-body">
            <div class="mb-4">
                <h3 class="text-secondary">
                    Gross Profit: <span class="{{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">TSh {{ number_format($grossProfit, 2) }}</span>
                </h3>
                <small class="text-muted">(Total sell price - Total purchase price)</small>
            </div>

            <div>
                <h3 class="text-secondary">
                    Net Profit: <span class="{{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">TSh {{ number_format($netProfit, 2) }}</span>
                </h3>
                <small class="text-success d-block">
                    Gross Profit + (Total sell shipping charge + Sell additional expenses + Total Stock Recovered + Total Purchase discount + Total sell round off)
                </small>
                <small class="text-danger d-block">
                    - (Total Stock Adjustment + Total Expense + Total purchase shipping charge + Total transfer shipping charge + Purchase additional expenses + Total Sell discount + Total customer reward)
                </small>
            </div>
        </div>
    </div>

    <!-- Profit Breakdown Tabs -->
    <div class="card">
        <div class="card-header bg-white border-bottom-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'products' ? 'active' : '' }}"
                                wire:click="setTab('products')">
                            <i class="fas fa-cubes me-1"></i> Profit by products
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'categories' ? 'active' : '' }}"
                                wire:click="setTab('categories')">
                            <i class="fas fa-tags me-1"></i> Profit by categories
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'customers' ? 'active' : '' }}"
                                wire:click="setTab('customers')">
                            <i class="fas fa-users me-1"></i> Profit by customer
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'date' ? 'active' : '' }}"
                                wire:click="setTab('date')">
                            <i class="fas fa-calendar-alt me-1"></i> Profit by date
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'day' ? 'active' : '' }}"
                                wire:click="setTab('day')">
                            <i class="fas fa-calendar-week me-1"></i> Profit by day
                        </button>
                    </li>
                </ul>
                <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="tab-content">
                <!-- Products Tab -->
                @if($activeTab == 'products')
                <div class="table-responsive">
                    <table class="table table-hover" id="productsTable">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-end">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profitByProducts as $product)
                            <tr>
                                <td>{{ $product['name'] }} @if($product['code'] != '-')<small class="text-muted">({{ $product['code'] }})</small>@endif</td>
                                <td class="text-end {{ $product['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TSh {{ number_format($product['gross_profit'], 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Categories Tab -->
                @if($activeTab == 'categories')
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th class="text-end">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profitByCategories as $category)
                            <tr>
                                <td>{{ $category['name'] }}</td>
                                <td class="text-end {{ $category['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TSh {{ number_format($category['gross_profit'], 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Customers Tab -->
                @if($activeTab == 'customers')
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profitByCustomers as $customer)
                            <tr>
                                <td>{{ $customer['name'] }}</td>
                                <td class="text-end">TSh {{ number_format($customer['total_sales'], 2) }}</td>
                                <td class="text-end {{ $customer['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TSh {{ number_format($customer['gross_profit'], 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Date Tab -->
                @if($activeTab == 'date')
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profitByDate as $dateRecord)
                            <tr>
                                <td>{{ $dateRecord['formatted_date'] }}</td>
                                <td class="text-end">TSh {{ number_format($dateRecord['total_sales'], 2) }}</td>
                                <td class="text-end {{ $dateRecord['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TSh {{ number_format($dateRecord['gross_profit'], 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif

                <!-- Day Tab -->
                @if($activeTab == 'day')
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th class="text-end">Total Sales</th>
                                <th class="text-end">Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($profitByDay as $dayRecord)
                            <tr>
                                <td>{{ $dayRecord['day'] }}</td>
                                <td class="text-end">TSh {{ number_format($dayRecord['total_sales'], 2) }}</td>
                                <td class="text-end {{ $dayRecord['gross_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    TSh {{ number_format($dayRecord['gross_profit'], 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Loading Indicator - Non-blocking -->
    <div wire:loading.delay wire:loading.class="opacity-100" class="opacity-0 position-fixed top-0 end-0 m-3 p-2 bg-primary text-white rounded shadow" style="z-index: 1050; transition: opacity 0.2s;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>Loading...</span>
        </div>
    </div>
</div>
