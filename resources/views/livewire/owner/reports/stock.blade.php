<div>
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Stock Report</h4>
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

                <!-- Unit -->
                <div class="col-md-3">
                    <label class="form-label">Unit:</label>
                    <select class="form-select" wire:model.live="unit_id">
                        <option value="">All Units</option>
                        @foreach($units as $u)
                            <option value="{{ $u['id'] }}">{{ $u['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="col-md-4">
                    <label class="form-label">Search:</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search by name or SKU..."
                               wire:model.live.debounce.300ms="search">
                    </div>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-secondary w-100" wire:click="resetFilters">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block mb-1">Closing stock (By purchase price)</small>
                    <h4 class="text-primary mb-0">TSh {{ number_format($closingStockPurchase, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block mb-1">Closing stock (By sale price)</small>
                    <h4 class="text-success mb-0">TSh {{ number_format($closingStockSale, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block mb-1">Potential profit</small>
                    <h4 class="{{ $potentialProfit >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                        TSh {{ number_format($potentialProfit, 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <small class="text-muted d-block mb-1">Profit Margin %</small>
                    <h4 class="{{ $profitMarginPercent >= 0 ? 'text-success' : 'text-danger' }} mb-0">
                        {{ number_format($profitMarginPercent, 2) }}%
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
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

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>SKU</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-end">Selling Price</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Current Stock</th>
                            <th class="text-end">Stock Value (Cost)</th>
                            <th class="text-end">Stock Value (Sale)</th>
                            <th class="text-end">Potential Profit</th>
                            <th class="text-end">Total Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockData as $item)
                        @php
                            $stock = isset($stockBalances[$item->id])
                                ? (float) $stockBalances[$item->id]->current_balance
                                : 0;
                            $costPrice = (float) ($item->cost_price ?? 0);
                            $sellingPrice = (float) ($item->selling_price ?? 0);
                            $stockValueCost = $stock * $costPrice;
                            $stockValueSale = $stock * $sellingPrice;
                            $itemProfit = $stockValueSale - $stockValueCost;
                            $totalSold = $this->getTotalUnitsSold($item->id);
                        @endphp
                        <tr>
                            <td><span class="badge bg-secondary">{{ $item->barcode ?? '-' }}</span></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name ?? '-' }}</td>
                            <td class="text-end">TSh {{ number_format($sellingPrice, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($costPrice, 2) }}</td>
                            <td class="text-end">
                                <span class="{{ $stock <= 0 ? 'text-danger' : ($stock <= 10 ? 'text-warning' : 'text-success') }}">
                                    {{ number_format($stock, 2) }} {{ $item->unit->name ?? 'Pc(s)' }}
                                </span>
                            </td>
                            <td class="text-end">TSh {{ number_format($stockValueCost, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($stockValueSale, 2) }}</td>
                            <td class="text-end {{ $itemProfit >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                TSh {{ number_format($itemProfit, 2) }}
                            </td>
                            <td class="text-end">{{ number_format($totalSold, 2) }} Pc(s)</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                No stock data found for the selected filters.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($stockData->count() > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="6" class="text-end">Totals:</td>
                            <td class="text-end">TSh {{ number_format($closingStockPurchase, 2) }}</td>
                            <td class="text-end">TSh {{ number_format($closingStockSale, 2) }}</td>
                            <td class="text-end {{ $potentialProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                TSh {{ number_format($potentialProfit, 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if($stockData->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $stockData->firstItem() ?? 0 }} to {{ $stockData->lastItem() ?? 0 }}
                    of {{ $stockData->total() }} entries
                </div>
                {{ $stockData->links() }}
            </div>
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
