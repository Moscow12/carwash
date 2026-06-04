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
            <h3 class="mb-1">Rental Units</h3>
            <p class="text-muted mb-0">Individual lettable units within your properties</p>
        </div>
        <button wire:click="openAddModal" class="btn btn-primary" @disabled(!$selectedBusiness || $properties->isEmpty())>
            <i class="ti ti-plus me-1"></i> Add Unit
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
                    @if($selectedBusiness && $properties->isEmpty())
                        <div class="alert alert-warning mb-0 py-2">
                            <i class="ti ti-alert-triangle me-2"></i>
                            <small>No active properties yet — add one under
                                <a href="{{ route('owner.rental.properties') }}" class="alert-link">Properties</a> first.
                            </small>
                        </div>
                    @elseif($selectedBusiness)
                        <div class="alert alert-info mb-0 py-2">
                            <i class="ti ti-info-circle me-2"></i>
                            <small>Units under <strong>{{ $businesses->firstWhere('id', $selectedBusiness)?->name }}</strong></small>
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
                        <i class="ti ti-home-2 fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Units</div>
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
                        <div class="text-muted small">Vacant</div>
                        <div class="h4 mb-0">{{ number_format($stats['vacant']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-user-check fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Occupied</div>
                        <div class="h4 mb-0">{{ number_format($stats['occupied']) }}</div>
                    </div>
                </div>
            </div></div>
        </div>
        <div class="col-md col-6">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                        <i class="ti ti-tool fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Maintenance</div>
                        <div class="h4 mb-0">{{ number_format($stats['maintenance']) }}</div>
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
                        <div class="text-muted small">Monthly Potential</div>
                        <div class="h4 mb-0">TZS {{ number_format($stats['monthly_potential'], 0) }}</div>
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search unit # or description…">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="propertyFilter" class="form-select" @disabled(!$selectedBusiness)>
                        <option value="">All Properties</option>
                        @foreach($properties as $p)
                            <option value="{{ $p->id }}">{{ $p->property_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">All Types</option>
                        <option value="single">Single Room</option>
                        <option value="double">Double Room</option>
                        <option value="full_house">Full House</option>
                        <option value="apartment">Apartment</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Status</option>
                        <option value="vacant">Vacant</option>
                        <option value="occupied">Occupied</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="reserved">Reserved</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <span class="text-muted small">{{ $units->total() }} unit(s)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div class="row g-3">
        @forelse($units as $unit)
        @php
            $cover = $unit->images->first();
            $statusColor = match($unit->status) {
                'vacant' => 'success',
                'occupied' => 'info',
                'maintenance' => 'warning',
                'reserved' => 'primary',
                default => 'secondary',
            };
        @endphp
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                @if($cover)
                    <img src="{{ asset('storage/' . $cover->image_url) }}" class="card-img-top" style="height:160px;object-fit:cover;" alt="Unit {{ $unit->unit_number }}">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:160px;">
                        <i class="ti ti-photo text-muted" style="font-size:3rem;"></i>
                    </div>
                @endif
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-0">Unit {{ $unit->unit_number }}</h6>
                            <small class="text-muted">{{ $unit->property?->property_name }} · {{ ucwords(str_replace('_',' ',$unit->unit_type)) }}</small>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openViewModal('{{ $unit->id }}')"><i class="ti ti-eye me-2"></i>View</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $unit->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><small class="dropdown-header text-muted">Set status</small></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $unit->id }}','vacant')"><i class="ti ti-circle-check me-2"></i>Vacant</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $unit->id }}','reserved')"><i class="ti ti-bookmark me-2"></i>Reserved</a></li>
                                <li><a class="dropdown-item" href="#" wire:click.prevent="setStatus('{{ $unit->id }}','maintenance')"><i class="ti ti-tool me-2"></i>Maintenance</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="deleteUnit('{{ $unit->id }}')" wire:confirm="Delete this unit? Active agreements must be terminated first.">
                                    <i class="ti ti-trash me-2"></i>Delete
                                </a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex gap-3 small text-muted mb-3">
                        <span><i class="ti ti-bed me-1"></i>{{ $unit->bedrooms }}</span>
                        <span><i class="ti ti-bath me-1"></i>{{ $unit->bathrooms }}</span>
                        @if($unit->floor_no !== null)
                            <span><i class="ti ti-stairs me-1"></i>{{ $unit->floor_no }}F</span>
                        @endif
                        @if($unit->has_electricity)<span title="Electricity"><i class="ti ti-bolt text-warning"></i></span>@endif
                        @if($unit->has_water)<span title="Water"><i class="ti ti-droplet text-info"></i></span>@endif
                        @if($unit->has_furniture)<span title="Furnished"><i class="ti ti-sofa text-secondary"></i></span>@endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                        <div>
                            <div class="fw-bold">TZS {{ number_format($unit->monthly_rent, 0) }}<small class="text-muted fw-normal">/mo</small></div>
                            <small class="text-muted">Deposit: TZS {{ number_format($unit->deposit_amount, 0) }}</small>
                        </div>
                        <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">{{ ucfirst($unit->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:64px;height:64px;">
                        <i class="ti ti-home-2 fs-1"></i>
                    </div>
                    <h6 class="text-muted">No units yet</h6>
                    <p class="text-muted small mb-3">
                        @if($selectedBusiness && $properties->isEmpty())
                            Add a property first, then come back to register its units.
                        @elseif($selectedBusiness)
                            Register the lettable units inside your properties.
                        @else
                            Select a rental business to see its units.
                        @endif
                    </p>
                    @if($selectedBusiness && $properties->isNotEmpty())
                    <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                        <i class="ti ti-plus me-1"></i>Add Unit
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($units->hasPages())
    <div class="d-flex justify-content-center mt-4">{{ $units->links() }}</div>
    @endif

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Unit' : 'Add Unit' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="saveUnit">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Property <span class="text-danger">*</span></label>
                                <select wire:model="property_id" class="form-select @error('property_id') is-invalid @enderror" @disabled($editMode)>
                                    <option value="">Choose property...</option>
                                    @foreach($properties as $p)
                                        <option value="{{ $p->id }}">{{ $p->property_name }}</option>
                                    @endforeach
                                </select>
                                @error('property_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($editMode) <small class="text-muted">Property cannot be changed after creation.</small> @endif
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Unit Number <span class="text-danger">*</span></label>
                                <input type="text" wire:model="unit_number" class="form-control @error('unit_number') is-invalid @enderror" placeholder="e.g. A-12">
                                @error('unit_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                                <select wire:model="unit_type" class="form-select @error('unit_type') is-invalid @enderror">
                                    <option value="single">Single Room</option>
                                    <option value="double">Double Room</option>
                                    <option value="full_house">Full House</option>
                                    <option value="apartment">Apartment</option>
                                </select>
                                @error('unit_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Floor</label>
                                <input type="number" wire:model="floor_no" class="form-control @error('floor_no') is-invalid @enderror" placeholder="0">
                                @error('floor_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Bedrooms <span class="text-danger">*</span></label>
                                <input type="number" min="0" wire:model="bedrooms" class="form-control @error('bedrooms') is-invalid @enderror">
                                @error('bedrooms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Bathrooms <span class="text-danger">*</span></label>
                                <input type="number" min="0" wire:model="bathrooms" class="form-control @error('bathrooms') is-invalid @enderror">
                                @error('bathrooms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Monthly Rent (TZS) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="monthly_rent" class="form-control @error('monthly_rent') is-invalid @enderror" placeholder="0.00">
                                @error('monthly_rent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Deposit (TZS) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" wire:model="deposit_amount" class="form-control @error('deposit_amount') is-invalid @enderror" placeholder="0.00">
                                @error('deposit_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Utilities</label>
                                <div class="d-flex gap-3 flex-wrap">
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="has_electricity" class="form-check-input" id="has_electricity">
                                        <label class="form-check-label" for="has_electricity"><i class="ti ti-bolt text-warning me-1"></i>Electricity</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="has_water" class="form-check-input" id="has_water">
                                        <label class="form-check-label" for="has_water"><i class="ti ti-droplet text-info me-1"></i>Water</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" wire:model="has_furniture" class="form-check-input" id="has_furniture">
                                        <label class="form-check-label" for="has_furniture"><i class="ti ti-sofa text-secondary me-1"></i>Furnished</label>
                                    </div>
                                </div>
                            </div>

                            @if($features->isNotEmpty())
                            <div class="col-12">
                                <label class="form-label">Features</label>
                                <div class="d-flex gap-2 flex-wrap">
                                    @foreach($features as $f)
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" wire:model="selectedFeatures" value="{{ $f->id }}" class="form-check-input" id="feat-{{ $f->id }}">
                                            <label class="form-check-label" for="feat-{{ $f->id }}">{{ $f->feature_name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="vacant">Vacant</option>
                                    <option value="reserved">Reserved</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                                <small class="text-muted">Occupied is set automatically when a tenancy is active.</small>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional details: views, finish, neighbours, etc."></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-2 rounded border bg-light" style="padding-left: 3rem !important;">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           id="unitPublish" style="cursor:pointer;width:2.5em;height:1.4em;"
                                           wire:model="is_published">
                                    <label class="form-check-label ms-2 fw-medium" for="unitPublish" style="cursor:pointer;">
                                        <i class="ti ti-world me-1 text-info"></i>Publish to public marketplace
                                        <small class="d-block text-muted fw-normal">Visible to everyone on the public site (add a photo for best results).</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Photos</label>
                                <input type="file" wire:model="newImages" class="form-control @error('newImages.*') is-invalid @enderror" multiple accept="image/*">
                                <small class="text-muted">JPG / PNG / WEBP up to 4MB each.</small>
                                @error('newImages.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

                                <div wire:loading wire:target="newImages" class="text-muted small mt-2">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Uploading…
                                </div>

                                @if($editMode && $unitImages->isNotEmpty())
                                <div class="row g-2 mt-2">
                                    @foreach($unitImages as $img)
                                        <div class="col-3 position-relative">
                                            <img src="{{ asset('storage/' . $img->image_url) }}" class="img-thumbnail" style="height:80px;width:100%;object-fit:cover;">
                                            <button type="button"
                                                    wire:click="deleteImage('{{ $img->id }}')"
                                                    wire:confirm="Remove this image?"
                                                    class="btn btn-sm btn-danger position-absolute top-0 end-0 p-1"
                                                    style="line-height:1;">
                                                <i class="ti ti-x"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex gap-2 pt-4">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill" wire:loading.attr="disabled" wire:target="saveUnit,newImages">
                                <span wire:loading.remove wire:target="saveUnit">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update Unit' : 'Save Unit' }}
                                </span>
                                <span wire:loading wire:target="saveUnit">
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
    @if($showViewModal && $viewUnit)
    <div class="modal fade show d-block" tabindex="-1" style="background:rgba(0,0,0,0.5);overflow-y:auto;">
        <div class="modal-dialog modal-dialog-centered modal-lg my-4">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title"><i class="ti ti-home-2 me-2"></i>Unit Details</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal"></button>
                </div>
                <div class="modal-body">
                    @if($viewUnit->images->isNotEmpty())
                        <div class="row g-2 mb-3">
                            @foreach($viewUnit->images as $img)
                                <div class="col-md-3 col-6">
                                    <img src="{{ asset('storage/' . $img->image_url) }}" class="img-thumbnail" style="height:100px;width:100%;object-fit:cover;">
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="text-center mb-3">
                        <h5 class="mb-1">Unit {{ $viewUnit->unit_number }} — {{ $viewUnit->property?->property_name }}</h5>
                        <span class="badge bg-{{ $viewUnit->status === 'vacant' ? 'success' : ($viewUnit->status === 'occupied' ? 'info' : ($viewUnit->status === 'maintenance' ? 'warning' : 'primary')) }}-subtle text-{{ $viewUnit->status === 'vacant' ? 'success' : ($viewUnit->status === 'occupied' ? 'info' : ($viewUnit->status === 'maintenance' ? 'warning' : 'primary')) }}">
                            {{ ucfirst($viewUnit->status) }}
                        </span>
                    </div>

                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-tag me-2"></i>Type</span>
                            <span class="fw-medium">{{ ucwords(str_replace('_',' ',$viewUnit->unit_type)) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-bed me-2"></i>Bedrooms / Bathrooms</span>
                            <span class="fw-medium">{{ $viewUnit->bedrooms }} / {{ $viewUnit->bathrooms }}</span>
                        </div>
                        @if($viewUnit->floor_no !== null)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-stairs me-2"></i>Floor</span>
                            <span class="fw-medium">{{ $viewUnit->floor_no }}</span>
                        </div>
                        @endif
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-cash me-2"></i>Monthly Rent</span>
                            <span class="fw-medium">TZS {{ number_format($viewUnit->monthly_rent, 0) }}</span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-coin me-2"></i>Deposit</span>
                            <span class="fw-medium">TZS {{ number_format($viewUnit->deposit_amount, 0) }}</span>
                        </div>
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-plug me-2"></i>Utilities</span>
                            <span class="small">
                                {{ $viewUnit->has_electricity ? '⚡ Electricity ' : '' }}
                                {{ $viewUnit->has_water ? '💧 Water ' : '' }}
                                {{ $viewUnit->has_furniture ? '🛋 Furnished' : '' }}
                                @unless($viewUnit->has_electricity || $viewUnit->has_water || $viewUnit->has_furniture)
                                    —
                                @endunless
                            </span>
                        </div>
                        @if($viewUnit->features->isNotEmpty())
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-list-check me-2"></i>Features</span>
                            <div class="d-flex gap-1 flex-wrap">
                                @foreach($viewUnit->features as $f)
                                    <span class="badge bg-light text-dark border">{{ $f->feature_name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($viewUnit->activeAgreement)
                        <div class="list-group-item d-flex justify-content-between px-0">
                            <span class="text-muted"><i class="ti ti-user me-2"></i>Current Tenant</span>
                            <span class="fw-medium">{{ $viewUnit->activeAgreement->customer?->name ?? '—' }}</span>
                        </div>
                        @endif
                        @if($viewUnit->description)
                        <div class="list-group-item px-0">
                            <span class="text-muted d-block mb-1"><i class="ti ti-file-description me-2"></i>Description</span>
                            <span>{{ $viewUnit->description }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="d-flex gap-2 pt-4">
                        <button wire:click="closeViewModal" class="btn btn-light flex-fill">Close</button>
                        <button wire:click="openEditModal('{{ $viewUnit->id }}')" class="btn btn-primary flex-fill">
                            <i class="ti ti-edit me-1"></i>Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
