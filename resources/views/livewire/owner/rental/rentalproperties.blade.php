<div>
    {{-- Flash Messages --}}
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
            <h3 class="mb-1">Rental Properties</h3>
            <p class="text-muted mb-0">Buildings &amp; estates available for letting</p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness)>
            <i class="ti ti-plus me-1"></i> Add Property
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
                        <small>Properties under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            You don't have any rental businesses yet. Create one under
            <a href="{{ route('owner.businesses') }}" class="alert-link">My Businesses</a> with type <strong>rental</strong> to start managing properties.
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="ti ti-building fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Properties</div>
                            <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="ti ti-circle-check fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Active</div>
                            <div class="h4 mb-0">{{ number_format($stats['active']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-secondary-subtle text-secondary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="ti ti-circle-x fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Inactive</div>
                            <div class="h4 mb-0">{{ number_format($stats['inactive']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                            <i class="ti ti-home-2 fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Units</div>
                            <div class="h4 mb-0">{{ number_format($stats['units']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search name, address, description…">
                    </div>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="apartment">Apartment</option>
                        <option value="standalone">Standalone</option>
                        <option value="hostel">Hostel</option>
                        <option value="commercial">Commercial</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="landlordFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Landlords</option>
                        @foreach($landlords as $ll)
                            <option value="{{ $ll->id }}">{{ $ll->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-1 text-end">
                    <span class="text-muted small">{{ $properties->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Properties Grid --}}
    <div class="row g-3">
        @forelse($properties as $property)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                <i class="ti ti-building fs-4"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $property->property_name }}</h6>
                                <small class="text-muted">{{ ucfirst($property->property_type) }} · {{ $property->units_count }} unit(s)</small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $property->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $property->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="toggleStatus('{{ $property->id }}')">
                                    <i class="ti ti-{{ $property->status === 'active' ? 'circle-x' : 'circle-check' }} me-2"></i>
                                    {{ $property->status === 'active' ? 'Deactivate' : 'Activate' }}
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteProperty('{{ $property->id }}')" wire:confirm="Delete this property? It must have no units.">
                                    <i class="ti ti-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ti ti-user text-muted me-2"></i>
                            <span class="small">{{ $property->landlord?->name ?? '—' }}</span>
                        </div>
                        @if($property->region || $property->district)
                        <div class="d-flex align-items-start mb-2">
                            <i class="ti ti-map-pin text-muted me-2 mt-1"></i>
                            <span class="text-muted small">
                                {{ collect([$property->district?->name, $property->region?->name])->filter()->implode(', ') }}
                            </span>
                        </div>
                        @endif
                        @if($property->postal_address)
                        <div class="d-flex align-items-start">
                            <i class="ti ti-mailbox text-muted me-2 mt-1"></i>
                            <span class="text-muted small">{{ $property->postal_address }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <span class="badge bg-{{ $property->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $property->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($property->status) }}
                        </span>
                        <small class="text-muted">Added {{ $property->created_at->diffForHumans() }}</small>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                        <i class="ti ti-building fs-1"></i>
                    </div>
                    <h6 class="text-muted">No properties yet</h6>
                    <p class="text-muted small mb-3">
                        @if($selectedBusiness)
                            Add your first property to start tracking units and tenants.
                        @else
                            Select a rental business to see its properties.
                        @endif
                    </p>
                    @if($selectedBusiness)
                    <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i> Add Property
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($properties->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $properties->links() }}
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
                        {{ $editMode ? 'Edit Property' : 'Add Property' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveProperty">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Landlord <span class="text-danger">*</span></label>
                                <select wire:model="landlord_id" class="form-select @error('landlord_id') is-invalid @enderror">
                                    <option value="">Choose landlord...</option>
                                    @foreach($landlords as $ll)
                                        <option value="{{ $ll->id }}">{{ $ll->name }}</option>
                                    @endforeach
                                </select>
                                @error('landlord_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($landlords->isEmpty())
                                    <small class="text-warning d-block mt-1"><i class="ti ti-alert-triangle me-1"></i>No landlords yet — add one under Landlords first.</small>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Property Type <span class="text-danger">*</span></label>
                                <select wire:model="property_type" class="form-select @error('property_type') is-invalid @enderror">
                                    <option value="apartment">Apartment</option>
                                    <option value="standalone">Standalone House</option>
                                    <option value="hostel">Hostel</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                                @error('property_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Property Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="property_name" class="form-control @error('property_name') is-invalid @enderror" placeholder="e.g. Mlimani View Apartments">
                                @error('property_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Country</label>
                                <select wire:model="country_id" class="form-select @error('country_id') is-invalid @enderror">
                                    <option value="">Select country</option>
                                    @foreach($allCountries as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Region</label>
                                <select wire:model.live="region_id" class="form-select @error('region_id') is-invalid @enderror">
                                    <option value="">Select region</option>
                                    @foreach($allRegions as $r)
                                        <option value="{{ $r->id }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">District</label>
                                <select wire:model.live="district_id" class="form-select @error('district_id') is-invalid @enderror" @disabled(!$region_id)>
                                    <option value="">Select district</option>
                                    @foreach($allDistricts as $d)
                                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                                    @endforeach
                                </select>
                                @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Ward</label>
                                <select wire:model.live="ward_id" class="form-select @error('ward_id') is-invalid @enderror" @disabled(!$district_id)>
                                    <option value="">Select ward</option>
                                    @foreach($allWards as $w)
                                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                                    @endforeach
                                </select>
                                @error('ward_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Street</label>
                                <select wire:model="street_id" class="form-select @error('street_id') is-invalid @enderror" @disabled(!$ward_id)>
                                    <option value="">Select street</option>
                                    @foreach($allStreets as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                @error('street_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Postal Address</label>
                                <input type="text" wire:model="postal_address" class="form-control @error('postal_address') is-invalid @enderror" placeholder="e.g. P.O. Box 1234, Dar es Salaam">
                                @error('postal_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional notes about the property"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="saveProperty">
                                <span wire:loading.remove wire:target="saveProperty">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update Property' : 'Save Property' }}
                                </span>
                                <span wire:loading wire:target="saveProperty">
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
    @if($showViewModal && $viewProperty)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-building me-2"></i>Property Details</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="avatar bg-primary-subtle text-primary rounded-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:72px;height:72px;">
                            <i class="ti ti-building fs-1"></i>
                        </div>
                        <h5 class="mb-1">{{ $viewProperty->property_name }}</h5>
                        <span class="badge bg-{{ $viewProperty->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $viewProperty->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($viewProperty->status) }}
                        </span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-tag me-2"></i>Type</span>
                            <span class="fw-medium">{{ ucfirst($viewProperty->property_type) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user me-2"></i>Landlord</span>
                            <span class="fw-medium">{{ $viewProperty->landlord?->name ?? '—' }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-home-2 me-2"></i>Units</span>
                            <span class="fw-medium">{{ $viewProperty->units_count }}</span>
                        </div>
                        @if($viewProperty->region || $viewProperty->district || $viewProperty->ward)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-map-pin me-2"></i>Location</span>
                            <span>
                                {{ collect([
                                    $viewProperty->ward?->name,
                                    $viewProperty->district?->name,
                                    $viewProperty->region?->name,
                                ])->filter()->implode(', ') }}
                            </span>
                        </div>
                        @endif
                        @if($viewProperty->postal_address)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-mailbox me-2"></i>Postal</span>
                            <span class="fw-medium">{{ $viewProperty->postal_address }}</span>
                        </div>
                        @endif
                        @if($viewProperty->description)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-file-description me-2"></i>Description</span>
                            <span>{{ $viewProperty->description }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-calendar me-2"></i>Added</span>
                            <span class="fw-medium">{{ $viewProperty->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        <button wire:click="openEditModal('{{ $viewProperty->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
