<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="ti ti-package me-2"></i>Bar Stock Management</h4>
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
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Total Items</h6>
                        <h3 class="mb-0">{{ $stats['total_items'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Low Stock</h6>
                        <h3 class="mb-0">{{ $stats['low_stock'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Out of Stock</h6>
                        <h3 class="mb-0">{{ $stats['out_of_stock'] }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h6 class="mb-1">Total Value</h6>
                        <h3 class="mb-0">TSh {{ number_format($stats['total_value'], 0) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" wire:model.live="search" class="form-control" placeholder="Search items...">
            </div>
            <div class="col-md-4">
                <select wire:model.live="stockFilter" class="form-select">
                    <option value="all">All Items</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Menu Item</th>
                                        <th>Category</th>
                                        <th>Stock Item</th>
                                        <th>Current Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Cost Price</th>
                                        <th>Selling Price</th>
                                        <th>Stock Value</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stockItems as $item)
                                        <tr>
                                            <td><strong>{{ $item->menu_item_name }}</strong></td>
                                            <td>{{ $item->category ?? '-' }}</td>
                                            <td>{{ $item->item_name }}</td>
                                            <td>
                                                <span class="badge {{ $item->status === 'out' ? 'bg-danger' : ($item->status === 'low' ? 'bg-warning' : 'bg-success') }}">
                                                    {{ $item->current_stock }} {{ $item->unit }}
                                                </span>
                                            </td>
                                            <td>{{ $item->reorder_level }} {{ $item->unit }}</td>
                                            <td>TSh {{ number_format($item->cost_price, 0) }}</td>
                                            <td>TSh {{ number_format($item->selling_price, 0) }}</td>
                                            <td>TSh {{ number_format($item->stock_value, 0) }}</td>
                                            <td>
                                                @if($item->status === 'out')
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @elseif($item->status === 'low')
                                                    <span class="badge bg-warning">Low Stock</span>
                                                @else
                                                    <span class="badge bg-success">OK</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-4 text-muted">
                                                <i class="ti ti-inbox fs-3 d-block mb-2"></i>
                                                No stock items found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Please select a business and outlet to view stock information.
                </div>
            </div>
        </div>
    @endif
</div>
