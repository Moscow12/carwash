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
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100" role="button" wire:click="$set('paymentStatusFilter', '')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Total Sales</p>
                            <h3 class="mb-0">{{ number_format($totalSales) }}</h3>
                            <small class="opacity-75">transactions</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-receipt fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100" role="button" wire:click="$set('paymentStatusFilter', '')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Total Revenue</p>
                            <h3 class="mb-0">TZS {{ number_format($totalRevenue, 2) }}</h3>
                            <small class="opacity-75">expected</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100" role="button" wire:click="$set('paymentStatusFilter', 'paid')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Paid Amount</p>
                            <h3 class="mb-0">TZS {{ number_format($paidSales, 2) }}</h3>
                            <small class="opacity-75">collected</small>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded-circle p-3">
                            <i class="ti ti-check fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white h-100" role="button" wire:click="$set('paymentStatusFilter', 'unpaid')">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 opacity-75 small">Unpaid Amount</p>
                            <h3 class="mb-0">TZS {{ number_format($unpaidSales, 2) }}</h3>
                            <small class="opacity-75">{{ $unpaidSalesCount + $partialSalesCount }} pending</small>
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
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 small text-muted">Show</label>
                <select wire:model.live="perPage" class="form-select form-select-sm" style="width: auto;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                    <option value="all">All</option>
                </select>
                <label class="mb-0 small text-muted">entries</label>
            </div>
            <div class="text-muted small">
                Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} transactions
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light position-sticky top-0" style="z-index: 1;">
                        <tr>
                            <th>Invoice</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Unpaid</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            @php
                                $paidAmount = (float) $sale->payments->sum('amount');
                                $unpaidAmount = max(0, (float) $sale->total_amount - $paidAmount);
                            @endphp
                            <tr>
                                <td>
                                    <span class="fw-medium text-primary">{{ $sale->invoice_number }}</span>
                                    <br>
                                    <small class="text-muted">{{ $sale->sale_type }}</small>
                                </td>
                                <td class="text-nowrap">
                                    {{ $sale->sale_date ? $sale->sale_date->format('M d, Y') : '-' }}
                                    <br>
                                    <small class="text-muted">{{ $sale->sale_date ? $sale->sale_date->format('H:i') : '' }}</small>
                                </td>
                                <td>
                                    @if($sale->customer)
                                        <span class="fw-medium">{{ Str::limit($sale->customer->name, 15) }}</span>
                                        <br>
                                        <small class="text-muted">{{ $sale->customer->phone ?? '' }}</small>
                                    @else
                                        <span class="text-muted">Walk-in</span>
                                    @endif
                                </td>
                                <td>
                                    @php $itemCount = $sale->items->count(); @endphp
                                    <span class="badge bg-secondary">{{ $itemCount }}</span>
                                    @if($sale->items->first())
                                        <br>
                                        <small class="text-muted">{{ Str::limit($sale->items->first()->item->name ?? '', 12) }}</small>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-nowrap">TZS {{ number_format($sale->total_amount, 2) }}</td>
                                <td class="text-end text-success fw-medium text-nowrap">TZS {{ number_format($paidAmount, 2) }}</td>
                                <td class="text-end {{ $unpaidAmount > 0 ? 'text-danger fw-bold' : 'text-muted' }} text-nowrap">
                                    TZS {{ number_format($unpaidAmount, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $sale->sale_status_badge_class }} mb-1">
                                        {{ ucfirst($sale->sale_status) }}
                                    </span>
                                    <br>
                                    <span class="badge bg-{{ $sale->payment_status_badge_class }}">
                                        {{ ucfirst($sale->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button wire:click="viewSale('{{ $sale->id }}')" class="btn btn-outline-primary" title="View Details">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <button wire:click="showReceipt('{{ $sale->id }}')" class="btn btn-outline-info" title="Print Receipt">
                                            <i class="ti ti-printer"></i>
                                        </button>
                                        @if($sale->payment_status === 'unpaid' || $sale->payment_status === 'partial')
                                            <button wire:click="openAddPaymentModal('{{ $sale->id }}')" class="btn btn-outline-success" title="Add Payment">
                                                <i class="ti ti-cash"></i>
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
                                <td colspan="9" class="text-center py-5">
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
        <div class="card-footer bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Total: {{ $sales->total() }} transactions
            </div>
            @if($sales->hasPages())
                {{ $sales->links() }}
            @endif
        </div>
    </div>

    <!-- Sale Details Modal -->
    @if($showDetailsModal && $selectedSale)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
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

                        <!-- Payment Summary Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white border-0">
                                    <div class="card-body py-2">
                                        <div class="small opacity-75">Total Amount</div>
                                        <div class="fs-5 fw-bold">TZS {{ number_format($selectedSale['total_amount'], 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white border-0">
                                    <div class="card-body py-2">
                                        <div class="small opacity-75">Paid Amount</div>
                                        <div class="fs-5 fw-bold">TZS {{ number_format($selectedSalePaidAmount, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-{{ $selectedSaleUnpaidAmount > 0 ? 'danger' : 'secondary' }} text-white border-0">
                                    <div class="card-body py-2">
                                        <div class="small opacity-75">Unpaid Amount</div>
                                        <div class="fs-5 fw-bold">TZS {{ number_format($selectedSaleUnpaidAmount, 2) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <h6 class="mb-3"><i class="ti ti-package me-1"></i> Items</h6>
                        <div class="table-responsive mb-4">
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

                        <!-- Payment History -->
                        <h6 class="mb-3"><i class="ti ti-cash me-1"></i> Payment History</h6>
                        @if(count($selectedSalePayments) > 0)
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Payment Method</th>
                                            <th class="text-end">Amount</th>
                                            <th>Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedSalePayments as $payment)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('M d, Y') }}</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ $payment['payment_method']['name'] ?? 'N/A' }}
                                                    </span>
                                                </td>
                                                <td class="text-end text-success fw-bold">TZS {{ number_format($payment['amount'], 2) }}</td>
                                                <td>{{ $payment['notes'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="2" class="text-end fw-bold">Total Paid:</td>
                                            <td class="text-end fw-bold text-success">TZS {{ number_format($selectedSalePaidAmount, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning mb-3">
                                <i class="ti ti-alert-circle me-2"></i>
                                No payments recorded yet.
                            </div>
                        @endif

                        @if($selectedSale['notes'])
                            <div class="mt-3">
                                <strong>Notes:</strong>
                                <p class="text-muted mb-0">{{ $selectedSale['notes'] }}</p>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        @if($selectedSaleUnpaidAmount > 0)
                            <button type="button" class="btn btn-success" wire:click="openAddPaymentModal('{{ $selectedSale['id'] }}')">
                                <i class="ti ti-cash me-1"></i> Add Payment
                            </button>
                        @endif
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="ti ti-printer me-1"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Payment Modal -->
    @if($showPaymentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.6); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="ti ti-cash me-2"></i> Add Payment
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Payment Rows -->
                        <div class="payment-rows mb-3">
                            @foreach($paymentRows as $index => $row)
                            <div class="card mb-2 border" wire:key="add-payment-row-{{ $index }}">
                                <div class="card-body p-3">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-5">
                                            <label class="form-label small mb-1">Amount <span class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">TSh</span>
                                                <input type="number"
                                                       wire:model.live="paymentRows.{{ $index }}.amount"
                                                       class="form-control text-end"
                                                       placeholder="0.00"
                                                       min="0"
                                                       step="0.01"
                                                       inputmode="decimal">
                                            </div>
                                        </div>
                                        <div class="col-5">
                                            <label class="form-label small mb-1">Method <span class="text-danger">*</span></label>
                                            <select wire:model="paymentRows.{{ $index }}.payment_method_id" class="form-select form-select-sm">
                                                <option value="">Select</option>
                                                @foreach($availablePaymentMethods as $method)
                                                    <option value="{{ $method['id'] }}">{{ $method['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-2">
                                            @if(count($paymentRows) > 1)
                                            <button wire:click="removePaymentRow({{ $index }})"
                                                    class="btn btn-outline-danger btn-sm w-100"
                                                    title="Remove">
                                                <i class="ti ti-x"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <input type="text"
                                               wire:model="paymentRows.{{ $index }}.note"
                                               class="form-control form-control-sm"
                                               placeholder="Note (optional)">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Add Row Button -->
                        <button wire:click="addPaymentRow" class="btn btn-outline-primary w-100 mb-3">
                            <i class="ti ti-plus me-1"></i> Add Another Payment
                        </button>

                        <!-- Payment Total -->
                        <div class="card bg-light">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <span>Payment Total:</span>
                                    <span class="fw-bold text-success">TZS {{ number_format($this->paymentTotal, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="processPayment" {{ $this->paymentTotal <= 0 ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="processPayment">
                                <i class="ti ti-check me-1"></i> Save Payment
                            </span>
                            <span wire:loading wire:target="processPayment">
                                <span class="spinner-border spinner-border-sm me-1"></span> Processing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Receipt Modal for Thermal Printer -->
    @if($showReceiptModal && $receiptSale)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5); z-index: 1100;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 py-2">
                    <h6 class="modal-title"><i class="ti ti-receipt me-2"></i> Receipt</h6>
                    <button type="button" class="btn-close" wire:click="closeReceiptModal"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Receipt Preview -->
                    <div id="receiptContent" class="receipt-thermal">
                        <!-- Header with Logo -->
                        <div class="receipt-header">
                            @php
                                $showLogo = ($receiptCarwashSettings['show_logo_on_receipt'] ?? false) && ($receiptCarwashInfo['logo'] ?? false);
                                $logoUrl = $receiptCarwashInfo['logo'] ?? null;
                            @endphp
                            @if($showLogo && $logoUrl)
                                <img src="{{ asset('storage/' . $logoUrl) }}" alt="Logo" class="receipt-logo" style="max-width: 120px; max-height: 60px; margin-bottom: 5px;">
                            @endif
                            <div class="shop-name">{{ $receiptCarwashSettings['business_name'] ?? $receiptCarwashInfo['name'] ?? 'SHOP NAME' }}</div>
                            <div class="shop-address">{{ $receiptCarwashSettings['business_address'] ?? $receiptCarwashInfo['address'] ?? '' }}</div>
                            <div class="shop-contact">
                                @if($receiptCarwashSettings['business_phone'] ?? $receiptCarwashInfo['phone'] ?? false)
                                    Mobile: {{ $receiptCarwashSettings['business_phone'] ?? $receiptCarwashInfo['phone'] }}
                                @endif
                            </div>
                        </div>

                        <!-- Custom Receipt Header -->
                        @if($receiptCarwashSettings['receipt_header'] ?? false)
                        <div class="receipt-custom-header">
                            {!! nl2br(e($receiptCarwashSettings['receipt_header'])) !!}
                        </div>
                        @endif

                        <div class="receipt-divider">================================</div>

                        <!-- Invoice Info -->
                        <div class="receipt-info">
                            <div class="receipt-row">
                                <span>Invoice:</span>
                                <span>INV-{{ strtoupper(substr($receiptSale['id'], 0, 8)) }}</span>
                            </div>
                            <div class="receipt-row">
                                <span>Date:</span>
                                <span>{{ \Carbon\Carbon::parse($receiptSale['sale_date'])->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="receipt-row">
                                <span>Customer:</span>
                                <span>{{ $receiptSale['customer']['name'] ?? 'Walk-In' }}</span>
                            </div>
                            <div class="receipt-row">
                                <span>Served by:</span>
                                <span>{{ $receiptSale['user']['name'] ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="receipt-divider">================================</div>

                        <!-- Items Header -->
                        <div class="receipt-items-header">
                            <span class="item-name">ITEM</span>
                            <span class="item-qty">QTY</span>
                            <span class="item-price">PRICE</span>
                            <span class="item-total">TOTAL</span>
                        </div>
                        <div class="receipt-divider-thin">--------------------------------</div>

                        <!-- Items -->
                        <div class="receipt-items">
                            @foreach($receiptSaleItems as $item)
                            @php
                                $itemQty = $item['quantity'] ?? 1;
                                $itemPrice = (float) ($item['price'] ?? 0);
                                $itemTotal = $itemPrice * $itemQty;
                            @endphp
                            <div class="receipt-item">
                                <div class="item-name-full">{{ $item['item']['name'] ?? 'Item' }}</div>
                                <div class="item-details">
                                    <span class="item-qty">{{ number_format($itemQty, 0) }}</span>
                                    <span class="item-price">{{ number_format($itemPrice, 0) }}</span>
                                    <span class="item-total">{{ number_format($itemTotal, 0) }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="receipt-divider">================================</div>

                        <!-- Totals -->
                        <div class="receipt-totals">
                            @php
                                $subtotal = collect($receiptSaleItems)->sum(function($item) {
                                    $qty = $item['quantity'] ?? 1;
                                    $price = (float) ($item['price'] ?? 0);
                                    return $price * $qty;
                                });
                                $discount = (float) ($receiptSale['discount_amount'] ?? 0);
                                $totalPaid = collect($receiptSalePayments)->sum('amount');
                            @endphp
                            <div class="receipt-row">
                                <span>Subtotal:</span>
                                <span>TZS {{ number_format($subtotal, 0) }}</span>
                            </div>
                            @if($discount > 0)
                            <div class="receipt-row">
                                <span>Discount:</span>
                                <span>-TZS {{ number_format($discount, 0) }}</span>
                            </div>
                            @endif
                            <div class="receipt-row receipt-total">
                                <span>TOTAL:</span>
                                <span>TZS {{ number_format($receiptSale['total_amount'], 0) }}</span>
                            </div>
                        </div>

                        <!-- Payments -->
                        @if(count($receiptSalePayments) > 0)
                        <div class="receipt-divider-thin">--------------------------------</div>
                        <div class="receipt-payments">
                            @foreach($receiptSalePayments as $payment)
                            <div class="receipt-row">
                                <span>{{ $payment['payment_method']['name'] ?? 'Payment' }}:</span>
                                <span>TZS {{ number_format($payment['amount'], 0) }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        @php
                            $balance = (float) $receiptSale['total_amount'] - $totalPaid;
                        @endphp
                        @if($balance > 0)
                        <div class="receipt-row receipt-balance">
                            <span>BALANCE:</span>
                            <span>TZS {{ number_format($balance, 0) }}</span>
                        </div>
                        @elseif($totalPaid > $receiptSale['total_amount'])
                        <div class="receipt-row receipt-change">
                            <span>CHANGE:</span>
                            <span>TZS {{ number_format($totalPaid - $receiptSale['total_amount'], 0) }}</span>
                        </div>
                        @endif

                        <div class="receipt-divider">================================</div>

                        <!-- Footer -->
                        <div class="receipt-footer">
                            @if($receiptCarwashSettings['receipt_footer'] ?? false)
                                <div class="custom-footer">{!! nl2br(e($receiptCarwashSettings['receipt_footer'])) !!}</div>
                            @else
                                <div>Thank you for your business!</div>
                                <div class="small">Please visit again</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 py-2">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeReceiptModal">
                        <i class="ti ti-x me-1"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="printThermalReceipt()">
                        <i class="ti ti-printer me-1"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Receipt Styles -->
    <style>
        .receipt-thermal {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 280px;
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            line-height: 1.3;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 5px;
        }
        .receipt-logo {
            max-width: 120px;
            max-height: 60px;
            margin-bottom: 5px;
        }
        .receipt-header .shop-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        .receipt-header .shop-address,
        .receipt-header .shop-contact {
            font-size: 11px;
        }
        .receipt-custom-header {
            text-align: center;
            font-size: 10px;
            margin: 5px 0;
            font-style: italic;
        }
        .receipt-divider {
            text-align: center;
            letter-spacing: -1px;
            margin: 5px 0;
        }
        .receipt-divider-thin {
            text-align: center;
            letter-spacing: -1px;
            margin: 3px 0;
            opacity: 0.6;
        }
        .receipt-info .receipt-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .receipt-items-header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 10px;
        }
        .receipt-items-header .item-name { flex: 2; }
        .receipt-items-header .item-qty { flex: 0.5; text-align: right; }
        .receipt-items-header .item-price { flex: 1; text-align: right; }
        .receipt-items-header .item-total { flex: 1; text-align: right; }
        .receipt-item {
            margin-bottom: 3px;
        }
        .receipt-item .item-name-full {
            font-size: 11px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .receipt-item .item-details {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            font-size: 11px;
        }
        .receipt-item .item-details .item-qty { width: 30px; text-align: right; }
        .receipt-item .item-details .item-price { width: 60px; text-align: right; }
        .receipt-item .item-details .item-total { width: 70px; text-align: right; }
        .receipt-totals .receipt-row,
        .receipt-payments .receipt-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }
        .receipt-total {
            font-weight: bold;
            font-size: 13px !important;
        }
        .receipt-balance {
            font-weight: bold;
            color: #dc3545;
        }
        .receipt-change {
            font-weight: bold;
            color: #198754;
        }
        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }
        @media print {
            body * { visibility: hidden; }
            #printableReceipt, #printableReceipt * { visibility: visible; }
            #printableReceipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 80mm;
            }
        }
    </style>

    <script>
        function printThermalReceipt() {
            var content = document.getElementById('receiptContent').innerHTML;
            var printWindow = window.open('', '_blank', 'width=320,height=600');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Receipt</title>
                    <style>
                        @page { size: 80mm auto; margin: 0; }
                        * { margin: 0; padding: 0; box-sizing: border-box; }
                        html, body { width: 100%; height: 100%; }
                        body {
                            font-family: 'Courier New', Courier, monospace;
                            font-size: 12px;
                            background: #fff;
                            display: flex;
                            justify-content: center;
                            line-height: 1.3;
                        }
                        .receipt-wrapper {
                            width: 80mm;
                            padding: 5mm;
                        }
                        .receipt-header { text-align: center; margin-bottom: 5px; }
                        .receipt-logo { max-width: 120px; max-height: 60px; margin-bottom: 5px; }
                        .receipt-header .shop-name { font-weight: bold; font-size: 14px; text-transform: uppercase; }
                        .receipt-header .shop-address, .receipt-header .shop-contact { font-size: 11px; }
                        .receipt-custom-header { text-align: center; font-size: 10px; margin: 5px 0; font-style: italic; }
                        .receipt-divider { text-align: center; letter-spacing: -1px; margin: 5px 0; }
                        .receipt-divider-thin { text-align: center; letter-spacing: -1px; margin: 3px 0; opacity: 0.6; }
                        .receipt-info .receipt-row { display: flex; justify-content: space-between; font-size: 11px; }
                        .receipt-items-header { display: flex; justify-content: space-between; font-weight: bold; font-size: 10px; }
                        .receipt-items-header .item-name { flex: 2; }
                        .receipt-items-header .item-qty { flex: 0.5; text-align: right; }
                        .receipt-items-header .item-price { flex: 1; text-align: right; }
                        .receipt-items-header .item-total { flex: 1; text-align: right; }
                        .receipt-item { margin-bottom: 3px; }
                        .receipt-item .item-name-full { font-size: 11px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
                        .receipt-item .item-details { display: flex; justify-content: flex-end; gap: 10px; font-size: 11px; }
                        .receipt-item .item-details .item-qty { width: 30px; text-align: right; }
                        .receipt-item .item-details .item-price { width: 60px; text-align: right; }
                        .receipt-item .item-details .item-total { width: 70px; text-align: right; }
                        .receipt-totals .receipt-row, .receipt-payments .receipt-row { display: flex; justify-content: space-between; font-size: 11px; }
                        .receipt-total { font-weight: bold; font-size: 13px !important; }
                        .receipt-balance { font-weight: bold; }
                        .receipt-change { font-weight: bold; }
                        .receipt-footer { text-align: center; margin-top: 10px; font-size: 11px; }
                    </style>
                </head>
                <body>
                    <div class="receipt-wrapper">
                        ${content}
                    </div>
                    <script>
                        window.onload = function() {
                            window.print();
                            setTimeout(function() { window.close(); }, 500);
                        };
                    <\/script>
                </body>
                </html>
            `);
            printWindow.document.close();
        }

    </script>
</div>
