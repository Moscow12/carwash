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
            <h3 class="mb-1">Utility Bills</h3>
            <p class="text-muted mb-0">Issue and settle water / electricity / internet / other bills</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="openBulkModal" class="btn btn-outline-primary" @disabled(!$selectedBusiness || $activeAgreements->isEmpty())>
                <i class="ti ti-stack-2 me-1"></i> Bulk Issue
            </button>
            <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness || $agreements->isEmpty())>
                <i class="ti ti-plus me-1"></i> Issue Bill
            </button>
        </div>
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
                            <small>Bills under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-file-invoice fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ now()->format('M Y') }} Issued</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['issued_this_month'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-coin fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">{{ now()->format('M Y') }} Collected</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['collected_this_month'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-danger-subtle text-danger rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-alert-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Outstanding (all-time)</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['outstanding_total'], 0) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-clock-x fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Unpaid Bills</div>
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
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search tenant…">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="billTypeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="water">Water</option>
                        <option value="electricity">Electricity</option>
                        <option value="internet">Internet</option>
                        <option value="gas">Gas</option>
                        <option value="security">Security</option>
                        <option value="service_charge">Service Charge</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="unpaid">Unpaid</option>
                        <option value="partial">Partial</option>
                        <option value="paid">Paid</option>
                        <option value="waived">Waived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="month" wire:model.live="monthFilter" class="form-control">
                </div>
                <div class="col-md-2">
                    <select wire:model.live="agreementFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Agreements</option>
                        @foreach($agreements as $a)
                            <option value="{{ $a->id }}">{{ $a->customer?->name }} · U{{ $a->unit?->unit_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <span class="text-muted small">{{ $bills->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Month</th>
                        <th>Tenant</th>
                        <th>Unit</th>
                        <th>Type</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Paid On</th>
                        <th class="text-end pe-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    @php
                        $statusColor = match($bill->status) {
                            'paid' => 'success',
                            'partial' => 'warning',
                            'unpaid' => 'danger',
                            'waived' => 'secondary',
                            default => 'secondary',
                        };
                        $typeIcon = match($bill->bill_type) {
                            'water' => 'ti-droplet text-info',
                            'electricity' => 'ti-bolt text-warning',
                            'internet' => 'ti-wifi text-primary',
                            'gas' => 'ti-flame text-danger',
                            'security' => 'ti-shield text-secondary',
                            'service_charge' => 'ti-receipt text-secondary',
                            default => 'ti-circle text-muted',
                        };
                    @endphp
                    <tr>
                        <td class="ps-3"><span class="badge bg-light text-dark border">{{ $bill->billing_month?->format('M Y') }}</span></td>
                        <td><div class="fw-medium">{{ $bill->agreement?->customer?->name ?? '—' }}</div></td>
                        <td><small>{{ $bill->agreement?->unit?->unit_number ?? '—' }}</small></td>
                        <td><i class="ti {{ $typeIcon }} me-1"></i><small>{{ ucwords(str_replace('_', ' ', $bill->bill_type)) }}</small></td>
                        <td class="text-end fw-bold">TZS {{ number_format($bill->amount, 0) }}</td>
                        <td><span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($bill->status) }}</span></td>
                        <td><small class="text-muted">{{ $bill->paid_at?->format('M d, Y') ?? '—' }}</small></td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $bill->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                    @if($bill->status !== 'paid' && $bill->status !== 'waived')
                                    <li><a class="dropdown-item text-success" href="#" wire:click.prevent="openSettleModal('{{ $bill->id }}')"><i class="ti ti-cash me-2"></i>Settle (mark paid)</a></li>
                                    @endif
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $bill->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><small class="dropdown-header text-muted">Set status</small></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $bill->id }}','unpaid')"><i class="ti ti-clock me-2"></i>Mark Unpaid</a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $bill->id }}','partial')"><i class="ti ti-circle-half me-2"></i>Mark Partial</a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $bill->id }}','waived')" wire:confirm="Waive this bill?"><i class="ti ti-ban me-2"></i>Waive</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteBill('{{ $bill->id }}')" wire:confirm="Delete this bill?">
                                        <i class="ti ti-trash me-2"></i>Delete
                                    </a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                <i class="ti ti-file-invoice fs-2"></i>
                            </div>
                            <div class="small">No bills for these filters.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($bills->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $bills->links() }}</div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Bill' : 'Issue Utility Bill' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveBill">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Tenancy Agreement <span class="text-danger">*</span></label>
                                <select wire:model="tenancy_agreement_id" class="form-select @error('tenancy_agreement_id') is-invalid @enderror" @disabled($editMode)>
                                    <option value="">Choose agreement...</option>
                                    @foreach($agreements as $a)
                                        <option value="{{ $a->id }}">{{ $a->customer?->name }} · Unit {{ $a->unit?->unit_number }}</option>
                                    @endforeach
                                </select>
                                @error('tenancy_agreement_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Bill Type <span class="text-danger">*</span></label>
                                <select wire:model="bill_type" class="form-select @error('bill_type') is-invalid @enderror">
                                    <option value="water">Water</option>
                                    <option value="electricity">Electricity</option>
                                    <option value="internet">Internet</option>
                                    <option value="gas">Gas</option>
                                    <option value="security">Security</option>
                                    <option value="service_charge">Service Charge</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('bill_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Billing Month <span class="text-danger">*</span></label>
                                <input type="month" wire:model="billing_month" class="form-control @error('billing_month') is-invalid @enderror">
                                @error('billing_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="amount" class="form-control @error('amount') is-invalid @enderror" placeholder="0.00">
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="unpaid">Unpaid</option>
                                    <option value="partial">Partial</option>
                                    <option value="paid">Paid</option>
                                    <option value="waived">Waived</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">To record a real payment with method + reference, use "Settle" instead.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Meter reading, period covered, etc."></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="saveBill">
                                <span wire:loading.remove wire:target="saveBill">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update' : 'Issue Bill' }}
                                </span>
                                <span wire:loading wire:target="saveBill">
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

    {{-- Settle Modal --}}
    @if($showSettleModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-cash me-2"></i>Settle Bill</h5>
                    <button type="button" class="btn-close" wire:click="closeSettleModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="settleBill">
                        <div class="text-center mb-3">
                            <div class="display-6 fw-bold text-success">TZS {{ number_format((float)$amount, 0) }}</div>
                            <small class="text-muted">Will be marked as <strong>paid</strong></small>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Paid On <span class="text-danger">*</span></label>
                                <input type="date" wire:model="settle_paid_at" class="form-control @error('settle_paid_at') is-invalid @enderror">
                                @error('settle_paid_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <select wire:model="settle_payment_method_id" class="form-select @error('settle_payment_method_id') is-invalid @enderror">
                                    <option value="">— Choose method —</option>
                                    @foreach($methods as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                                @error('settle_payment_method_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Pick a method to mirror in the unified ledger.</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Reference No</label>
                                <input type="text" wire:model="settle_reference_no" class="form-control @error('settle_reference_no') is-invalid @enderror" placeholder="M-Pesa TxID, cheque #, etc.">
                                @error('settle_reference_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeSettleModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-success flex-fill" wire:loading.attr="disabled" wire:target="settleBill">
                                <span wire:loading.remove wire:target="settleBill">
                                    <i class="ti ti-check me-1"></i>Settle Bill
                                </span>
                                <span wire:loading wire:target="settleBill">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Settling…
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Bulk Issue Modal --}}
    @if($showBulkModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-stack-2 me-2"></i>Bulk Issue Bills</h5>
                    <button type="button" class="btn-close" wire:click="closeBulkModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="bulkIssue">
                        <div class="alert alert-info py-2 small">
                            <i class="ti ti-info-circle me-1"></i>
                            Issues the same bill to every selected agreement. Agreements that already have this bill type for that month are skipped.
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Bill Type <span class="text-danger">*</span></label>
                                <select wire:model="bulk_bill_type" class="form-select @error('bulk_bill_type') is-invalid @enderror">
                                    <option value="water">Water</option>
                                    <option value="electricity">Electricity</option>
                                    <option value="internet">Internet</option>
                                    <option value="gas">Gas</option>
                                    <option value="security">Security</option>
                                    <option value="service_charge">Service Charge</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('bulk_bill_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">For Month <span class="text-danger">*</span></label>
                                <input type="month" wire:model="bulk_billing_month" class="form-control @error('bulk_billing_month') is-invalid @enderror">
                                @error('bulk_billing_month') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount per Agreement (TZS) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="bulk_amount" class="form-control @error('bulk_amount') is-invalid @enderror" placeholder="0.00">
                                @error('bulk_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Active Agreements <span class="text-danger">*</span></span>
                                <span class="small text-muted">{{ count($bulk_agreement_ids) }} / {{ $activeAgreements->count() }} selected</span>
                            </label>
                            <div class="border rounded p-2" style="max-height:240px;overflow-y:auto;">
                                @if($activeAgreements->isEmpty())
                                    <div class="text-muted small">No active agreements to bill.</div>
                                @else
                                <div class="form-check mb-2 pb-2 border-bottom">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="bulk-select-all"
                                           @checked(count($bulk_agreement_ids) === $activeAgreements->count())
                                           wire:click="$set('bulk_agreement_ids', {{ count($bulk_agreement_ids) === $activeAgreements->count() ? '[]' : json_encode($activeAgreements->pluck('id')) }})">
                                    <label for="bulk-select-all" class="form-check-label fw-semibold small">Select all</label>
                                </div>
                                @foreach($activeAgreements as $a)
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="bulk_agreement_ids" value="{{ $a->id }}" class="form-check-input" id="bulk-{{ $a->id }}">
                                        <label class="form-check-label small" for="bulk-{{ $a->id }}">
                                            {{ $a->customer?->name }} · Unit {{ $a->unit?->unit_number }}
                                        </label>
                                    </div>
                                @endforeach
                                @endif
                            </div>
                            @error('bulk_agreement_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex gap-2 pt-2">
                            <button type="button" wire:click="closeBulkModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="bulkIssue">
                                <span wire:loading.remove wire:target="bulkIssue">
                                    <i class="ti ti-check me-1"></i>Issue to {{ count($bulk_agreement_ids) }} agreement(s)
                                </span>
                                <span wire:loading wire:target="bulkIssue">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Issuing…
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
    @if($showViewModal && $viewBill)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-file-invoice me-2"></i>Utility Bill</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $vc = match($viewBill->status) {
                            'paid' => 'success', 'partial' => 'warning',
                            'unpaid' => 'danger', 'waived' => 'secondary',
                            default => 'secondary',
                        };
                    @endphp
                    <div class="text-center mb-3">
                        <div class="display-6 fw-bold">TZS {{ number_format($viewBill->amount, 0) }}</div>
                        <div class="text-muted">{{ ucwords(str_replace('_',' ',$viewBill->bill_type)) }} · {{ $viewBill->billing_month?->format('M Y') }}</div>
                        <span class="badge bg-{{ $vc }}-subtle text-{{ $vc }} mt-2">{{ ucfirst($viewBill->status) }}</span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user me-2"></i>Tenant</span>
                            <span class="fw-medium">{{ $viewBill->agreement?->customer?->name ?? '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-home-2 me-2"></i>Unit</span>
                            <span class="fw-medium">{{ $viewBill->agreement?->unit?->property?->property_name }} · {{ $viewBill->agreement?->unit?->unit_number }}</span>
                        </div>
                        @if($viewBill->paid_at)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar-check me-2"></i>Paid On</span>
                            <span class="fw-medium">{{ $viewBill->paid_at->format('M d, Y') }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-link me-2"></i>Unified Ledger</span>
                            @if($viewBill->payment)
                                <span class="badge bg-success-subtle text-success">Linked · {{ $viewBill->payment->paymentMethod?->name }}</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Not linked</span>
                            @endif
                        </div>
                        @if($viewBill->notes)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-note me-2"></i>Notes</span>
                            <span>{{ $viewBill->notes }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i>Issued</span>
                            <span class="fw-medium">{{ $viewBill->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        @if($viewBill->status !== 'paid' && $viewBill->status !== 'waived')
                        <button wire:click="openSettleModal('{{ $viewBill->id }}')" class="btn btn-success flex-fill">
                            <i class="ti ti-cash me-1"></i>Settle
                        </button>
                        @endif
                        <button wire:click="openEditModal('{{ $viewBill->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
