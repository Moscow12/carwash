<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Booking Sources Management</h4>
            <p class="text-muted mb-0">Manage OTA channels and booking partners</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Booking Source
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
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search booking sources...">
            </div>
        </div>

        <!-- Booking Sources Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Source Name</th>
                                <th>Type</th>
                                <th>Commission %</th>
                                <th>Reservations</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sources as $source)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $source->name }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $typeColors = [
                                                'direct' => 'primary',
                                                'ota' => 'info',
                                                'corporate' => 'success',
                                                'travel_agent' => 'warning',
                                                'walk_in' => 'secondary',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $typeColors[$source->type] ?? 'secondary' }}">
                                            {{ ucfirst(str_replace('_', ' ', $source->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($source->commission_pct > 0)
                                            <span class="text-danger">{{ $source->commission_pct }}%</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $source->reservations_count }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $source->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($source->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button wire:click="editSource('{{ $source->id }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button wire:click="delete('{{ $source->id }}')"
                                                    wire:confirm="Are you sure you want to delete this booking source?"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="ti ti-source fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No booking sources found. Click "Add Booking Source" to create one.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $sources->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage booking sources</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-source me-2"></i>
                            {{ $editMode ? 'Edit Booking Source' : 'Add New Booking Source' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="mb-3">
                                <label class="form-label">Source Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Booking.com, Expedia">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="direct">Direct Booking</option>
                                    <option value="ota">Online Travel Agency (OTA)</option>
                                    <option value="corporate">Corporate</option>
                                    <option value="travel_agent">Travel Agent</option>
                                    <option value="walk_in">Walk-In</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Commission Percentage</label>
                                <input type="number" wire:model="commission_pct" class="form-control @error('commission_pct') is-invalid @enderror" step="0.01" min="0" max="100" placeholder="0.00">
                                @error('commission_pct')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Commission charged by this booking source</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Source' : 'Save Source' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
