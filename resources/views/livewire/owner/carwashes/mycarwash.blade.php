<div>
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">My Carwashes</h3>
            <p class="text-muted mb-0">Manage your carwash locations</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Carwash
        </button>
    </div>

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search carwashes...">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="text-muted">{{ $carwashes->total() }} carwash(es) found</span>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 ps-4">Name</th>
                            <th class="border-0">Location</th>
                            <th class="border-0">Contact</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($carwashes as $carwash)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm bg-primary-subtle text-primary rounded me-3">
                                        <i class="ti ti-car-wash"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $carwash->name }}</h6>
                                        <small class="text-muted">{{ Str::limit($carwash->address, 30) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="d-block">{{ $carwash->regions->name ?? '-' }}</span>
                                    <small class="text-muted">
                                        {{ $carwash->districts->name ?? '' }}{{ $carwash->wards ? ', ' . $carwash->wards->name : '' }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="d-block">{{ $carwash->resentative_name }}</span>
                                    <small class="text-muted">{{ $carwash->resentative_phone }}</small>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-{{ $carwash->status === 'active' ? 'success' : 'secondary' }}-subtle text-{{ $carwash->status === 'active' ? 'success' : 'secondary' }}">
                                    <i class="ti ti-{{ $carwash->status === 'active' ? 'check' : 'x' }} me-1"></i>
                                    {{ ucfirst($carwash->status) }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('owner.mycarwash', ['id' => $carwash->id]) }}"
                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                    <button wire:click="editCarwash('{{ $carwash->id }}')"
                                            class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-car-wash display-4 d-block mb-3 opacity-50"></i>
                                    <h5>No carwashes found</h5>
                                    <p class="mb-3">Get started by adding your first carwash</p>
                                    <button wire:click="openModal" class="btn btn-primary btn-sm">
                                        <i class="ti ti-plus me-1"></i> Add Carwash
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($carwashes->hasPages())
        <div class="card-footer bg-transparent border-top">
            {{ $carwashes->links() }}
        </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeModal">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 900px;">
            <div class="modal-content border-0 shadow" style="max-height: 90vh;">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Carwash' : 'Add New Carwash' }}
                    </h5>
                    <button type="button" wire:click="closeModal" class="btn-close"></button>
                </div>

                <form wire:submit="save">
                    <div class="modal-body" style="overflow-y: auto; max-height: calc(90vh - 130px);">
                        {{-- Basic Information --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-info-circle me-1"></i> Basic Information
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Carwash Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter carwash name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" wire:model="address" class="form-control @error('address') is-invalid @enderror" placeholder="Enter full address">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Brief description of your carwash"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Operating Hours</label>
                                <input type="text" wire:model="operating_hours" class="form-control" placeholder="e.g., 8:00 AM - 6:00 PM">
                            </div>
                        </div>

                        {{-- Location --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-map-pin me-1"></i> Location
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Region <span class="text-danger">*</span></label>
                                <select wire:model.live="region_id" class="form-select @error('region_id') is-invalid @enderror">
                                    <option value="">Select Region</option>
                                    @foreach($allRegions as $region)
                                        <option value="{{ $region->id }}">{{ $region->name }}</option>
                                    @endforeach
                                </select>
                                @error('region_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District <span class="text-danger">*</span></label>
                                <select wire:model.live="district_id" class="form-select @error('district_id') is-invalid @enderror" {{ empty($allDistricts) ? 'disabled' : '' }}>
                                    <option value="">Select District</option>
                                    @foreach($allDistricts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                                @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ward <span class="text-danger">*</span></label>
                                <select wire:model.live="ward_id" class="form-select @error('ward_id') is-invalid @enderror" {{ empty($allWards) ? 'disabled' : '' }}>
                                    <option value="">Select Ward</option>
                                    @foreach($allWards as $ward)
                                        <option value="{{ $ward->id }}">{{ $ward->name }}</option>
                                    @endforeach
                                </select>
                                @error('ward_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Street</label>
                                <select wire:model="street_id" class="form-select" {{ empty($allStreets) ? 'disabled' : '' }}>
                                    <option value="">Select Street (Optional)</option>
                                    @foreach($allStreets as $street)
                                        <option value="{{ $street->id }}">{{ $street->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Representative --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-user me-1"></i> Representative
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Representative Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="resentative_name" class="form-control @error('resentative_name') is-invalid @enderror" placeholder="Full name">
                                @error('resentative_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Representative Phone <span class="text-danger">*</span></label>
                                <input type="text" wire:model="resentative_phone" class="form-control @error('resentative_phone') is-invalid @enderror" placeholder="Phone number">
                                @error('resentative_phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Contact & Social --}}
                        <h6 class="text-primary mb-3">
                            <i class="ti ti-world me-1"></i> Contact & Social Media
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-mail"></i></span>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="email@example.com">
                                </div>
                                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Website</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-world"></i></span>
                                    <input type="url" wire:model="website" class="form-control @error('website') is-invalid @enderror" placeholder="https://example.com">
                                </div>
                                @error('website') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">WhatsApp</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-brand-whatsapp"></i></span>
                                    <input type="text" wire:model="whatsapp" class="form-control" placeholder="+255...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Instagram</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-brand-instagram"></i></span>
                                    <input type="text" wire:model="instagram" class="form-control" placeholder="@username">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-light">
                        <button type="button" wire:click="closeModal" class="btn btn-outline-secondary">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save">
                                <i class="ti ti-{{ $editMode ? 'check' : 'plus' }} me-1"></i>
                                {{ $editMode ? 'Update Carwash' : 'Create Carwash' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
