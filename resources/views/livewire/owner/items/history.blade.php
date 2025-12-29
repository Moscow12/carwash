<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Product stock history</h4>
            <p class="text-muted mb-0">View all stock transactions for your products</p>
        </div>
        <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to Products
        </a>
    </div>

    <!-- Item Selection Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            @if($item)
                <h5 class="mb-3">{{ $item->name }}</h5>
            @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product:</label>
                    <select wire:model.live="itemId" class="form-select">
                        <option value="">Select Product</option>
                        @foreach($itemsList as $listItem)
                            <option value="{{ $listItem->id }}">{{ $listItem->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Business Location:</label>
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">Select Location</option>
                        @foreach($carwashes as $carwash)
                            <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if($item && $itemId)
        <!-- Summary Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="mb-4">{{ $item->name }} @if($item->sku ?? false)({{ $item->sku }})@endif</h5>

                <div class="row">
                    <!-- Quantities In -->
                    <div class="col-md-4">
                        <h6 class="text-primary mb-3">Quantities In</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Total Purchase</td>
                                <td class="text-end fw-medium">{{ number_format($totalPurchase, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Opening Stock</td>
                                <td class="text-end fw-medium">{{ number_format($openingStock, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Sell Return</td>
                                <td class="text-end fw-medium">{{ number_format($totalSellReturn, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Stock Transfers (In)</td>
                                <td class="text-end fw-medium">{{ number_format($stockTransfersIn, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Quantities Out -->
                    <div class="col-md-4">
                        <h6 class="text-danger mb-3">Quantities Out</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Total Sold</td>
                                <td class="text-end fw-medium">{{ number_format($totalSold, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Stock Adjustment</td>
                                <td class="text-end fw-medium">{{ number_format($totalAdjustment, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Purchase Return</td>
                                <td class="text-end fw-medium">{{ number_format($totalPurchaseReturn, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Stock Transfers (Out)</td>
                                <td class="text-end fw-medium">{{ number_format($stockTransfersOut, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="col-md-4">
                        <h6 class="text-success mb-3">Totals</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Current stock</td>
                                <td class="text-end fw-bold fs-5 text-success">{{ number_format($currentStock, 2) }} {{ $item->unit?->symbol ?? 'Pc(s)' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table Card -->
        <div class="card border-0 shadow-sm">
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
                    <div class="col-md-3">
                        <label class="form-label small">Type</label>
                        <select wire:model.live="typeFilter" class="form-select">
                            <option value="">All Types</option>
                            <option value="sale">Sale</option>
                            <option value="purchase">Purchase</option>
                            <option value="restock">Restock</option>
                            <option value="adjustment">Adjustment</option>
                            <option value="initial_stock">Initial Stock</option>
                            <option value="return">Return</option>
                            <option value="refund">Refund</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="ti ti-printer me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th class="text-end">Quantity change</th>
                                <th class="text-end">New Quantity</th>
                                <th>Date</th>
                                <th>Reference No</th>
                                <th>Customer/Supplier</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                @php
                                    $info = $this->getTransactionInfo($transaction);
                                    $qtyChange = $transaction->quantity_changed ?? ($transaction->current_balance - $transaction->previous_balance);
                                    $isPositive = $qtyChange >= 0;
                                @endphp
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $transaction->stock_type === 'in' ? 'success' : 'danger' }}-subtle text-{{ $transaction->stock_type === 'in' ? 'success' : 'danger' }}">
                                            {{ $transaction->transaction_type_label }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="{{ $isPositive ? 'text-success' : 'text-danger' }} fw-medium">
                                            {{ $isPositive ? '+' : '' }}{{ number_format($qtyChange, 2) }}
                                        </span>
                                    </td>
                                    <td class="text-end fw-medium">{{ number_format($transaction->current_balance, 2) }}</td>
                                    <td>{{ $transaction->created_at->format('m/d/Y H:i') }}</td>
                                    <td>
                                        <span class="text-muted">{{ $info['reference'] }}</span>
                                    </td>
                                    <td>{{ $info['customer_supplier'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="ti ti-history-off fs-1 d-block mb-2"></i>
                                            No transaction history found
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($transactions instanceof \Illuminate\Pagination\LengthAwarePaginator && $transactions->hasPages())
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries
                    </div>
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- No Item Selected -->
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="text-muted">
                    <i class="ti ti-package fs-1 d-block mb-2"></i>
                    Please select a product to view its stock history
                </div>
            </div>
        </div>
    @endif
</div>
