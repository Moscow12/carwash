<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Sales</h4>
            <p class="text-muted mb-0">View and manage all sales transactions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.posscreen') }}" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Sale (POS)
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Sales</p>
                            <h3 class="mb-0">{{ number_format($totalSales) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Total Revenue</p>
                            <h3 class="mb-0">TZS {{ number_format($totalRevenue) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Paid Amount</p>
                            <h3 class="mb-0">TZS {{ number_format($paidSales) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75">Unpaid Amount</p>
                            <h3 class="mb-0">TZS {{ number_format($unpaidSales) }}</h3>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label small">Carwash</label>
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($carwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Sale Status</label>
                    <select wire:model.live="saleStatusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="completed">Completed</option>
                        <option value="pending">Pending</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Payment Status</label>
                    <select wire:model.live="paymentStatusFilter" class="form-select">
                        <option value="">All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">From Date</label>
                    <input type="date" wire:model.live="dateFrom" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To Date</label>
                    <input type="date" wire:model.live="dateTo" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Search</label>
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Customer, plate...">
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="text-end">Amount</th>
                            <th class="text-center">Sale Status</th>
                            <th class="text-center">Payment</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>
                                    <span class="fw-medium text-primary">{{ $sale->invoice_number }}</span>
                                    <br>
                                    <small class="text-muted">{{ $sale->sale_type }}</small>
                                </td>
                                <td>
                                    {{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : '-' }}
                                    <br>
                                    <small class="text-muted">{{ $sale->sale_date ? $sale->sale_date->format('H:i') : '' }}</small>
                                </td>
                                <td>
                                    @if($sale->customer)
                                        <span class="fw-medium">{{ $sale->customer->name }}</span>
                                        <br>
                                        <small class="text-muted">{{ $sale->customer->phone ?? '' }}</small>
                                    @else
                                        <span class="text-muted">Walk-in Customer</span>
                                    @endif
                                </td>
                                <td>
                                    @php $itemCount = $sale->items->count(); @endphp
                                    <span class="badge bg-secondary">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }}</span>
                                    @if($sale->items->first())
                                        <br>
                                        <small class="text-muted">{{ $sale->items->first()->item->name ?? '' }}</small>
                                        @if($itemCount > 1)
                                            <small class="text-muted">+{{ $itemCount - 1 }} more</small>
                                        @endif
                                    @endif
                                </td>
                                <td class="text-end fw-bold">TZS {{ number_format($sale->total_amount) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $sale->sale_status_badge_class }}">
                                        {{ ucfirst($sale->sale_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $sale->payment_status_badge_class }}">
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="viewSale('{{ $sale->id }}')" class="btn btn-outline-primary" title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        @if($sale->payment_status === 'unpaid')
                                            <button wire:click="markAsPaid('{{ $sale->id }}')" class="btn btn-outline-success" title="Mark as Paid">
                                                <i class="ti ti-check"></i>
                                            </button>
                                        @endif
                                        @if($sale->sale_status !== 'canceled')
                                            <button wire:click="cancelSale('{{ $sale->id }}')" wire:confirm="Are you sure you want to cancel this sale?" class="btn btn-outline-danger" title="Cancel Sale">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="ti ti-receipt-off fs-1 d-block mb-2"></i>
                                        No sales found for the selected filters
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($sales->hasPages())
            <div class="card-footer bg-transparent">
                {{ $sales->links() }}
            </div>
        @endif
    </div>

    <!-- Sale Details Modal -->
    @if($showDetailsModal && $selectedSale)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-receipt me-2"></i>
                            Sale Details - {{ $selectedSale['invoice_number'] ?? 'INV-' . strtoupper(substr($selectedSale['id'], 0, 8)) }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Sale Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Date:</strong> {{ \Carbon\Carbon::parse($selectedSale['sale_date'])->format('M d, Y H:i') }}</p>
                                <p class="mb-1"><strong>Type:</strong> {{ ucfirst($selectedSale['sale_type'] ?? '-') }}</p>
                                <p class="mb-1"><strong>Customer:</strong> {{ $selectedSale['customer']['name'] ?? 'Walk-in Customer' }}</p>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <p class="mb-1">
                                    <strong>Sale Status:</strong>
                                    <span class="badge bg-{{ $selectedSale['sale_status'] === 'completed' ? 'success' : ($selectedSale['sale_status'] === 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($selectedSale['sale_status']) }}
                                    </span>
                                </p>
                                <p class="mb-1">
                                    <strong>Payment:</strong>
                                    <span class="badge bg-{{ $selectedSale['payment_status'] === 'paid' ? 'success' : ($selectedSale['payment_status'] === 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($selectedSale['payment_status']) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <h6 class="mb-3">Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Plate Number</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedSaleItems as $item)
                                        <tr>
                                            <td>{{ $item['item']['name'] ?? '-' }}</td>
                                            <td>{{ $item['plate_number'] ?? '-' }}</td>
                                            <td class="text-center">{{ $item['quantity'] }}</td>
                                            <td class="text-end">TZS {{ number_format($item['price']) }}</td>
                                            <td class="text-end">TZS {{ number_format($item['discount'] ?? 0) }}</td>
                                            <td class="text-end fw-bold">TZS {{ number_format(($item['price'] * $item['quantity']) - ($item['discount'] ?? 0)) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Total Amount:</td>
                                        <td class="text-end fw-bold text-primary">TZS {{ number_format($selectedSale['total_amount']) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($selectedSale['notes'])
                            <div class="mt-3">
                                <strong>Notes:</strong>
                                <p class="text-muted mb-0">{{ $selectedSale['notes'] }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
