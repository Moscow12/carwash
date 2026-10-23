<div class="container-fluid py-4 items-page">
    <style>
        [x-cloak] { display: none !important; }
        .items-page .stat-card { cursor: pointer; transition: transform .12s, box-shadow .12s, border-color .12s; border: 2px solid transparent !important; }
        .items-page .stat-card:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .08) !important; }
        .items-page .stat-card.active { border-color: var(--bs-primary) !important; }
        .items-page .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .items-page .item-thumb { width: 44px; height: 44px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
        .items-page .item-thumb-placeholder { width: 44px; height: 44px; border-radius: 10px; background: var(--bs-tertiary-bg); display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .items-page .table > :not(caption) > * > * { padding-top: .65rem; padding-bottom: .65rem; }
        .items-page .table thead th { white-space: nowrap; font-size: .8rem; text-transform: uppercase; letter-spacing: .02em; color: var(--bs-secondary-color); }
        .items-page .row-actions .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
        .items-page .filter-chip { border-radius: 999px; }
        .items-page .table-wrap { position: relative; }
        /* No display rule here: Livewire hides wire:loading elements and sets display:flex only while loading */
        .items-page .table-loading { position: absolute; inset: 0; background: rgba(var(--bs-body-bg-rgb), .6); align-items: flex-start; justify-content: center; padding-top: 4rem; z-index: 2; }
    </style>

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Items</h4>
            <p class="text-muted mb-0">Your products and services, prices, margins and stock in one place</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            @if($businesses->count() > 1)
                <div class="input-group" style="max-width: 260px;">
                    <span class="input-group-text"><i class="ti ti-building-store"></i></span>
                    <select wire:model.live="selectedBusiness" class="form-select" aria-label="Business">
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}" wire:key="business-option-{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button class="btn btn-outline-secondary" onclick="window.print()" title="Print">
                <i class="ti ti-printer"></i><span class="d-none d-sm-inline ms-1">Print</span>
            </button>
            <a href="{{ route('owner.uploaditems') }}" class="btn btn-outline-primary" title="Import from CSV">
                <i class="ti ti-file-upload"></i><span class="d-none d-sm-inline ms-1">Import</span>
            </a>
            <a href="{{ route('owner.itemregister') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Item
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-x me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Summary cards double as quick filters --}}
    @php
        $statCards = [
            ['filter' => '', 'label' => 'All Items', 'value' => $totalItems, 'icon' => 'ti-layout-grid', 'color' => 'primary', 'active' => $typeFilter === '' && $stockFilter === ''],
            ['filter' => 'product', 'label' => 'Products', 'value' => $totalProducts, 'icon' => 'ti-package', 'color' => 'info', 'active' => $typeFilter === 'product' && $stockFilter === ''],
            ['filter' => 'Service', 'label' => 'Services', 'value' => $totalServices, 'icon' => 'ti-tools', 'color' => 'success', 'active' => $typeFilter === 'Service'],
            ['filter' => 'low', 'label' => 'Low / Out of Stock', 'value' => $lowStockCount, 'icon' => 'ti-alert-triangle', 'color' => $lowStockCount > 0 ? 'danger' : 'secondary', 'active' => $stockFilter === 'low'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($statCards as $card)
            <div class="col-6 col-lg-3" wire:key="stat-card-{{ $card['label'] }}">
                <div class="card shadow-sm h-100 stat-card {{ $card['active'] ? 'active' : '' }}"
                     role="button" tabindex="0"
                     wire:click="quickFilter('{{ $card['filter'] }}')"
                     wire:keydown.enter="quickFilter('{{ $card['filter'] }}')"
                     aria-pressed="{{ $card['active'] ? 'true' : 'false' }}"
                     title="Show {{ strtolower($card['label']) }}">
                    <div class="card-body d-flex align-items-center gap-3">
                        <span class="stat-icon bg-{{ $card['color'] }}-subtle text-{{ $card['color'] }}"><i class="ti {{ $card['icon'] }} fs-4"></i></span>
                        <div class="min-w-0">
                            <div class="text-muted small text-truncate">{{ $card['label'] }}</div>
                            <div class="h4 mb-0">{{ number_format($card['value']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        {{-- Tabs --}}
        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-0">
            <ul class="nav nav-pills gap-2">
                <li class="nav-item">
                    <button wire:click="setTab('products')" class="nav-link {{ $activeTab === 'products' ? 'active' : '' }}">
                        <i class="ti ti-list me-1"></i> All Items
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="setTab('stock')" class="nav-link {{ $activeTab === 'stock' ? 'active' : '' }}">
                        <i class="ti ti-chart-bar me-1"></i> Stock Report
                    </button>
                </li>
            </ul>
        </div>

        {{-- Toolbar --}}
        <div class="card-body pb-0">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="ti ti-search"></i></span>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name, SKU or barcode..." aria-label="Search items">
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-lg-auto">
                    <div class="btn-group w-100" role="group" aria-label="Item type">
                        <button type="button" wire:click="$set('typeFilter', '')" class="btn btn-sm {{ $typeFilter === '' ? 'btn-secondary' : 'btn-outline-secondary' }}">All</button>
                        <button type="button" wire:click="$set('typeFilter', 'product')" class="btn btn-sm {{ $typeFilter === 'product' ? 'btn-secondary' : 'btn-outline-secondary' }}">Products</button>
                        <button type="button" wire:click="$set('typeFilter', 'Service')" class="btn btn-sm {{ $typeFilter === 'Service' ? 'btn-secondary' : 'btn-outline-secondary' }}">Services</button>
                    </div>
                </div>
                <div class="col-6 col-sm-3 col-lg">
                    <select wire:model.live="categoryFilter" class="form-select form-select-sm" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" wire:key="category-option-{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-lg">
                    <select wire:model.live="unitFilter" class="form-select form-select-sm" aria-label="Unit">
                        <option value="">All units</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" wire:key="unit-option-{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-lg">
                    <select wire:model.live="statusFilter" class="form-select form-select-sm" aria-label="Status">
                        <option value="">Any status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-sm-3 col-lg">
                    <select wire:model.live="stockFilter" class="form-select form-select-sm" aria-label="Stock level">
                        <option value="">Any stock level</option>
                        <option value="low">Low stock (&lt; {{ \App\Livewire\Owner\Items\Listitems::LOW_STOCK_THRESHOLD }})</option>
                        <option value="out">Out of stock</option>
                    </select>
                </div>
            </div>

            {{-- Active filter chips --}}
            @php
                $activeFilters = array_filter([
                    'search' => $search !== '' ? 'Search: "'.$search.'"' : null,
                    'typeFilter' => $typeFilter !== '' ? ($typeFilter === 'Service' ? 'Services' : 'Products') : null,
                    'categoryFilter' => $categoryFilter !== '' ? 'Category: '.($categories->firstWhere('id', $categoryFilter)?->name ?? '-') : null,
                    'unitFilter' => $unitFilter !== '' ? 'Unit: '.($units->firstWhere('id', $unitFilter)?->name ?? '-') : null,
                    'statusFilter' => $statusFilter !== '' ? ucfirst($statusFilter) : null,
                    'stockFilter' => $stockFilter !== '' ? ($stockFilter === 'out' ? 'Out of stock' : 'Low stock') : null,
                ]);
            @endphp
            @if(count($activeFilters) > 0)
                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <small class="text-muted">Filtered by:</small>
                    @foreach($activeFilters as $property => $label)
                        <button type="button" wire:key="filter-chip-{{ $property }}" wire:click="$set('{{ $property }}', '')" class="btn btn-sm btn-light border filter-chip" title="Remove filter">
                            {{ $label }} <i class="ti ti-x ms-1"></i>
                        </button>
                    @endforeach
                    <button type="button" wire:click="clearFilters" class="btn btn-sm btn-link text-decoration-none">Clear all</button>
                </div>
            @endif
        </div>

        <div class="card-body table-wrap">
            <div class="table-loading" wire:loading.flex wire:target="search,typeFilter,categoryFilter,unitFilter,statusFilter,stockFilter,sortTable,setTab,quickFilter,clearFilters,selectedBusiness,perPage,gotoPage,nextPage,previousPage">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
            </div>

            @if($activeTab === 'products')
                {{-- All Items --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                @include('livewire.owner.partials.sort-header', ['field' => 'name', 'label' => 'Item'])
                                @include('livewire.owner.partials.sort-header', ['field' => 'cost_price', 'label' => 'Cost Price', 'class' => 'text-end d-none d-lg-table-cell'])
                                @include('livewire.owner.partials.sort-header', ['field' => 'selling_price', 'label' => 'Selling Price', 'class' => 'text-end'])
                                @include('livewire.owner.partials.sort-header', ['field' => 'profit_margin', 'label' => 'Profit Margin', 'class' => 'text-end'])
                                <th class="text-end">Stock</th>
                                <th class="text-end" style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $isService = $item->type === 'Service';
                                    $stock = $isService ? null : (float) ($stockData[$item->id] ?? 0);
                                    $unitLabel = $item->unit?->symbol ?? $item->unit?->name ?? '';
                                @endphp
                                <tr wire:key="item-row-{{ $item->id }}" class="{{ $item->status === 'inactive' ? 'opacity-75' : '' }}">
                                    <td class="text-muted">{{ $items->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->image)
                                                <img src="{{ asset('storage/' . $item->image) }}" alt="" class="item-thumb" loading="lazy">
                                            @else
                                                <span class="item-thumb-placeholder"><i class="ti ti-{{ $isService ? 'tools' : 'package' }} text-muted"></i></span>
                                            @endif
                                            <div class="min-w-0">
                                                <button type="button" wire:click="viewItem('{{ $item->id }}')" class="btn btn-link p-0 fw-semibold text-body text-decoration-none text-start">{{ $item->name }}</button>
                                                @if($item->sku)
                                                    <span class="badge bg-body-tertiary text-body-secondary border ms-1" title="SKU">SKU {{ $item->sku }}</span>
                                                @endif
                                                <div class="small text-muted">
                                                    <span class="badge bg-{{ $isService ? 'success' : 'info' }}-subtle text-{{ $isService ? 'success' : 'info' }}-emphasis me-1">{{ $isService ? 'Service' : 'Product' }}</span>
                                                    {{ $item->category?->name ?? 'Uncategorised' }}
                                                    @if($item->status === 'inactive')
                                                        <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end d-none d-lg-table-cell text-muted">TZS {{ number_format($item->cost_price, 2) }}</td>
                                    <td class="text-end fw-semibold">TZS {{ number_format($item->selling_price, 2) }}</td>
                                    @include('livewire.owner.items.partials.margin-cell', ['item' => $item])
                                    <td class="text-end">
                                        @if($isService)
                                            <span class="text-muted small">N/A</span>
                                        @elseif($stock <= 0)
                                            <span class="badge bg-danger-subtle text-danger-emphasis">Out of stock</span>
                                        @else
                                            <span class="badge bg-{{ $stock < \App\Livewire\Owner\Items\Listitems::LOW_STOCK_THRESHOLD ? 'warning' : 'success' }}-subtle text-{{ $stock < \App\Livewire\Owner\Items\Listitems::LOW_STOCK_THRESHOLD ? 'warning' : 'success' }}-emphasis">
                                                {{ rtrim(rtrim(number_format($stock, 2), '0'), '.') }} {{ $unitLabel }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="row-actions d-inline-flex gap-1">
                                            <a href="{{ route('owner.items.edit', ['itemId' => $item->id]) }}" class="btn btn-sm btn-outline-primary" title="Edit {{ $item->name }}" aria-label="Edit {{ $item->name }}">
                                                <i class="ti ti-pencil"></i>
                                            </a>
                                            @unless($isService)
                                                <button type="button" wire:click="openStockModal('{{ $item->id }}')" class="btn btn-sm btn-outline-success" title="Adjust stock" aria-label="Adjust stock for {{ $item->name }}">
                                                    <i class="ti ti-packages"></i>
                                                </button>
                                            @endunless
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More actions" aria-label="More actions for {{ $item->name }}">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                    <li>
                                                        <button class="dropdown-item" wire:click="viewItem('{{ $item->id }}')">
                                                            <i class="ti ti-eye me-2"></i> View details
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('owner.history', ['itemId' => $item->id]) }}" class="dropdown-item">
                                                            <i class="ti ti-history me-2"></i> Item history
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item" wire:click="toggleStatus('{{ $item->id }}')">
                                                            <i class="ti ti-toggle-{{ $item->status === 'active' ? 'right' : 'left' }} me-2"></i>
                                                            {{ $item->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item text-danger" wire:click="delete('{{ $item->id }}')" wire:confirm="Delete {{ $item->name }}? This cannot be undone.">
                                                            <i class="ti ti-trash me-2"></i> Delete
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        @include('livewire.owner.items.partials.empty-state', ['icon' => 'ti-package-off'])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Stock Report --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">S/N</th>
                                @include('livewire.owner.partials.sort-header', ['field' => 'name', 'label' => 'Item'])
                                <th class="text-end">Current Stock</th>
                                @include('livewire.owner.partials.sort-header', ['field' => 'stock_value_cost', 'label' => 'Stock Value (Cost)', 'class' => 'text-end'])
                                @include('livewire.owner.partials.sort-header', ['field' => 'stock_value_sell', 'label' => 'Stock Value (Sell)', 'class' => 'text-end'])
                                @include('livewire.owner.partials.sort-header', ['field' => 'profit_margin', 'label' => 'Profit Margin', 'class' => 'text-end'])
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                @php
                                    $isService = $item->type === 'Service';
                                    $stock = $isService ? 0 : ($stockData[$item->id] ?? 0);
                                    $stockValueCost = $isService ? 0 : ($stock * $item->cost_price);
                                    $stockValueSell = $isService ? 0 : ($stock * $item->selling_price);
                                @endphp
                                <tr wire:key="stock-row-{{ $item->id }}">
                                    <td class="text-muted">{{ $items->firstItem() + $loop->index }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item->name }} @if($item->sku)<span class="badge bg-body-tertiary text-body-secondary border ms-1" title="SKU">SKU {{ $item->sku }}</span>@endif</div>
                                        <small class="text-muted">{{ $item->category?->name ?? 'Uncategorised' }} · {{ $item->unit?->name ?? '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        @if($isService)
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Service</span>
                                        @else
                                            <div class="fw-semibold {{ $stock <= 0 ? 'text-danger' : '' }}">{{ number_format($stock, 2) }}</div>
                                            @if($stock <= 0)
                                                <span class="badge bg-danger-subtle text-danger-emphasis">Out of stock</span>
                                            @elseif($stock < \App\Livewire\Owner\Items\Listitems::LOW_STOCK_THRESHOLD)
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Low stock</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success-emphasis">In stock</span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isService)
                                            <span class="text-muted small">N/A</span>
                                        @else
                                            TZS {{ number_format($stockValueCost, 2) }}
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold">
                                        @if($isService)
                                            <span class="text-muted small">N/A</span>
                                        @else
                                            TZS {{ number_format($stockValueSell, 2) }}
                                        @endif
                                    </td>
                                    {{-- Products show potential profit on stock held; services have no stock, so per-unit profit --}}
                                    @include('livewire.owner.items.partials.margin-cell', [
                                        'item' => $item,
                                        'profitAmount' => $isService ? null : $stockValueSell - $stockValueCost,
                                    ])
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        @include('livewire.owner.items.partials.empty-state', ['icon' => 'ti-chart-bar-off'])
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($items->count() > 0)
                            <tfoot class="table-dark">
                                @php
                                    $totalStockValueCost = 0;
                                    $totalStockValueSell = 0;
                                    foreach($items as $item) {
                                        if ($item->type !== 'Service') {
                                            $stock = $stockData[$item->id] ?? 0;
                                            $totalStockValueCost += $stock * $item->cost_price;
                                            $totalStockValueSell += $stock * $item->selling_price;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Total Stock Value:</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($totalStockValueCost, 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($totalStockValueSell, 2) }}</td>
                                    @include('livewire.owner.items.partials.margin-cell', [
                                        'item' => null,
                                        'margin' => $totalStockValueSell > 0 ? ($totalStockValueSell - $totalStockValueCost) * 100 / $totalStockValueSell : null,
                                        'profitAmount' => $totalStockValueSell - $totalStockValueCost,
                                        'dark' => true,
                                    ])
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endif
        </div>

        {{-- Footer: page size + pagination --}}
        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->total() > 0)
            <div class="card-footer bg-transparent d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <span>Show</span>
                    <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;" aria-label="Items per page">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}">{{ $size }}</option>
                        @endforeach
                    </select>
                    <span>per page · {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }}</span>
                </div>
                @if($items->hasPages())
                    {{ $items->links() }}
                @endif
            </div>
        @endif
    </div>

    {{-- View Item Modal --}}
    @if($showViewModal && $viewingItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title"><i class="ti ti-eye me-2"></i>Item Details</h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex align-items-center gap-3 mb-4">
                            @if($viewingItem->image)
                                <img src="{{ asset('storage/' . $viewingItem->image) }}" alt="" class="rounded-3" style="width: 88px; height: 88px; object-fit: cover;">
                            @else
                                <div class="bg-body-tertiary rounded-3 d-inline-flex align-items-center justify-content-center" style="width: 88px; height: 88px;">
                                    <i class="ti ti-photo fs-1 text-muted"></i>
                                </div>
                            @endif
                            <div>
                                <h5 class="mb-1">{{ $viewingItem->name }}</h5>
                                <span class="badge bg-{{ $viewingItem->type === 'Service' ? 'success' : 'info' }}-subtle text-{{ $viewingItem->type === 'Service' ? 'success' : 'info' }}-emphasis">{{ $viewingItem->type === 'Service' ? 'Service' : 'Product' }}</span>
                                <span class="badge bg-{{ $viewingItem->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($viewingItem->status) }}</span>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="small text-muted">Cost</div>
                                <div class="fw-semibold">TZS {{ number_format($viewingItem->cost_price, 2) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Selling</div>
                                <div class="fw-bold text-primary">TZS {{ number_format($viewingItem->selling_price, 2) }}</div>
                            </div>
                            <div class="col-4">
                                <div class="small text-muted">Margin</div>
                                <div class="fw-semibold">
                                    {{ $viewingItem->selling_price > 0 ? number_format(($viewingItem->selling_price - $viewingItem->cost_price) * 100 / $viewingItem->selling_price, 1).'%' : '-' }}
                                </div>
                            </div>
                        </div>

                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted fw-normal">SKU</dt>
                            <dd class="col-7">{{ $viewingItem->sku ?? '-' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Barcode</dt>
                            <dd class="col-7">{{ $viewingItem->barcode ?: '-' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Description</dt>
                            <dd class="col-7">{{ $viewingItem->description ?: '-' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Category</dt>
                            <dd class="col-7">{{ $viewingItem->category?->name ?? '-' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Unit</dt>
                            <dd class="col-7">{{ $viewingItem->unit?->name ?? '-' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Market Price</dt>
                            <dd class="col-7">{{ $viewingItem->market_price ? 'TZS ' . number_format($viewingItem->market_price, 2) : '-' }}</dd>
                            <dt class="col-5 text-muted fw-normal">Commission</dt>
                            <dd class="col-7 mb-0">
                                @if($viewingItem->commission)
                                    {{ $viewingItem->commission_type === 'percentage' ? $viewingItem->commission . '%' : 'TZS ' . number_format($viewingItem->commission, 2) }}
                                @else
                                    -
                                @endif
                            </dd>
                        </dl>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeViewModal">Close</button>
                        <a href="{{ route('owner.items.edit', ['itemId' => $viewingItem->id]) }}" class="btn btn-primary">
                            <i class="ti ti-pencil me-1"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Stock Modal --}}
    @if($showStockModal && $stockItem)
        @php
            $currentStock = (float) $this->getCurrentStock($stockItem->id);
            $stockUnit = $stockItem->unit?->symbol ?? $stockItem->unit?->name ?? 'units';
        @endphp
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow"
                     x-data="{ type: @entangle('stockType'), qty: @entangle('stockQuantity'), current: {{ $currentStock }} }">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title"><i class="ti ti-packages me-2"></i>Adjust Stock</h5>
                        <button type="button" class="btn-close" wire:click="closeStockModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="fw-semibold mb-3">{{ $stockItem->name }}</div>

                        <div class="btn-group w-100 mb-3" role="group" aria-label="Stock movement">
                            <button type="button" class="btn" :class="type === 'in' ? 'btn-success' : 'btn-outline-success'" @click="type = 'in'">
                                <i class="ti ti-plus me-1"></i> Stock In
                            </button>
                            <button type="button" class="btn" :class="type === 'out' ? 'btn-danger' : 'btn-outline-danger'" @click="type = 'out'">
                                <i class="ti ti-minus me-1"></i> Stock Out
                            </button>
                        </div>

                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg mb-1">
                            <input type="number" x-model.number="qty" x-init="$nextTick(() => $el.select())" wire:keydown.enter="saveStock"
                                   class="form-control @error('stockQuantity') is-invalid @enderror" placeholder="0" step="0.01" min="0" inputmode="decimal">
                            <span class="input-group-text">{{ $stockUnit }}</span>
                        </div>
                        @error('stockQuantity')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-between align-items-center bg-body-tertiary rounded-3 p-3 mt-3">
                            <div class="text-center">
                                <div class="small text-muted">Current</div>
                                <div class="fw-semibold">{{ rtrim(rtrim(number_format($currentStock, 2), '0'), '.') }}</div>
                            </div>
                            <i class="ti ti-arrow-right text-muted"></i>
                            <div class="text-center">
                                <div class="small text-muted">After</div>
                                <div class="fw-bold"
                                     x-text="(current + (type === 'in' ? 1 : -1) * (Number(qty) || 0)).toLocaleString(undefined, { maximumFractionDigits: 2 })"
                                     :class="(current + (type === 'in' ? 1 : -1) * (Number(qty) || 0)) < 0 ? 'text-danger' : 'text-success'"></div>
                            </div>
                        </div>
                        <small class="text-danger d-block mt-2" x-show="type === 'out' && (Number(qty) || 0) > current" x-cloak>
                            <i class="ti ti-alert-circle me-1"></i> You can't remove more than the current stock.
                        </small>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-light" wire:click="closeStockModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStock" wire:loading.attr="disabled" wire:target="saveStock"
                                :disabled="type === 'out' && (Number(qty) || 0) > current">
                            <span wire:loading.remove wire:target="saveStock"><i class="ti ti-device-floppy me-1"></i> Save</span>
                            <span wire:loading wire:target="saveStock"><span class="spinner-border spinner-border-sm me-1"></span> Saving...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
