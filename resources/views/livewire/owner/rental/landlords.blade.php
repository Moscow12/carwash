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
            <h3 class="mb-1">Landlords</h3>
            <p class="text-muted mb-0">Property owners registered under this rental business</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="quickAddSelf" class="btn btn-outline-primary" @disabled(!$selectedBusiness)>
                <i class="ti ti-user-plus me-1"></i> Add Me
            </button>
            <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness)>
                <i class="ti ti-plus me-1"></i> Add Landlord
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
                    @if($selectedBusiness)
                    <div class="alert alert-info mb-0 py-2">
                        <i class="ti ti-info-circle me-2"></i>
                        <small>Landlords under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            No rental businesses yet. Create one with type <strong>rental</strong> under
            <a href="{{ route('owner.businesses') }}" class="alert-link">My Businesses</a> first.
        </div>
    @endif

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-users fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Landlords</div>
                        <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
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
                        <div class="text-muted small">Active</div>
                        <div class="h4 mb-0">{{ number_format($stats['active']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-secondary-subtle text-secondary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-circle-x fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Inactive</div>
                        <div class="h4 mb-0">{{ number_format($stats['inactive']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-id-badge-2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">With Platform Login</div>
                        <div class="h4 mb-0">{{ number_format($stats['linked']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search name, phone or email...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="loginFilter" class="form-select">
                        <option value="">All Landlords</option>
                        <option value="linked">With Platform Login</option>
                        <option value="external">External (no login)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <span class="text-muted small">{{ $landlords->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Cards --}}
    <div class="row g-3">
        @forelse($landlords as $landlord)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary-subtle text-primary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="ti ti-user fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    {{ $landlord->name }}
                                    @if($landlord->user_id)
                                        <i class="ti ti-rosette-discount-check text-info ms-1" title="Has platform login"></i>
                                    @endif
                                </h6>
                                <small class="text-muted">{{ $landlord->properties_count }} property(ies)</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $landlord->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $landlord->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="toggleStatus('{{ $landlord->id }}')">
                                    <i class="ti ti-{{ $landlord->status === 'active' ? 'circle-x' : 'circle-check' }} me-2"></i>
                                    {{ $landlord->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteLandlord('{{ $landlord->id }}')" wire:confirm="Delete this landlord? Properties &amp; agreements must be empty.">
                                    <i class="ti ti-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-phone text-muted me-2"></i>
                            <span class="small">{{ $landlord->phone }}</span>
                        </div>
                        @if($landlord->email)
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-mail text-muted me-2"></i>
                            <span class="small text-truncate">{{ $landlord->email }}</span>
                        </div>
                        @endif
                        @if($landlord->region || $landlord->district)
                        <div class="d-flex align-items-start">
                            <i class="ti ti-map-pin text-muted me-2 mt-1"></i>
                            <span class="text-muted small">
                                {{ collect([$landlord->district?->name, $landlord->region?->name])->filter()->implode(', ') }}
                            </span>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="badge bg-{{ $landlord->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $landlord->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($landlord->status) }}
                        </span>
                        <small class="text-muted">{{ $landlord->tenancy_agreements_count }} tenancy(ies)</small>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                        <i class="ti ti-users fs-1"></i>
                    </div>
                    <h6 class="text-muted">No landlords yet</h6>
                    <p class="text-muted small mb-3">
                        @if($selectedBusiness)
                            Add the property owners so you can register their properties next.
                        @else
                            Select a rental business to see its landlords.
                        @endif
                    </p>
                    @if($selectedBusiness)
                    <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i> Add Landlord
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($landlords->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $landlords->links() }}
    </div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Landlord' : 'Add Landlord' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveLandlord">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Landlord name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-phone"></i></span>
                                    <input type="text" wire:model="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="+255 7xx xxx xxx">
                                </div>
                                @error('phone') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="optional@example.com">
                                </div>
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">
                                    Platform Login
                                    <small class="text-muted">(optional — link an existing user)</small>
                                </label>
                                <select wire:model="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                    <option value="">— External landlord (no login) —</option>
                                    @foreach($linkableUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <input type="text" wire:model="address" class="form-control @error('address') is-invalid @enderror" placeholder="Street address or landmark">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select wire:model="country_id" class="form-select">
                                    <option value="">Select country</option>
                                    @foreach($allCountries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Region</label>
                                <select wire:model.live="region_id" class="form-select">
                                    <option value="">Select region</option>
                                    @foreach($allRegions as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <select wire:model.live="district_id" class="form-select" @disabled(!$region_id)>
                                    <option value="">Select district</option>
                                    @foreach($allDistricts as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ward</label>
                                <select wire:model.live="ward_id" class="form-select" @disabled(!$district_id)>
                                    <option value="">Select ward</option>
                                    @foreach($allWards as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Street</label>
                                <select wire:model="street_id" class="form-select" @disabled(!$ward_id)>
                                    <option value="">Select street</option>
                                    @foreach($allStreets as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="saveLandlord">
                                <span wire:loading.remove wire:target="saveLandlord">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update Landlord' : 'Save Landlord' }}
                                </span>
                                <span wire:loading wire:target="saveLandlord">
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
    @if($showViewModal && $viewLandlord)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-user me-2"></i>Landlord Details</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar bg-primary-subtle text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                            <i class="ti ti-user fs-1"></i>
                        </div>
                        <h5 class="mb-1">
                            {{ $viewLandlord->name }}
                            @if($viewLandlord->user_id)
                                <i class="ti ti-rosette-discount-check text-info ms-1" title="Has platform login"></i>
                            @endif
                        </h5>
                        <span class="badge bg-{{ $viewLandlord->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $viewLandlord->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($viewLandlord->status) }}
                        </span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-phone me-2"></i>Phone</span>
                            <span class="fw-medium">{{ $viewLandlord->phone }}</span>
                        </div>
                        @if($viewLandlord->email)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-mail me-2"></i>Email</span>
                            <span class="fw-medium">{{ $viewLandlord->email }}</span>
                        </div>
                        @endif
                        @if($viewLandlord->user)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-id-badge-2 me-2"></i>Linked User</span>
                            <span class="fw-medium">{{ $viewLandlord->user->name }}</span>
                        </div>
                        @endif
                        @if($viewLandlord->address || $viewLandlord->region || $viewLandlord->district)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-map-pin me-2"></i>Location</span>
                            <span>
                                {{ collect([
                                    $viewLandlord->address,
                                    $viewLandlord->ward?->name,
                                    $viewLandlord->district?->name,
                                    $viewLandlord->region?->name,
                                ])->filter()->implode(', ') }}
                            </span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-building me-2"></i>Properties</span>
                            <span class="fw-medium">{{ $viewLandlord->properties_count }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-file-text me-2"></i>Tenancy Agreements</span>
                            <span class="fw-medium">{{ $viewLandlord->tenancy_agreements_count }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i>Registered</span>
                            <span class="fw-medium">{{ $viewLandlord->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        <button wire:click="openEditModal('{{ $viewLandlord->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
