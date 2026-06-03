<div>
    {{-- Flash --}}
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

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Rent Roll</h3>
            <p class="text-muted mb-0">Paid &amp; unpaid rent per agreement for <strong>{{ $rollMonthLabel }}</strong></p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness || $agreements->isEmpty())>
            <i class="ti ti-plus me-1"></i> Record Payment
        </button>
    </div>

    {{-- Business Selector --}}
    @if(count($businesses) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <label class="form-label mb-1 small text-muted">Rental Business</label>
                    <select wire:model.live="selectedBusiness" class="form-select">
                        <option value="">Choose business...</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    @if($selectedBusiness && $agreements->isEmpty())
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <small>No tenancy agreements yet — register one under
                                <a href="{{ route('owner.rental.agreements') }}" class="alert-link">Tenancy Agreements</a> first.
                            </small>
                        </div>
                    @elseif($selectedBusiness)
                        <div class="alert alert-info mb-0 py-2">
                            <i class="ti ti-info-circle me-2"></i>
                            <small>Receipts under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats (for the selected roll month) --}}
    <div class="row g-3 mb-4">
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Expected ({{ $rollMonthLabel }})</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['expected'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-coin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Collected</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['collected'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-danger-subtle text-danger rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-alert-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Outstanding</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['outstanding'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-home-x fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Unpaid / Partial</div>
                        <div class="h5 mb-0">{{ number_format($stats['unpaid_count']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">Roll month</label>
                    <input type="month" wire:model.live="rollMonth" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label mb-1 small text-muted">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Tenant or unit number…">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1 small text-muted">Status</label>
                    <select wire:model.live="rollStatusFilter" class="form-select">
                        <option value="">All</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partially paid</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <label class="form-label mb-1 small text-muted d-block">Agreements</label>
                    <span class="text-muted">{{ $roll->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rent Roll Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Tenant</th>
                        <th>Unit</th>
                        <th>Billing</th>
                        <th class="text-end">Period Due</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roll as $row)
                    @php
                        $a = $row['agreement'];
                        [$badge, $label] = match($row['status']) {
                            'paid' => ['success', 'Paid'],
                            'partial' => ['warning', 'Partial'],
                            default => ['danger', 'Unpaid'],
                        };
                    @endphp
                    <tr wire:key="roll-{{ $a->id }}">
                        <td class="ps-3">
                            <div class="fw-medium">
                                {{ $a->customer?->name ?? '—' }}
                                @if($a->agreement_status !== 'active')
                                    @php $agColor = $a->agreement_status === 'draft' ? 'secondary' : 'warning'; @endphp
                                    <span class="badge bg-{{ $agColor }}-subtle text-{{ $agColor }} ms-1">{{ ucfirst($a->agreement_status) }}</span>
                                @endif
                            </div>
                            <small class="text-muted">{{ $a->customer?->phone }}</small>
                        </td>
                        <td>
                            <small>{{ $a->unit?->property?->property_name }}</small><br>
                            <small class="text-muted">Unit {{ $a->unit?->unit_number ?? '—' }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ ucwords(str_replace('_',' ', $a->payment_frequency)) }}</span>
                            @if(($row['period_months'] ?? 1) > 1)
                                <div><small class="text-muted">{{ $row['period_months'] }} mo × TZS {{ number_format($a->rent_amount, 0) }}</small></div>
                            @endif
                        </td>
                        <td class="text-end">TZS {{ number_format($row['due'], 0) }}</td>
                        <td class="text-end text-success">TZS {{ number_format($row['paid'], 0) }}</td>
                        <td class="text-end fw-bold {{ $row['remaining'] > 0 ? 'text-danger' : 'text-muted' }}">
                            TZS {{ number_format($row['remaining'], 0) }}
                        </td>
                        <td><span class="badge bg-{{ $badge }}-subtle text-{{ $badge }}">{{ $label }}</span></td>
                        <td class="text-end pe-3">
                            @if($row['remaining'] > 0)
                                <button wire:click="openRecordModal('{{ $a->id }}')" class="btn btn-sm btn-primary">
                                    <i class="ti ti-plus me-1"></i> Record Payment
                                </button>
                            @else
                                <button wire:click="openAgreementReceipts('{{ $a->id }}')" class="btn btn-sm btn-outline-secondary">
                                    <i class="ti ti-receipt me-1"></i> Receipts
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                <i class="ti ti-home-2 fs-2"></i>
                            </div>
                            <div class="small">
                                @if(!$selectedBusiness)
                                    Select a rental business to view the rent roll.
                                @else
                                    No agreements with rent due in {{ $rollMonthLabel }} for these filters.
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($roll->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $roll->links() }}</div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Receipt' : 'Record Rent Payment' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="savePayment">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Tenancy Agreement <span class="text-danger">*</span></label>
                                <select wire:model.live="tenancy_agreement_id" class="form-select @error('tenancy_agreement_id') is-invalid @enderror">
                                    <option value="">Choose agreement...</option>
                                    @foreach($agreements as $a)
                                        <option value="{{ $a->id }}">
                                            {{ $a->customer?->name }} · Unit {{ $a->unit?->unit_number }}
                                            (TZS {{ number_format($a->rent_amount, 0) }}/{{ $a->payment_frequency }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('tenancy_agreement_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="payment_date" class="form-control @error('payment_date') is-invalid @enderror">
                                @error('payment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">For Month <span class="text-danger">*</span></label>
                                <input type="month" wire:model="payment_for_month" class="form-control @error('payment_for_month') is-invalid @enderror">
                                @error('payment_for_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Saved as the first of the month.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="amount_paid" class="form-control @error('amount_paid') is-invalid @enderror" placeholder="0.00">
                                @error('amount_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select wire:model="payment_method_id" class="form-select @error('payment_method_id') is-invalid @enderror">
                                    <option value="">— Choose method —</option>
                                    @foreach($methods as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($methods->isEmpty())
                                    <small class="text-warning"><i class="ti ti-alert-triangle me-1"></i>No payment methods configured. Receipts without a method won't appear in the unified ledger.</small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Reference No</label>
                                <input type="text" wire:model="reference_no" class="form-control @error('reference_no') is-invalid @enderror" placeholder="M-Pesa TxID, cheque #, etc.">
                                @error('reference_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Receipt Channel</label>
                                <select wire:model="receipt_channel" class="form-select">
                                    <option value="none">No receipt sent</option>
                                    <option value="print">Print</option>
                                    <option value="email">Email</option>
                                    <option value="sms">SMS</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                                <small class="text-muted">Recorded in the unified payments ledger.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Optional"></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="savePayment">
                                <span wire:loading.remove wire:target="savePayment">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update Receipt' : 'Record Payment' }}
                                </span>
                                <span wire:loading wire:target="savePayment">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving…
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- View Modal --}}
    @if($showViewModal && $viewPayment)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-receipt me-2"></i>Receipt</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="display-6 fw-bold text-success">TZS {{ number_format($viewPayment->amount_paid, 0) }}</div>
                        <div class="text-muted">for {{ $viewPayment->payment_for_month?->format('M Y') }}</div>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user me-2"></i>Tenant</span>
                            <span class="fw-medium">{{ $viewPayment->agreement?->customer?->name ?? '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-home-2 me-2"></i>Unit</span>
                            <span class="fw-medium">{{ $viewPayment->agreement?->unit?->property?->property_name }} · {{ $viewPayment->agreement?->unit?->unit_number }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i>Paid on</span>
                            <span class="fw-medium">{{ $viewPayment->payment_date?->format('M d, Y') }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-credit-card me-2"></i>Method</span>
                            <span class="fw-medium">{{ $viewPayment->paymentMethod?->name ?? '—' }}</span>
                        </div>
                        @if($viewPayment->reference_no)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-hash me-2"></i>Reference</span>
                            <span class="fw-medium">{{ $viewPayment->reference_no }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user-check me-2"></i>Received by</span>
                            <span class="fw-medium">{{ $viewPayment->receivedBy?->name ?? '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-link me-2"></i>Unified Ledger</span>
                            @if($viewPayment->payment)
                                <span class="badge bg-success-subtle text-success">Linked ({{ $viewPayment->payment->receipt_channel }})</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Not linked</span>
                            @endif
                        </div>
                        @if($viewPayment->notes)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-note me-2"></i>Notes</span>
                            <span>{{ $viewPayment->notes }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        <button wire:click="openEditModal('{{ $viewPayment->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Agreement Receipts Modal --}}
    @if($showReceiptsModal && $receiptsAgreement)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-receipt me-2"></i>
                        Receipts — {{ $receiptsAgreement->customer?->name }}
                        <small class="text-muted">· Unit {{ $receiptsAgreement->unit?->unit_number }}</small>
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeReceiptsModal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-3">
                        <button wire:click="openRecordModal('{{ $receiptsAgreement->id }}')" class="btn btn-sm btn-primary">
                            <i class="ti ti-plus me-1"></i> Record Payment
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>For Month</th>
                                    <th class="text-end">Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th class="text-end"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($receiptsAgreement->rentPayments as $p)
                                <tr wire:key="receipt-{{ $p->id }}">
                                    <td>{{ $p->payment_date?->format('M d, Y') }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $p->payment_for_month?->format('M Y') }}</span></td>
                                    <td class="text-end fw-bold">TZS {{ number_format($p->amount_paid, 0) }}</td>
                                    <td><small>{{ $p->paymentMethod?->name ?? '—' }}</small></td>
                                    <td><small class="text-muted">{{ $p->reference_no ?? '—' }}</small></td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $p->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                                <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $p->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="voidPayment('{{ $p->id }}')" wire:confirm="Void this receipt? The mirrored ledger entry will also be voided.">
                                                    <i class="ti ti-ban me-2"></i>Void
                                                </a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4 small">No receipts recorded for this agreement yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4">
                        <button wire:click="closeReceiptsModal" class="btn btn-light w-100">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
