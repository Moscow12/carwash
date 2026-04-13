<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Room Types Management</h4>
            <p class="text-muted mb-0">Manage room categories and pricing</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Room Type
        </button>
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
        <!-- Search -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search room types...">
            </div>
        </div>

        <!-- Room Types Grid -->
        <div class="row g-3">
            @forelse($roomTypes as $roomType)
                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="mb-1">{{ $roomType->name }}</h5>
                                    <span class="badge bg-{{ $roomType->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($roomType->status) }}
                                    </span>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                        <i class="ti ti-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" wire:click="editRoomType('{{ $roomType->id }}')"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                        <li><a class="dropdown-item" wire:click="toggleStatus('{{ $roomType->id }}')"><i class="ti ti-toggle-left me-2"></i>Toggle Status</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" wire:click="delete('{{ $roomType->id }}')" wire:confirm="Are you sure?"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>

                            @if($roomType->description)
                                <p class="text-muted small mb-3">{{ Str::limit($roomType->description, 100) }}</p>
                            @endif

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted"><i class="ti ti-users me-1"></i>Capacity:</span>
                                    <span class="fw-bold">{{ $roomType->max_adults }} Adults, {{ $roomType->max_children }} Children</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted"><i class="ti ti-currency-dollar me-1"></i>Base Price:</span>
                                    <span class="fw-bold text-primary">{{ number_format($roomType->base_price, 2) }}/night</span>
                                </div>
                                @if($roomType->weekend_price)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted"><i class="ti ti-calendar-weekend me-1"></i>Weekend:</span>
                                        <span class="fw-bold text-success">{{ number_format($roomType->weekend_price, 2) }}/night</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted"><i class="ti ti-door me-1"></i>Total Rooms:</span>
                                    <span class="badge bg-info">{{ $roomType->rooms_count }}</span>
                                </div>
                            </div>

                            @if($roomType->amenities && count($roomType->amenities) > 0)
                                <div>
                                    <small class="text-muted d-block mb-2">Amenities:</small>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach(array_slice($roomType->amenities, 0, 5) as $amenity)
                                            <span class="badge bg-light text-dark">{{ $availableAmenities[$amenity] ?? $amenity }}</span>
                                        @endforeach
                                        @if(count($roomType->amenities) > 5)
                                            <span class="badge bg-light text-dark">+{{ count($roomType->amenities) - 5 }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="ti ti-bed fs-1 text-muted"></i>
                            <p class="text-muted mt-2">No room types found. Click "Add Room Type" to create one.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $roomTypes->links() }}
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage room types</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-bed me-2"></i>
                            {{ $editMode ? 'Edit Room Type' : 'Add New Room Type' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Room Type Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Deluxe Suite">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control" rows="3" placeholder="Brief description of this room type..."></textarea>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Maximum Adults <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="max_adults" class="form-control @error('max_adults') is-invalid @enderror" min="1" max="10">
                                    @error('max_adults')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Maximum Children <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="max_children" class="form-control @error('max_children') is-invalid @enderror" min="0" max="10">
                                    @error('max_children')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Base Price (Weekday) <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="base_price" class="form-control @error('base_price') is-invalid @enderror" step="0.01" min="0">
                                    @error('base_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Weekend Price</label>
                                    <input type="number" wire:model="weekend_price" class="form-control" step="0.01" min="0" placeholder="Optional">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Amenities</label>
                                    <div class="row g-2">
                                        @foreach($availableAmenities as $key => $label)
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" wire:model="selectedAmenities" value="{{ $key }}" id="amenity_{{ $key }}">
                                                    <label class="form-check-label" for="amenity_{{ $key }}">
                                                        {{ $label }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Room Type' : 'Save Room Type' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
