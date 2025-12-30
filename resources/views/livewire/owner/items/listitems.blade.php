<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Products</h4>
            <p class="text-muted mb-0">Manage your products</p>
        </div>
    </div>

    <!-- Flash Messages -->
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

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Items</p>
                            <h3 class="mb-0">{{ number_format($totalItems) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-package fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Services</p>
                            <h3 class="mb-0">{{ number_format($totalServices) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-tools fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Products</p>
                            <h3 class="mb-0">{{ number_format($totalProducts) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-box fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent">
            <h6 class="mb-0 text-primary"><i class="ti ti-filter me-2"></i>Filters</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Product Type:</label>
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All</option>
                        <option value="Service">Service</option>
                        <option value="product">Product</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Category:</label>
                    <select wire:model.live="categoryFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Unit:</label>
                    <select wire:model.live="unitFilter" class="form-select">
                        <option value="">All</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Business Location:</label>
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All</option>
                        @foreach($carwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs and Table Card -->
    <div class="card border-0 shadow-sm">
        <!-- Tabs -->
        <div class="card-header bg-transparent border-bottom">
            <ul class="nav nav-tabs card-header-tabs">
                <li class="nav-item">
                    <button wire:click="setTab('products')" class="nav-link {{ $activeTab === 'products' ? 'active' : '' }}">
                        <i class="ti ti-package me-1"></i> All Products
                    </button>
                </li>
                <li class="nav-item">
                    <button wire:click="setTab('stock')" class="nav-link {{ $activeTab === 'stock' ? 'active' : '' }}">
                        <i class="ti ti-chart-bar me-1"></i> Stock Report
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body">
            <!-- Table Controls -->
            <div class="row g-3 mb-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Show</label>
                    <select wire:model.live="perPage" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
                <div class="col-md-3 text-end">
                    <a href="{{ route('owner.itemregister') }}" class="btn btn-success">
                        <i class="ti ti-plus me-1"></i> Add
                    </a>
                </div>
                <div class="col-md-3">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search...">
                </div>
            </div>

            <!-- Products Table -->
            @if($activeTab === 'products')
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px;"></th>
                                <th>Action</th>
                                <th>Product</th>
                                <th>Business Location</th>
                                <th class="text-end">Cost Price</th>
                                <th class="text-end">Selling Price</th>
                                <th class="text-end">Current Stock</th>
                                <th>Product Type</th>
                                <th>Category</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    <td>
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="rounded" style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ti ti-photo text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-info dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button class="dropdown-item" wire:click="viewItem('{{ $item->id }}')">
                                                        <i class="ti ti-eye me-2"></i> View
                                                    </button>
                                                </li>
                                                <li>
                                                    <a href="{{ route('owner.items.edit', ['itemId' => $item->id]) }}" class="dropdown-item">
                                                        <i class="ti ti-edit me-2"></i> Edit
                                                    </a>
                                                </li>
                                                @if($item->type !== 'Service')
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <button class="dropdown-item" wire:click="openStockModal('{{ $item->id }}')">
                                                            <i class="ti ti-package me-2"></i> Add/Edit Stock
                                                        </button>
                                                    </li>
                                                @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a href="{{ route('owner.history', ['itemId' => $item->id]) }}" class="dropdown-item">
                                                            <i class="ti ti-history me-2"></i> Item History
                                                        </a>
                                                    </li>
                                                
                                               
                                                <li><hr class="dropdown-divider"></li>
                                                 <li>
                                                    <button class="dropdown-item" wire:click="toggleStatus('{{ $item->id }}')">
                                                        <i class="ti ti-toggle-{{ $item->status === 'active' ? 'right' : 'left' }} me-2"></i>
                                                        {{ $item->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item text-danger" wire:click="delete('{{ $item->id }}')" wire:confirm="Are you sure you want to delete this item?">
                                                        <i class="ti ti-trash me-2"></i> Delete
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $item->name }}</span>
                                        @if($item->status === 'inactive')
                                            <span class="badge bg-secondary ms-1">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->carwash?->name ?? '-' }}</td>
                                    <td class="text-end">TZS {{ number_format($item->cost_price, 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($item->selling_price, 2) }}</td>
                                    <td class="text-end">
                                        @if($item->type === 'Service')
                                            <span class="text-muted">N/A</span>
                                        @else
                                            @php $stock = $stockData[$item->id] ?? 0; @endphp
                                            <span class="{{ $stock <= 0 ? 'text-danger' : '' }}">
                                                {{ number_format($stock, 2) }} {{ $item->unit?->symbol ?? $item->unit?->name ?? '' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item->type === 'Service' ? 'primary' : 'info' }}">
                                            {{ $item->type === 'Service' ? 'Service' : 'Single' }}
                                        </span>
                                    </td>
                                    <td>{{ $item->category?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="ti ti-package-off fs-1 d-block mb-2"></i>
                                            No products found
                                            <br>
                                            <a href="{{ route('owner.itemregister') }}" class="btn btn-success btn-sm mt-2">
                                                <i class="ti ti-plus me-1"></i> Add Product
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Stock Report Tab -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-end">Current Stock</th>
                                <th class="text-end">Stock Value (Cost)</th>
                                <th class="text-end">Stock Value (Sell)</th>
                                <th>Status</th>
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
                                <tr>
                                    <td class="fw-medium">{{ $item->name }}</td>
                                    <td>{{ $item->category?->name ?? '-' }}</td>
                                    <td>{{ $item->unit?->name ?? '-' }}</td>
                                    <td class="text-end">
                                        @if($isService)
                                            <span class="text-muted">N/A</span>
                                        @else
                                            <span class="{{ $stock <= 0 ? 'text-danger fw-bold' : '' }}">
                                                {{ number_format($stock, 2) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($isService)
                                            <span class="text-muted">N/A</span>
                                        @else
                                            TZS {{ number_format($stockValueCost, 2) }}
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        @if($isService)
                                            <span class="text-muted">N/A</span>
                                        @else
                                            TZS {{ number_format($stockValueSell, 2) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if($isService)
                                            <span class="badge bg-secondary">Service</span>
                                        @elseif($stock <= 0)
                                            <span class="badge bg-danger">Out of Stock</span>
                                        @elseif($stock < 10)
                                            <span class="badge bg-warning">Low Stock</span>
                                        @else
                                            <span class="badge bg-success">In Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="ti ti-chart-bar-off fs-1 d-block mb-2"></i>
                                            No stock data available
                                        </div>
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
                                    <td colspan="4" class="text-end fw-bold">Total Stock Value:</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($totalStockValueCost, 2) }}</td>
                                    <td class="text-end fw-bold">TZS {{ number_format($totalStockValueSell, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endif
        </div>

        <!-- Pagination -->
        @if($items instanceof \Illuminate\Pagination\LengthAwarePaginator && $items->hasPages())
            <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    Showing {{ $items->firstItem() }} to {{ $items->lastItem() }} of {{ $items->total() }} entries
                </div>
                {{ $items->links() }}
            </div>
        @endif
    </div>

    <!-- View Item Modal -->
    @if($showViewModal && $viewingItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-eye me-2"></i>Product Details
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            @if($viewingItem->image)
                                <img src="{{ asset('storage/' . $viewingItem->image) }}" alt="{{ $viewingItem->name }}" class="rounded" style="max-width: 200px; max-height: 200px; object-fit: cover;">
                            @else
                                <div class="bg-light rounded d-inline-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                    <i class="ti ti-photo fs-1 text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <table class="table table-sm">
                            <tr>
                                <th class="w-50">Name:</th>
                                <td>{{ $viewingItem->name }}</td>
                            </tr>
                            <tr>
                                <th>Description:</th>
                                <td>{{ $viewingItem->description ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Type:</th>
                                <td>
                                    <span class="badge bg-{{ $viewingItem->type === 'Service' ? 'primary' : 'info' }}">
                                        {{ $viewingItem->type }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $viewingItem->category?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Unit:</th>
                                <td>{{ $viewingItem->unit?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Cost Price:</th>
                                <td>TZS {{ number_format($viewingItem->cost_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Selling Price:</th>
                                <td class="fw-bold">TZS {{ number_format($viewingItem->selling_price, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Market Price:</th>
                                <td>{{ $viewingItem->market_price ? 'TZS ' . number_format($viewingItem->market_price, 2) : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Commission:</th>
                                <td>
                                    @if($viewingItem->commission)
                                        {{ $viewingItem->commission_type === 'percentage' ? $viewingItem->commission . '%' : 'TZS ' . number_format($viewingItem->commission, 2) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge bg-{{ $viewingItem->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($viewingItem->status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stock Modal -->
    @if($showStockModal && $stockItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-package me-2"></i>Add/Edit Stock - {{ $stockItem->name }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeStockModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Current Stock:</strong>
                            {{ number_format($this->getCurrentStock($stockItem->id), 2) }} {{ $stockItem->unit?->symbol ?? $stockItem->unit?->name ?? '' }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stock Type <span class="text-danger">*</span></label>
                            <select wire:model="stockType" class="form-select">
                                <option value="in">Stock In (Add)</option>
                                <option value="out">Stock Out (Remove)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" wire:model="stockQuantity" class="form-control @error('stockQuantity') is-invalid @enderror" placeholder="0.00" step="0.01" min="0">
                                <span class="input-group-text">{{ $stockItem->unit?->symbol ?? $stockItem->unit?->name ?? 'Units' }}</span>
                            </div>
                            @error('stockQuantity')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeStockModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveStock">
                            <i class="ti ti-device-floppy me-1"></i> Save Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
