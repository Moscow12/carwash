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
            <h3 class="mb-1">Tenancy Agreements</h3>
            <p class="text-muted mb-0">Contracts between tenants and landlords for specific units</p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness)>
            <i class="ti ti-plus me-1"></i> New Agreement
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
                    @if($selectedBusiness)
                    <div class="alert alert-info mb-0 py-2">
                        <i class="ti ti-info-circle me-2"></i>
                        <small>Agreements under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-file-text fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total</div>
                        <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-circle-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active</div>
                        <div class="h4 mb-0">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-secondary-subtle text-secondary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-pencil fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Drafts</div>
                        <div class="h4 mb-0">{{ number_format($stats['draft']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-clock-x fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Expired</div>
                        <div class="h4 mb-0">{{ number_format($stats['expired']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-12">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Active Monthly Revenue</div>
                        <div class="h4 mb-0">TZS {{ number_format($stats['monthly_revenue'], 0) }}</div>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search tenant or unit number…">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="landlordFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Landlords</option>
                        @foreach($landlords as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="propertyFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Properties</option>
                        @foreach($filterProperties as $p)
                            <option value="{{ $p->id }}">{{ $p->property_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="terminated">Terminated</option>
                        <option value="expired">Expired</option>
                        <option value="renewed">Renewed</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <span class="text-muted small">{{ $agreements->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div class="row g-3">
        @forelse($agreements as $a)
        @php
            $statusColor = match($a->agreement_status) {
                'active' => 'success',
                'draft' => 'secondary',
                'terminated' => 'danger',
                'expired' => 'warning',
                'renewed' => 'info',
                default => 'secondary',
            };
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-{{ $statusColor }}-subtle text-{{ $statusColor }} rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="ti ti-file-text fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $a->customer?->name ?? 'Unknown tenant' }}</h6>
                                <small class="text-muted">{{ $a->customer?->phone }}</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $a->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $a->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                @if($a->agreement_status === 'draft')
                                <li><a class="dropdown-item text-success" href="#" wire:click.prevent="activate('{{ $a->id }}')" wire:confirm="Activate this agreement and mark the unit as occupied?">
                                    <i class="ti ti-circle-check me-2"></i>Activate
                                </a></li>
                                @endif
                                @if($a->agreement_status === 'active')
                                <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="terminate('{{ $a->id }}')" wire:confirm="Terminate the agreement and release the unit?">
                                    <i class="ti ti-door-exit me-2"></i>Terminate
                                </a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteAgreement('{{ $a->id }}')" wire:confirm="Delete this agreement? Rent payments must not exist.">
                                    <i class="ti ti-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3 small">
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-home-2 text-muted me-2"></i>
                            <span>{{ $a->unit?->property?->property_name }} · Unit {{ $a->unit?->unit_number ?? '—' }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <i class="ti ti-user text-muted me-2"></i>
                            <span class="text-muted">Landlord: {{ $a->landlord?->name }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-calendar text-muted me-2"></i>
                            <span class="text-muted">
                                {{ $a->start_date?->format('M d, Y') }}
                                @if($a->end_date) → {{ $a->end_date->format('M d, Y') }} @endif
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div>
                            <div class="fw-bold">TZS {{ number_format($a->rent_amount, 0) }}<small class="text-muted fw-normal">/{{ str_replace('_', ' ', $a->payment_frequency) }}</small></div>
                            <small class="text-muted">Deposit: TZS {{ number_format($a->deposit_paid, 0) }}</small>
                        </div>
                        <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($a->agreement_status) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                        <i class="ti ti-file-text fs-1"></i>
                    </div>
                    <h6 class="text-muted">No agreements yet</h6>
                    <p class="text-muted small mb-3">
                        @if($selectedBusiness)
                            Register a tenancy by linking a tenant to a vacant unit.
                        @else
                            Select a rental business to see its agreements.
                        @endif
                    </p>
                    @if($selectedBusiness)
                    <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i>New Agreement
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($agreements->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $agreements->links() }}</div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Agreement' : 'New Tenancy Agreement' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveAgreement">
                        <h6 class="text-muted small mb-2"><i class="ti ti-building me-1"></i> WHERE</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Landlord <span class="text-danger">*</span></label>
                                <select wire:model.live="landlord_id" class="form-select @error('landlord_id') is-invalid @enderror">
                                    <option value="">Choose...</option>
                                    @foreach($landlords as $l)
                                        <option value="{{ $l->id }}">{{ $l->name }}</option>
                                    @endforeach
                                </select>
                                @error('landlord_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Property <span class="text-danger">*</span></label>
                                <select wire:model.live="property_id" class="form-select @error('property_id') is-invalid @enderror" @disabled(!$landlord_id)>
                                    <option value="">Choose property...</option>
                                    @foreach($propertyOptions as $p)
                                        <option value="{{ $p->id }}">{{ $p->property_name }}</option>
                                    @endforeach
                                </select>
                                @error('property_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <select wire:model.live="rental_unit_id" class="form-select @error('rental_unit_id') is-invalid @enderror" @disabled(!$property_id)>
                                    <option value="">Choose unit...</option>
                                    @foreach($unitOptions as $u)
                                        <option value="{{ $u->id }}">{{ $u->unit_number }} ({{ ucfirst($u->status) }} · TZS {{ number_format($u->monthly_rent, 0) }})</option>
                                    @endforeach
                                </select>
                                @error('rental_unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($property_id && $unitOptions->isEmpty())
                                    <small class="text-warning d-block mt-1"><i class="ti ti-alert-triangle me-1"></i>No vacant/reserved units on this property.</small>
                                @endif
                            </div>
                        </div>

                        <h6 class="text-muted small mb-2"><i class="ti ti-user me-1"></i> WHO</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                @if(!$quickTenant)
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>Tenant <span class="text-danger">*</span></span>
                                    <a href="#" class="small text-decoration-none" wire:click.prevent="$set('quickTenant', true)">
                                        <i class="ti ti-plus me-1"></i>Quick-add new tenant
                                    </a>
                                </label>
                                <select wire:model="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                    <option value="">Choose tenant...</option>
                                    @foreach($tenants as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }} · {{ $t->phone }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @else
                                <div class="card bg-light border-0">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 small text-primary"><i class="ti ti-user-plus me-1"></i>New Tenant</h6>
                                            <a href="#" class="small text-muted" wire:click.prevent="$set('quickTenant', false)">
                                                <i class="ti ti-x me-1"></i>Cancel
                                            </a>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-5">
                                                <input type="text" wire:model="qt_name" class="form-control form-control-sm @error('qt_name') is-invalid @enderror" placeholder="Full name *">
                                                @error('qt_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <input type="text" wire:model="qt_phone" class="form-control form-control-sm @error('qt_phone') is-invalid @enderror" placeholder="Phone *">
                                                @error('qt_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                            <div class="col-md-3">
                                                <input type="email" wire:model="qt_email" class="form-control form-control-sm @error('qt_email') is-invalid @enderror" placeholder="Email">
                                                @error('qt_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <h6 class="text-muted small mb-2"><i class="ti ti-cash me-1"></i> TERMS</h6>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" wire:model="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date</label>
                                <input type="date" wire:model="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Rent <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="rent_amount" class="form-control @error('rent_amount') is-invalid @enderror" placeholder="0.00">
                                @error('rent_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Deposit Paid</label>
                                <input type="number" step="0.01" min="0" wire:model="deposit_paid" class="form-control @error('deposit_paid') is-invalid @enderror" placeholder="0.00">
                                @error('deposit_paid') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Payment Frequency <span class="text-danger">*</span></label>
                                <select wire:model="payment_frequency" class="form-select @error('payment_frequency') is-invalid @enderror">
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="semi_annual">Semi-annual</option>
                                    <option value="annual">Annual</option>
                                </select>
                                @error('payment_frequency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="agreement_status" class="form-select @error('agreement_status') is-invalid @enderror">
                                    <option value="draft">Draft</option>
                                    <option value="active">Active (occupies the unit)</option>
                                    @if($editMode)
                                        <option value="terminated">Terminated</option>
                                        <option value="expired">Expired</option>
                                        <option value="renewed">Renewed</option>
                                    @endif
                                </select>
                                @error('agreement_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Saving as <strong>Active</strong> immediately marks the unit occupied.</small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Special clauses, references, etc."></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="saveAgreement">
                                <span wire:loading.remove wire:target="saveAgreement">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update Agreement' : 'Save Agreement' }}
                                </span>
                                <span wire:loading wire:target="saveAgreement">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
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
    @if($showViewModal && $viewAgreement)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-file-text me-2"></i>Agreement Details</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $vColor = match($viewAgreement->agreement_status) {
                            'active' => 'success', 'draft' => 'secondary',
                            'terminated' => 'danger', 'expired' => 'warning',
                            'renewed' => 'info', default => 'secondary',
                        };
                    @endphp
                    <div class="text-center mb-3">
                        <h5 class="mb-1">{{ $viewAgreement->customer?->name }}</h5>
                        <div class="text-muted small">{{ $viewAgreement->customer?->phone }}{{ $viewAgreement->customer?->email ? ' · ' . $viewAgreement->customer->email : '' }}</div>
                        <span class="badge bg-{{ $vColor }}-subtle text-{{ $vColor }} mt-2">{{ ucfirst($viewAgreement->agreement_status) }}</span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-home-2 me-2"></i>Unit</span>
                            <span class="fw-medium">{{ $viewAgreement->unit?->property?->property_name }} · {{ $viewAgreement->unit?->unit_number }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user me-2"></i>Landlord</span>
                            <span class="fw-medium">{{ $viewAgreement->landlord?->name }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i>Start</span>
                            <span class="fw-medium">{{ $viewAgreement->start_date?->format('M d, Y') }}</span>
                        </div>
                        @if($viewAgreement->end_date)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar-x me-2"></i>End</span>
                            <span class="fw-medium">{{ $viewAgreement->end_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-cash me-2"></i>Rent</span>
                            <span class="fw-medium">TZS {{ number_format($viewAgreement->rent_amount, 0) }} / {{ str_replace('_',' ',$viewAgreement->payment_frequency) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-coin me-2"></i>Deposit Paid</span>
                            <span class="fw-medium">TZS {{ number_format($viewAgreement->deposit_paid, 0) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-receipt me-2"></i>Payments Recorded</span>
                            <span class="fw-medium">{{ $viewAgreement->rent_payments_count }} · TZS {{ number_format($viewAgreement->total_paid ?? 0, 0) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-bolt me-2"></i>Utility Bills</span>
                            <span class="fw-medium">{{ $viewAgreement->utility_bills_count }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-tool me-2"></i>Maintenance Requests</span>
                            <span class="fw-medium">{{ $viewAgreement->maintenance_requests_count }}</span>
                        </div>
                        @if($viewAgreement->notes)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-note me-2"></i>Notes</span>
                            <span>{{ $viewAgreement->notes }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-pencil me-2"></i>Created by</span>
                            <span class="fw-medium">{{ $viewAgreement->creator?->name ?? '—' }} · {{ $viewAgreement->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        <button wire:click="openEditModal('{{ $viewAgreement->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
