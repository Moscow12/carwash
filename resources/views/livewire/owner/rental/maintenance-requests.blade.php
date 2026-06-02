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
            <h3 class="mb-1">Maintenance Requests</h3>
            <p class="text-muted mb-0">Repairs and upkeep tickets raised against tenancy units</p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness || $agreements->isEmpty())>
            <i class="ti ti-plus me-1"></i> Log Request
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
                            <small>Tickets under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
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
                    <div class="avatar bg-danger-subtle text-danger rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-alert-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Open</div>
                        <div class="h4 mb-0">{{ number_format($stats['open']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-progress fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">In Progress</div>
                        <div class="h4 mb-0">{{ number_format($stats['in_progress']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-circle-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Resolved this Month</div>
                        <div class="h4 mb-0">{{ number_format($stats['resolved_this_month']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-cash fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Cost this Month</div>
                        <div class="h5 mb-0">TZS {{ number_format($stats['cost_this_month'], 0) }}</div>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search tenant or description…">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="open">Open</option>
                        <option value="in_progress">In Progress</option>
                        <option value="resolved">Resolved</option>
                        <option value="closed">Closed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="plumbing">Plumbing</option>
                        <option value="electrical">Electrical</option>
                        <option value="painting">Painting</option>
                        <option value="roofing">Roofing</option>
                        <option value="furniture">Furniture</option>
                        <option value="appliance">Appliance</option>
                        <option value="pest_control">Pest Control</option>
                        <option value="cleaning">Cleaning</option>
                        <option value="security">Security</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="agreementFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Agreements</option>
                        @foreach($agreements as $a)
                            <option value="{{ $a->id }}">{{ $a->customer?->name }} · U{{ $a->unit?->unit_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="assigneeFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Assignees</option>
                        @foreach($staff as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <span class="text-muted small">{{ $requests->total() }}</span>
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
                        <th class="ps-3">Logged</th>
                        <th>Tenant / Unit</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Assigned</th>
                        <th>Status</th>
                        <th class="text-end">Cost</th>
                        <th class="text-end pe-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $r)
                    @php
                        $statusColor = match($r->status) {
                            'open' => 'danger',
                            'in_progress' => 'warning',
                            'resolved' => 'success',
                            'closed' => 'secondary',
                            'cancelled' => 'dark',
                            default => 'secondary',
                        };
                        $typeIcon = match($r->maintenance_type) {
                            'plumbing' => 'ti-droplet text-info',
                            'electrical' => 'ti-bolt text-warning',
                            'painting' => 'ti-paint text-primary',
                            'roofing' => 'ti-home-2 text-secondary',
                            'furniture' => 'ti-sofa text-secondary',
                            'appliance' => 'ti-wash-machine text-primary',
                            'pest_control' => 'ti-bug text-danger',
                            'cleaning' => 'ti-spray text-info',
                            'security' => 'ti-shield text-secondary',
                            default => 'ti-tool text-muted',
                        };
                    @endphp
                    <tr>
                        <td class="ps-3"><small>{{ $r->created_at->format('M d, Y') }}</small></td>
                        <td>
                            <div class="fw-medium">{{ $r->agreement?->customer?->name ?? '—' }}</div>
                            <small class="text-muted">Unit {{ $r->agreement?->unit?->unit_number ?? '—' }}</small>
                        </td>
                        <td><i class="ti {{ $typeIcon }} me-1"></i><small>{{ ucwords(str_replace('_',' ',$r->maintenance_type)) }}</small></td>
                        <td><small class="text-muted">{{ \Illuminate\Support\Str::limit($r->description ?? '—', 60) }}</small></td>
                        <td><small>{{ $r->assignee?->name ?? '—' }}</small></td>
                        <td><span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucwords(str_replace('_',' ',$r->status)) }}</span></td>
                        <td class="text-end">
                            @if($r->cost !== null)
                                <small class="fw-medium">TZS {{ number_format($r->cost, 0) }}</small>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                    <i class="ti ti-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $r->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $r->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                    @if(!in_array($r->status, ['resolved','closed','cancelled']))
                                        <li><hr class="dropdown-divider"></li>
                                        <li><small class="dropdown-header text-muted">Workflow</small></li>
                                        @if($r->status === 'open')
                                        <li><a class="dropdown-item text-warning" href="#" wire:click.prevent="setStatus('{{ $r->id }}','in_progress')"><i class="ti ti-progress me-2"></i>Start Work</a></li>
                                        @endif
                                        <li><a class="dropdown-item text-success" href="#" wire:click.prevent="openResolveModal('{{ $r->id }}')"><i class="ti ti-circle-check me-2"></i>Resolve…</a></li>
                                        <li><a class="dropdown-item text-dark" href="#" wire:click.prevent="setStatus('{{ $r->id }}','cancelled')" wire:confirm="Cancel this request?"><i class="ti ti-ban me-2"></i>Cancel</a></li>
                                    @elseif($r->status === 'resolved')
                                        <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $r->id }}','closed')"><i class="ti ti-archive me-2"></i>Close</a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteRequest('{{ $r->id }}')" wire:confirm="Delete this request?">
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
                                <i class="ti ti-tool fs-2"></i>
                            </div>
                            <div class="small">No maintenance requests for these filters.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($requests->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $requests->links() }}</div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Request' : 'Log Maintenance Request' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveRequest">
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
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="maintenance_type" class="form-select @error('maintenance_type') is-invalid @enderror">
                                    <option value="plumbing">Plumbing</option>
                                    <option value="electrical">Electrical</option>
                                    <option value="painting">Painting</option>
                                    <option value="roofing">Roofing</option>
                                    <option value="furniture">Furniture</option>
                                    <option value="appliance">Appliance</option>
                                    <option value="pest_control">Pest Control</option>
                                    <option value="cleaning">Cleaning</option>
                                    <option value="security">Security</option>
                                    <option value="other">Other</option>
                                </select>
                                @error('maintenance_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Assignee</label>
                                <select wire:model="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror">
                                    <option value="">— External / unassigned —</option>
                                    @foreach($staff as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}{{ $s->position ? " ({$s->position})" : '' }}</option>
                                    @endforeach
                                </select>
                                @error('assigned_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="What needs fixing? Include any relevant details."></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Start Date</label>
                                <input type="date" wire:model="start_date" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">End Date</label>
                                <input type="date" wire:model="end_date" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Cost (TZS)</label>
                                <input type="number" step="0.01" min="0" wire:model="cost" class="form-control @error('cost') is-invalid @enderror" placeholder="0.00">
                                @error('cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Optional until resolved.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    @if($editMode)
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                        <option value="cancelled">Cancelled</option>
                                    @endif
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="saveRequest">
                                <span wire:loading.remove wire:target="saveRequest">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update' : 'Log Request' }}
                                </span>
                                <span wire:loading wire:target="saveRequest">
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

    {{-- Resolve Modal --}}
    @if($showResolveModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-circle-check me-2"></i>Resolve Request</h5>
                    <button type="button" class="btn-close" wire:click="closeResolveModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="resolveRequest">
                        <div class="alert alert-info py-2 small">
                            <i class="ti ti-info-circle me-1"></i>
                            Capture when the work was completed and what it cost (optional).
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Completed On <span class="text-danger">*</span></label>
                                <input type="date" wire:model="resolve_end_date" class="form-control @error('resolve_end_date') is-invalid @enderror">
                                @error('resolve_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Cost (TZS)</label>
                                <input type="number" step="0.01" min="0" wire:model="resolve_cost" class="form-control @error('resolve_cost') is-invalid @enderror" placeholder="0.00">
                                @error('resolve_cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeResolveModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-success flex-fill" wire:loading.attr="disabled" wire:target="resolveRequest">
                                <span wire:loading.remove wire:target="resolveRequest">
                                    <i class="ti ti-check me-1"></i>Mark Resolved
                                </span>
                                <span wire:loading wire:target="resolveRequest">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Resolving…
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
    @if($showViewModal && $viewRequest)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-tool me-2"></i>Maintenance Request</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    @php
                        $vc = match($viewRequest->status) {
                            'open' => 'danger', 'in_progress' => 'warning',
                            'resolved' => 'success', 'closed' => 'secondary',
                            'cancelled' => 'dark', default => 'secondary',
                        };
                    @endphp
                    <div class="text-center mb-3">
                        <h6 class="mb-1">{{ ucwords(str_replace('_',' ',$viewRequest->maintenance_type)) }}</h6>
                        <span class="badge bg-{{ $vc }}-subtle text-{{ $vc }}">{{ ucwords(str_replace('_',' ',$viewRequest->status)) }}</span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user me-2"></i>Tenant</span>
                            <span class="fw-medium">{{ $viewRequest->agreement?->customer?->name ?? '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-home-2 me-2"></i>Unit</span>
                            <span class="fw-medium">{{ $viewRequest->agreement?->unit?->property?->property_name }} · {{ $viewRequest->agreement?->unit?->unit_number }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user-cog me-2"></i>Assignee</span>
                            <span class="fw-medium">{{ $viewRequest->assignee?->name ?? 'Unassigned' }}</span>
                        </div>
                        @if($viewRequest->start_date)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i>Started</span>
                            <span class="fw-medium">{{ $viewRequest->start_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($viewRequest->end_date)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar-check me-2"></i>Completed</span>
                            <span class="fw-medium">{{ $viewRequest->end_date->format('M d, Y') }}</span>
                        </div>
                        @endif
                        @if($viewRequest->cost !== null)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-cash me-2"></i>Cost</span>
                            <span class="fw-medium">TZS {{ number_format($viewRequest->cost, 0) }}</span>
                        </div>
                        @endif
                        @if($viewRequest->description)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-file-description me-2"></i>Description</span>
                            <span>{{ $viewRequest->description }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-clock me-2"></i>Logged</span>
                            <span class="fw-medium">{{ $viewRequest->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        @if(!in_array($viewRequest->status, ['resolved','closed','cancelled']))
                            <button wire:click="openResolveModal('{{ $viewRequest->id }}')" class="btn btn-success flex-fill">
                                <i class="ti ti-check me-1"></i>Resolve
                            </button>
                        @endif
                        <button wire:click="openEditModal('{{ $viewRequest->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
