<div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Product Sell Report</h4>
        <div class="d-flex gap-2 flex-wrap align-items-center">
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
                               href="#" wire:click.prevent="$set('carwash_id', '{{ $carwash['id'] }}')">
                                {{ $carwash['name'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <button class="btn btn-outline-secondary" wire:click="toggleFilters">
                <i class="fas fa-filter me-1"></i> Filters
            </button>
        </div>
    </div>

    <!-- Filters Section -->
    @if($showFilters)
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <!-- Search Product -->
                <div class="col-md-3">
                    <label class="form-label">Search Product:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Enter Product name / SKU / Scan bar code"
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <!-- Customer -->
                <div class="col-md-3">
                    <label class="form-label">Customer:</label>
                    <select class="form-select" wire:model.live="customer_id">
                        <option value="">All Customers</option>
                        @foreach($customersList as $customer)
                            <option value="{{ $customer['id'] }}">{{ $customer['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category -->
                <div class="col-md-3">
                    <label class="form-label">Category:</label>
                    <select class="form-select" wire:model.live="category_id">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method -->
                <div class="col-md-3">
                    <label class="form-label">Payment Method:</label>
                    <select class="form-select" wire:model.live="payment_method">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="mobile">Mobile Money</option>
                        <option value="credit">Credit</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-md-3">
                    <label class="form-label">Start Date:</label>
                    <input type="date" class="form-control" wire:model.live="start_date">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Date:</label>
                    <input type="date" class="form-control" wire:model.live="end_date">
                </div>

                <!-- Time Range -->
                <div class="col-md-3">
                    <label class="form-label">Start Time:</label>
                    <input type="time" class="form-control" wire:model.live="start_time">
                </div>

                <div class="col-md-3">
                    <label class="form-label">End Time:</label>
                    <input type="time" class="form-control" wire:model.live="end_time">
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-secondary btn-sm" wire:click="resetFilters">
                    <i class="fas fa-undo me-1"></i> Reset Filters
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Quantity</h6>
                    <h4 class="mb-0">{{ number_format($summary['total_quantity'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Total Discount</h6>
                    <h4 class="mb-0">TSh {{ number_format($summary['total_discount'], 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Sales</h6>
                    <h4 class="mb-0">TSh {{ number_format($summary['total_amount'], 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'detailed' ? 'active' : '' }}"
                                wire:click="setTab('detailed')">
                            <i class="fas fa-list me-1"></i> Detailed
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'grouped' ? 'active' : '' }}"
                                wire:click="setTab('grouped')">
                            <i class="fas fa-layer-group me-1"></i> Grouped
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {{ $activeTab == 'category' ? 'active' : '' }}"
                                wire:click="setTab('category')">
                            <i class="fas fa-tags me-1"></i> By Category
                        </button>
                    </li>
                </ul>

                <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
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

                    <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Detailed Tab -->
            @if($activeTab == 'detailed' && $detailedData)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Invoice No.</th>
                            <th>Date</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($detailedData as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->customer_name ?? 'Walk-In Customer' }}</td>
                            <td>
                                <a href="#" class="text-primary">
                                    {{ $this->generateInvoiceNumber($item->sale_id) }}
                                </a>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($item->sale_date)->format('m/d/Y H:i') }}</td>
                            <td class="text-end">{{ number_format($item->quantity, 2) }} Pc(s)</td>
                            <td class="text-end">TSh {{ number_format($item->price, 2) }}</td>
                            <td class="text-end">{{ number_format($item->discount ?? 0, 2) }}</td>
                            <td class="text-end text-success fw-bold">
                                TSh {{ number_format(($item->price * $item->quantity) - ($item->discount ?? 0), 2) }}
                            </td>
                            <td><span class="badge bg-info">{{ ucfirst($item->payment_type ?? 'Cash') }}</span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No sales data found for the selected filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($detailedData->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $detailedData->firstItem() ?? 0 }} to {{ $detailedData->lastItem() ?? 0 }}
                    of {{ $detailedData->total() }} entries
                </div>
                {{ $detailedData->links() }}
            </div>
            @endif
            @endif

            <!-- Grouped Tab -->
            @if($activeTab == 'grouped' && $groupedData)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-end">Total Quantity</th>
                            <th class="text-end">Avg. Unit Price</th>
                            <th class="text-end">Total Discount</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedData as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-end">{{ number_format($item->total_quantity, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($item->avg_price, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($item->total_discount, 2) }}</td>
                            <td class="text-end text-success fw-bold">TSh {{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No sales data found for the selected filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($groupedData->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $groupedData->firstItem() ?? 0 }} to {{ $groupedData->lastItem() ?? 0 }}
                    of {{ $groupedData->total() }} entries
                </div>
                {{ $groupedData->links() }}
            </div>
            @endif
            @endif

            <!-- By Category Tab -->
            @if($activeTab == 'category' && $byCategoryData)
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Category</th>
                            <th class="text-end">Total Quantity</th>
                            <th class="text-end">Total Discount</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byCategoryData as $item)
                        <tr>
                            <td>{{ $item->category_name }}</td>
                            <td class="text-end">{{ number_format($item->total_quantity, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($item->total_discount, 2) }}</td>
                            <td class="text-end text-success fw-bold">TSh {{ number_format($item->total_amount, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No sales data found for the selected filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($byCategoryData->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $byCategoryData->firstItem() ?? 0 }} to {{ $byCategoryData->lastItem() ?? 0 }}
                    of {{ $byCategoryData->total() }} entries
                </div>
                {{ $byCategoryData->links() }}
            </div>
            @endif
            @endif
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
