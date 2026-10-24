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
                        $selectedBusiness = collect($businesses)->firstWhere('id', $business_id);
                    @endphp
                    {{ $selectedBusiness['name'] ?? 'Select Location' }}
                </button>
                <ul class="dropdown-menu">
                    @foreach($businesses as $business)
                        <li>
                            <a class="dropdown-item {{ $business_id == $business['id'] ? 'active' : '' }}"
                               href="#" wire:click.prevent="$set('business_id', '{{ $business['id'] }}')">
                                {{ $business['name'] }}
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
                    <x-forms.select2
                        name="customer_id"
                        label="Customer"
                        :options="collect($customersList)->pluck('name', 'id')"
                        wire:model.live="customer_id"
                        placeholder="All Customers"
                        wrapper="false"
                    />
                </div>

                <!-- Category -->
                <div class="col-md-3">
                    <x-forms.select2
                        name="category_id"
                        label="Category"
                        :options="collect($categories)->pluck('name', 'id')"
                        wire:model.live="category_id"
                        placeholder="All Categories"
                        wrapper="false"
                    />
                </div>

                <!-- Payment Method -->
                <div class="col-md-3">
                    <label class="form-label">Payment Method:</label>
                    <select class="form-select" wire:model.live="payment_method">
                        <option value="">All Payments</option>
                        <option value="cash">Cash</option>
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
    <div class="row mb-4" data-print-area>
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

    <!-- Cash vs Credit split -->
    @php
        $splitTotal = $summary['cash_amount'] + $summary['credit_amount'];
        $cashShare = $splitTotal > 0 ? round($summary['cash_amount'] / $splitTotal * 100, 1) : 0;
        $creditShare = $splitTotal > 0 ? round(100 - $cashShare, 1) : 0;
    @endphp
    <div class="card mb-4" data-print-area>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h6 class="mb-0">Payment Split</h6>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" data-print-text
                            wire:click="$set('payment_method', '{{ $payment_method === 'cash' ? '' : 'cash' }}')"
                            class="btn btn-sm {{ $payment_method === 'cash' ? 'btn-success' : 'btn-outline-success' }}"
                            title="{{ $payment_method === 'cash' ? 'Show all payments' : 'Show only cash sales' }}">
                        <i class="ti ti-cash me-1"></i> Cash · TSh {{ number_format($summary['cash_amount'], 2) }}
                        <span class="opacity-75">({{ $cashShare }}%)</span>
                    </button>
                    <button type="button" data-print-text
                            wire:click="$set('payment_method', '{{ $payment_method === 'credit' ? '' : 'credit' }}')"
                            class="btn btn-sm {{ $payment_method === 'credit' ? 'btn-warning' : 'btn-outline-warning' }}"
                            title="{{ $payment_method === 'credit' ? 'Show all payments' : 'Show only credit sales' }}">
                        <i class="ti ti-file-invoice me-1"></i> Credit · TSh {{ number_format($summary['credit_amount'], 2) }}
                        <span class="opacity-75">({{ $creditShare }}%)</span>
                    </button>
                </div>
            </div>
            <div class="progress" style="height: 10px;" role="img" aria-label="Cash {{ $cashShare }}%, credit {{ $creditShare }}%">
                <div class="progress-bar bg-success" style="width: {{ $cashShare }}%"></div>
                <div class="progress-bar bg-warning" style="width: {{ $creditShare }}%"></div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card" data-print-area>
        <div class="card-header bg-white" data-print-exclude>
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

                    <x-print.button title="Product Sell Report" :business="$business_id" all :meta="[
                        'Period' => \Carbon\Carbon::parse($start_date.' '.$start_time)->format('d M Y H:i').' – '.\Carbon\Carbon::parse($end_date.' '.$end_time)->format('d M Y H:i'),
                        'View' => ['detailed' => 'Detailed', 'grouped' => 'Grouped by product', 'category' => 'By category'][$activeTab] ?? null,
                        'Payment' => $payment_method ? ucfirst($payment_method) : 'All',
                        'Customer' => $customer_id ? data_get(collect($customersList)->firstWhere('id', $customer_id), 'name') : null,
                        'Category' => $category_id ? data_get(collect($categories)->firstWhere('id', $category_id), 'name') : null,
                        'Search' => $search,
                    ]" />
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
                            <th style="width: 60px;">S/N</th>
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
                        @php $payment = $this->paymentIndicator($item->payment_type, $item->payment_status); @endphp
                        <tr class="payment-row payment-row-{{ $payment['color'] }}" wire:key="sale-line-{{ $item->id }}">
                            <td class="text-muted">{{ $detailedData->firstItem() + $loop->index }}</td>
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
                            <td>
                                <span class="badge bg-{{ $payment['color'] }}-subtle text-{{ $payment['color'] }}-emphasis border border-{{ $payment['color'] }}-subtle">
                                    <i class="ti {{ $payment['icon'] }} me-1"></i>{{ $payment['label'] }}
                                </span>
                                @if($payment['status'])
                                    <small class="d-block mt-1 text-{{ $payment['status_color'] }} fw-medium">{{ $payment['status'] }}</small>
                                @endif
                            </td>
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
                            <th style="width: 60px;">S/N</th>
                            <th>Product</th>
                            <th class="text-end">Total Quantity</th>
                            <th class="text-end">Avg. Unit Price</th>
                            <th class="text-end">Total Discount</th>
                            <th class="text-end"><i class="ti ti-cash text-success me-1"></i>Cash</th>
                            <th class="text-end"><i class="ti ti-file-invoice text-warning me-1"></i>Credit</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groupedData as $item)
                        <tr wire:key="grouped-{{ $item->item_id }}">
                            <td class="text-muted">{{ $groupedData->firstItem() + $loop->index }}</td>
                            <td>{{ $item->product_name }}</td>
                            <td class="text-end">{{ number_format($item->total_quantity, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($item->avg_price, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($item->total_discount, 2) }}</td>
                            @include('livewire.owner.reports.partials.payment-split-cells', ['row' => $item])
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
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
                            <th style="width: 60px;">S/N</th>
                            <th>Category</th>
                            <th class="text-end">Total Quantity</th>
                            <th class="text-end">Total Discount</th>
                            <th class="text-end"><i class="ti ti-cash text-success me-1"></i>Cash</th>
                            <th class="text-end"><i class="ti ti-file-invoice text-warning me-1"></i>Credit</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($byCategoryData as $item)
                        <tr wire:key="category-{{ $item->category_id }}">
                            <td class="text-muted">{{ $byCategoryData->firstItem() + $loop->index }}</td>
                            <td>{{ $item->category_name }}</td>
                            <td class="text-end">{{ number_format($item->total_quantity, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($item->total_discount, 2) }}</td>
                            @include('livewire.owner.reports.partials.payment-split-cells', ['row' => $item])
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
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

    <style>
        .payment-row > td:first-child { box-shadow: inset 4px 0 0 var(--row-payment-color); }
        .payment-row-success { --row-payment-color: var(--bs-success); }
        .payment-row-warning { --row-payment-color: var(--bs-warning); }
    </style>

    <!-- Loading Indicator - Non-blocking -->
    <div wire:loading.delay wire:loading.class="opacity-100" class="opacity-0 position-fixed top-0 end-0 m-3 p-2 bg-primary text-white rounded shadow" style="z-index: 1050; transition: opacity 0.2s;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>Loading...</span>
        </div>
    </div>
</div>
