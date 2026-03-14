<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Billing & Finance</h4>
            <p class="text-muted mb-0">Manage folios, invoices, payments, and tax configuration</p>
        </div>
        @if($activeTab === 'tax')
            <button wire:click="openTaxModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Tax
            </button>
        @endif
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

    <!-- Hotel Selection -->
    @if($hotels->count() > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Select Hotel</label>
                <select wire:model.live="selectedHotel" class="form-select">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($selectedHotel)
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-file-invoice"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Open Folios</h6>
                                <h3 class="mb-0">{{ $stats['open_folios'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-danger text-white me-3">
                                <i class="ti ti-currency-dollar"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Balance</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_balance'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-cash"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Payments Today</h6>
                                <h3 class="mb-0">{{ number_format($stats['payments_today'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-chart-line"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Revenue Today</h6>
                                <h3 class="mb-0">{{ number_format($stats['revenue_today'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'folios' ? 'active' : '' }}"
                        wire:click="switchTab('folios')"
                        type="button">
                    <i class="ti ti-file-invoice me-1"></i> Folios
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'invoices' ? 'active' : '' }}"
                        wire:click="switchTab('invoices')"
                        type="button">
                    <i class="ti ti-receipt me-1"></i> Invoices
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'payments' ? 'active' : '' }}"
                        wire:click="switchTab('payments')"
                        type="button">
                    <i class="ti ti-cash me-1"></i> Payments
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'tax' ? 'active' : '' }}"
                        wire:click="switchTab('tax')"
                        type="button">
                    <i class="ti ti-percentage me-1"></i> Tax Configuration
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Folios Tab -->
            @if($activeTab === 'folios')
                <!-- Filters -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by folio number or guest...">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select wire:model.live="statusFilter" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="open">Open</option>
                                    <option value="closed">Closed</option>
                                    <option value="settled">Settled</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Folios Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Folio No.</th>
                                        <th>Guest</th>
                                        <th>Charges</th>
                                        <th>Payments</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($folios as $folio)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $folio->folio_no }}</span></td>
                                            <td>
                                                @if($folio->guest)
                                                    <div>{{ $folio->guest->full_name }}</div>
                                                    <small class="text-muted">{{ $folio->guest->email }}</small>
                                                @else
                                                    <span class="text-muted">No Guest</span>
                                                @endif
                                            </td>
                                            <td><span class="text-danger">{{ number_format($folio->total_charges, 2) }}</span></td>
                                            <td><span class="text-success">{{ number_format($folio->total_payments, 2) }}</span></td>
                                            <td>
                                                @if($folio->balance > 0)
                                                    <span class="badge bg-warning">{{ number_format($folio->balance, 2) }}</span>
                                                @elseif($folio->balance < 0)
                                                    <span class="badge bg-info">{{ number_format(abs($folio->balance), 2) }} CR</span>
                                                @else
                                                    <span class="badge bg-success">0.00</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'open' => 'warning',
                                                        'closed' => 'secondary',
                                                        'settled' => 'success',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$folio->status] ?? 'secondary' }}">
                                                    {{ ucfirst($folio->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="viewFolio('{{ $folio->id }}')"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="View Details">
                                                        <i class="ti ti-eye"></i>
                                                    </button>
                                                    @if($folio->status === 'open')
                                                        <button wire:click="openChargeModal('{{ $folio->id }}')"
                                                                class="btn btn-sm btn-outline-danger"
                                                                title="Add Charge">
                                                            <i class="ti ti-plus"></i>
                                                        </button>
                                                        <button wire:click="openPaymentModal('{{ $folio->id }}')"
                                                                class="btn btn-sm btn-outline-success"
                                                                title="Record Payment">
                                                            <i class="ti ti-cash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti ti-file-invoice fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No folios found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-3">
                            {{ $folios->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Invoices Tab -->
            @if($activeTab === 'invoices')
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Invoice No.</th>
                                        <th>Folio</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoices as $invoice)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $invoice->invoice_no }}</span></td>
                                            <td>{{ $invoice->folio->folio_no ?? 'N/A' }}</td>
                                            <td>{{ number_format($invoice->amount, 2) }}</td>
                                            <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($invoice->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" title="Print">
                                                    <i class="ti ti-printer"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="ti ti-receipt fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No invoices found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $invoices->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Payments Tab -->
            @if($activeTab === 'payments')
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Folio</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $payment->folio->folio_no ?? 'N/A' }}</td>
                                            <td><span class="text-success fw-bold">{{ number_format($payment->amount, 2) }}</span></td>
                                            <td>
                                                @php
                                                    $methodColors = [
                                                        'cash' => 'success',
                                                        'card' => 'primary',
                                                        'bank_transfer' => 'info',
                                                        'mobile_money' => 'warning',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $methodColors[$payment->payment_method] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $payment->reference ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" title="Print Receipt">
                                                    <i class="ti ti-printer"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="ti ti-cash fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No payments found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $payments->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tax Configuration Tab -->
            @if($activeTab === 'tax')
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Tax Name</th>
                                        <th>Rate (%)</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($taxes as $tax)
                                        <tr>
                                            <td>{{ $tax->tax_name }}</td>
                                            <td><span class="badge bg-info">{{ $tax->rate }}%</span></td>
                                            <td>
                                                <span class="badge bg-{{ $tax->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($tax->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="editTax('{{ $tax->id }}')"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="deleteTax('{{ $tax->id }}')"
                                                            wire:confirm="Are you sure you want to delete this tax?"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <i class="ti ti-percentage fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No tax configurations found. Click "Add Tax" to create one.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage billing</p>
            </div>
        </div>
    @endif

    <!-- Folio Details Modal -->
    @if($showModal && $selectedFolio)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-file-invoice me-2"></i>
                            Folio Details - {{ $selectedFolio->folio_no }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Guest Info -->
                        @if($selectedFolio->guest)
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="text-primary">Guest Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> {{ $selectedFolio->guest->full_name }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $selectedFolio->guest->email }}</p>
                                    <p class="mb-0"><strong>Phone:</strong> {{ $selectedFolio->guest->phone }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Charges -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Charges</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selectedFolio->charges as $charge)
                                            <tr>
                                                <td>{{ $charge->charge_date->format('M d, Y') }}</td>
                                                <td><span class="badge bg-secondary">{{ ucfirst($charge->charge_type) }}</span></td>
                                                <td>{{ $charge->description }}</td>
                                                <td>{{ number_format($charge->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No charges</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Payments -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Payments</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Method</th>
                                            <th>Reference</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($selectedFolio->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->paid_at->format('M d, Y H:i') }}</td>
                                                <td><span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span></td>
                                                <td>{{ $payment->reference ?? '-' }}</td>
                                                <td>{{ number_format($payment->amount, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No payments</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Summary -->
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Total Charges:</strong> <span class="text-danger">{{ number_format($selectedFolio->total_charges, 2) }}</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Total Payments:</strong> <span class="text-success">{{ number_format($selectedFolio->total_payments, 2) }}</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Balance:</strong>
                                            <span class="badge bg-{{ $selectedFolio->balance > 0 ? 'warning' : 'success' }}">
                                                {{ number_format($selectedFolio->balance, 2) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Add Charge Modal -->
    @if($showChargeModal && $selectedFolio)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Charge to {{ $selectedFolio->folio_no }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="addCharge">
                            <div class="mb-3">
                                <label class="form-label">Charge Type <span class="text-danger">*</span></label>
                                <select wire:model="charge_type" class="form-select @error('charge_type') is-invalid @enderror">
                                    <option value="room">Room</option>
                                    <option value="restaurant">Restaurant</option>
                                    <option value="bar">Bar</option>
                                    <option value="minibar">Minibar</option>
                                    <option value="laundry">Laundry</option>
                                    <option value="telephone">Telephone</option>
                                    <option value="spa">Spa</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('charge_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <input type="text" wire:model="charge_description" class="form-control @error('charge_description') is-invalid @enderror">
                                @error('charge_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" wire:model="charge_amount" class="form-control @error('charge_amount') is-invalid @enderror" step="0.01" min="0">
                                @error('charge_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" wire:model="charge_date" class="form-control">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="addCharge">
                            <i class="ti ti-plus me-1"></i> Add Charge
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Record Payment Modal -->
    @if($showPaymentModal && $selectedFolio)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Record Payment for {{ $selectedFolio->folio_no }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Balance Due:</strong> {{ number_format($selectedFolio->balance, 2) }}
                        </div>
                        <form wire:submit.prevent="recordPayment">
                            <div class="mb-3">
                                <label class="form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" wire:model="payment_amount" class="form-control @error('payment_amount') is-invalid @enderror" step="0.01" min="0">
                                @error('payment_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select wire:model="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="mobile_money">Mobile Money</option>
                                </select>
                                @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Reference</label>
                                <input type="text" wire:model="payment_reference" class="form-control" placeholder="Transaction reference...">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="recordPayment">
                            <i class="ti ti-cash me-1"></i> Record Payment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Tax Configuration Modal -->
    @if($showModal && $activeTab === 'tax')
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-percentage me-2"></i>
                            {{ $editMode ? 'Edit Tax' : 'Add Tax' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveTax">
                            <div class="mb-3">
                                <label class="form-label">Tax Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="tax_name" class="form-control @error('tax_name') is-invalid @enderror" placeholder="e.g., VAT, Service Charge">
                                @error('tax_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror" step="0.01" min="0" max="100">
                                @error('tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="tax_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTax">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Tax' : 'Save Tax' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
