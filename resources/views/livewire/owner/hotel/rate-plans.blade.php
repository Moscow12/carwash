<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Rate Plans Management</h4>
            <p class="text-muted mb-0">Manage pricing strategies and meal plans</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Rate Plan
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
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search rate plans...">
            </div>
        </div>

        <!-- Rate Plans Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Plan Name</th>
                                <th>Room Type</th>
                                <th>Meal Plan</th>
                                <th>Price/Night</th>
                                <th>Min Nights</th>
                                <th>Validity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ratePlans as $plan)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $plan->name }}</div>
                                    </td>
                                    <td>{{ $plan->roomType->name }}</td>
                                    <td>
                                        @php
                                            $mealPlans = [
                                                'RO' => ['label' => 'Room Only', 'color' => 'secondary'],
                                                'BB' => ['label' => 'Bed & Breakfast', 'color' => 'info'],
                                                'HB' => ['label' => 'Half Board', 'color' => 'primary'],
                                                'FB' => ['label' => 'Full Board', 'color' => 'success'],
                                                'AI' => ['label' => 'All Inclusive', 'color' => 'warning'],
                                            ];
                                            $mealData = $mealPlans[$plan->meal_plan] ?? ['label' => $plan->meal_plan, 'color' => 'secondary'];
                                        @endphp
                                        <span class="badge bg-{{ $mealData['color'] }}">{{ $mealData['label'] }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ number_format($plan->price, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $plan->min_nights }}</span>
                                    </td>
                                    <td>
                                        @if($plan->valid_from && $plan->valid_to)
                                            <small>{{ $plan->valid_from->format('M d') }} - {{ $plan->valid_to->format('M d, Y') }}</small>
                                        @else
                                            <span class="text-muted">Always</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $plan->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($plan->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button wire:click="editRatePlan('{{ $plan->id }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button wire:click="delete('{{ $plan->id }}')"
                                                    wire:confirm="Are you sure you want to delete this rate plan?"
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
                                        <i class="ti ti-currency-dollar fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No rate plans found. Click "Add Rate Plan" to create one.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $ratePlans->links() }}
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage rate plans</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-currency-dollar me-2"></i>
                            {{ $editMode ? 'Edit Rate Plan' : 'Add New Rate Plan' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Early Bird Special">
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

                                <div class="col-md-6">
                                    <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                    <select wire:model="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                        <option value="">-- Select Room Type --</option>
                                        @foreach($roomTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('room_type_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Meal Plan <span class="text-danger">*</span></label>
                                    <select wire:model="meal_plan" class="form-select @error('meal_plan') is-invalid @enderror">
                                        <option value="RO">Room Only (RO)</option>
                                        <option value="BB">Bed & Breakfast (BB)</option>
                                        <option value="HB">Half Board (HB)</option>
                                        <option value="FB">Full Board (FB)</option>
                                        <option value="AI">All Inclusive (AI)</option>
                                    </select>
                                    @error('meal_plan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Price per Night <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="price" class="form-control @error('price') is-invalid @enderror" step="0.01" min="0">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Minimum Nights <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="min_nights" class="form-control @error('min_nights') is-invalid @enderror" min="1">
                                    @error('min_nights')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Valid From</label>
                                    <input type="date" wire:model="valid_from" class="form-control @error('valid_from') is-invalid @enderror">
                                    @error('valid_from')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Valid To</label>
                                    <input type="date" wire:model="valid_to" class="form-control @error('valid_to') is-invalid @enderror">
                                    @error('valid_to')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Leave empty for no expiry</small>
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
                            {{ $editMode ? 'Update Plan' : 'Save Plan' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
