<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Hotel Management</h4>
            <p class="text-muted mb-0">Manage your hotel properties</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Hotel
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

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-primary text-white rounded">
                                <i class="ti ti-bed fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Hotels</h6>
                            <h3 class="mb-0">{{ $totalHotels }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-success text-white rounded">
                                <i class="ti ti-check-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Active Hotels</h6>
                            <h3 class="mb-0">{{ $activeHotels }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-lg bg-warning text-white rounded">
                                <i class="ti ti-pause-circle fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Inactive Hotels</h6>
                            <h3 class="mb-0">{{ $inactiveHotels }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search hotels by name, address, or email...">
                </div>
            </div>
        </div>
    </div>

    <!-- Hotels Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Hotel Name</th>
                            <th>Location</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Branches</th>
                            <th>Reservations</th>
                            <th>Guests</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotels as $hotel)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $hotel->name }}</div>
                                    <small class="text-muted">{{ Str::limit($hotel->description, 50) }}</small>
                                </td>
                                <td>
                                    <div>{{ $hotel->regions?->name }}</div>
                                    <small class="text-muted">{{ $hotel->districts?->name }}, {{ $hotel->wards?->name }}</small>
                                </td>
                                <td>
                                    <div><i class="ti ti-phone me-1"></i>{{ $hotel->resentative_phone }}</div>
                                    @if($hotel->email)
                                        <small class="text-muted"><i class="ti ti-mail me-1"></i>{{ $hotel->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <button wire:click="toggleStatus('{{ $hotel->id }}')" class="btn btn-sm btn-{{ $hotel->status === 'active' ? 'success' : 'warning' }}">
                                        {{ ucfirst($hotel->status) }}
                                    </button>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $hotel->hotel_branches_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $hotel->reservations_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $hotel->guests_count ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button wire:click="editHotel('{{ $hotel->id }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button wire:click="delete('{{ $hotel->id }}')"
                                                wire:confirm="Are you sure you want to delete this hotel?"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="ti ti-bed fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No hotels found. Click "Add Hotel" to create one.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $hotels->links() }}
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-bed me-2"></i>
                            {{ $editMode ? 'Edit Hotel' : 'Add New Hotel' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Hotel Information -->
                                <div class="col-12">
                                    <h6 class="mb-3">Hotel Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Hotel Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter hotel name">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <x-forms.select2
                                        name="status"
                                        :options="collect(['active' => 'Active', 'inactive' => 'Inactive'])"
                                        wire:model="status"
                                        wrapper="false"
                                    />
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description</label>
                                    <textarea wire:model="description" class="form-control" rows="2" placeholder="Brief description of the hotel"></textarea>
                                </div>

                                <!-- Location Information -->
                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="mb-3">Location Information</h6>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="address" class="form-control @error('address') is-invalid @enderror" placeholder="Street address">
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Region <span class="text-danger">*</span></label>
                                    <x-forms.select2
                                        name="region_id"
                                        :options="collect($allRegions)->pluck('name', 'id')"
                                        wire:model.live="region_id"
                                        placeholder="Select Region"
                                        wrapper="false"
                                    />
                                    @error('region_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">District <span class="text-danger">*</span></label>
                                    <x-forms.select2
                                        name="district_id"
                                        :options="collect($allDistricts)->pluck('name', 'id')"
                                        wire:model.live="district_id"
                                        placeholder="Select District"
                                        wrapper="false"
                                    />
                                    @error('district_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Ward <span class="text-danger">*</span></label>
                                    <x-forms.select2
                                        name="ward_id"
                                        :options="collect($allWards)->pluck('name', 'id')"
                                        wire:model.live="ward_id"
                                        placeholder="Select Ward"
                                        wrapper="false"
                                    />
                                    @error('ward_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Street</label>
                                    <x-forms.select2
                                        name="street_id"
                                        :options="collect($allStreets)->pluck('name', 'id')"
                                        wire:model="street_id"
                                        placeholder="Select Street (Optional)"
                                        wrapper="false"
                                    />
                                </div>

                                <!-- Representative Information -->
                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="mb-3">Representative Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Representative Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="resentative_name" class="form-control @error('resentative_name') is-invalid @enderror" placeholder="Contact person name">
                                    @error('resentative_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Representative Phone <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="resentative_phone" class="form-control @error('resentative_phone') is-invalid @enderror" placeholder="+255 XXX XXX XXX">
                                    @error('resentative_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Contact Information -->
                                <div class="col-12">
                                    <hr class="my-2">
                                    <h6 class="mb-3">Contact Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" placeholder="hotel@example.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" wire:model="website" class="form-control" placeholder="https://example.com">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">WhatsApp</label>
                                    <input type="text" wire:model="whatsapp" class="form-control" placeholder="+255 XXX XXX XXX">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Instagram</label>
                                    <input type="text" wire:model="instagram" class="form-control" placeholder="@hotelname">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Operating Hours</label>
                                    <input type="text" wire:model="operating_hours" class="form-control" placeholder="e.g., 24/7 or Mon-Sun: 8:00 AM - 10:00 PM">
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
                            {{ $editMode ? 'Update Hotel' : 'Save Hotel' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
