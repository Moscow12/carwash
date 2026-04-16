<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Laundry Management</h4>
            <p class="text-muted mb-0">Manage guest laundry orders and services</p>
        </div>
        @if($selectedBusiness)
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Order
            </button>
        @endif
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

    <!-- Business Selection -->
    @if($businesses->count() > 0)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Select Hotel</label>
                <select wire:model.live="selectedBusiness" class="form-select">
                    @foreach($businesses as $business)
                        <option value="{{ $business->id }}">{{ $business->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($selectedBusiness)
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-clock"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Pending</h6>
                                <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-progress"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">In Progress</h6>
                                <h3 class="mb-0">{{ $stats['in_progress'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check-circle"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Completed Today</h6>
                                <h3 class="mb-0">{{ $stats['completed_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-currency-dollar"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Revenue Today</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_revenue'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Guest name, room, item type...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Guest & Room</th>
                                <th>Item & Service</th>
                                <th>Qty</th>
                                <th>Charge</th>
                                <th>Expected</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $order->guest->full_name }}</div>
                                        @if($order->room)
                                            <small class="text-muted"><i class="ti ti-door me-1"></i>Room {{ $order->room->room_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $order->item_type }}</div>
                                        <span class="badge bg-{{ $order->service_type === 'express' ? 'danger' : 'secondary' }}">
                                            {{ ucfirst($order->service_type) }}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ $order->quantity }}</span></td>
                                    <td>{{ number_format($order->charge_amount, 2) }}</td>
                                    <td>
                                        @if($order->expected_completion)
                                            {{ \Carbon\Carbon::parse($order->expected_completion)->format('M d, h:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($order->status === 'pending')
                                            <span class="badge bg-warning">Pending</span>
                                        @elseif($order->status === 'in_progress')
                                            <span class="badge bg-info">In Progress</span>
                                        @elseif($order->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($order->status === 'delivered')
                                            <span class="badge bg-primary">Delivered</span>
                                        @else
                                            <span class="badge bg-danger">Cancelled</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($order->status === 'pending')
                                                <button wire:click="markInProgress('{{ $order->id }}')" class="btn btn-info" title="Start">
                                                    <i class="ti ti-progress"></i>
                                                </button>
                                            @endif
                                            @if($order->status === 'in_progress')
                                                <button wire:click="markCompleted('{{ $order->id }}')" class="btn btn-success" title="Complete">
                                                    <i class="ti ti-check"></i>
                                                </button>
                                            @endif
                                            @if($order->status === 'completed')
                                                <button wire:click="markDelivered('{{ $order->id }}')" class="btn btn-primary" title="Deliver">
                                                    <i class="ti ti-truck-delivery"></i>
                                                </button>
                                            @endif
                                            @if(in_array($order->status, ['pending', 'in_progress']))
                                                <button wire:click="editOrder('{{ $order->id }}')" class="btn btn-warning" title="Edit">
                                                    <i class="ti ti-edit"></i>
                                                </button>
                                                <button wire:click="cancelOrder('{{ $order->id }}')" class="btn btn-danger" title="Cancel">
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="ti ti-wash-off fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">No laundry orders found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $orders->links() }}</div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-building fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage laundry orders</p>
            </div>
        </div>
    @endif

    <!-- Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-wash me-2"></i>{{ $editMode ? 'Edit' : 'New' }} Laundry Order</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveOrder">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Guest <span class="text-danger">*</span></label>
                                    <select wire:model="guest_id" class="form-select @error('guest_id') is-invalid @enderror">
                                        <option value="">Select Guest</option>
                                        @foreach($guests as $guest)
                                            <option value="{{ $guest->id }}">{{ $guest->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('guest_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Room</label>
                                    <select wire:model="room_id" class="form-select">
                                        <option value="">Select Room</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">{{ $room->full_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Item Type <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="item_type" class="form-control @error('item_type') is-invalid @enderror" placeholder="Shirt, Pants, Bedsheet">
                                    @error('item_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                    <input type="number" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror" min="1">
                                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Service Type <span class="text-danger">*</span></label>
                                    <select wire:model="service_type" class="form-select @error('service_type') is-invalid @enderror">
                                        <option value="regular">Regular</option>
                                        <option value="express">Express</option>
                                    </select>
                                    @error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Charge Amount</label>
                                    <input type="number" step="0.01" wire:model="charge_amount" class="form-control" placeholder="0.00">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Expected Completion</label>
                                    <input type="datetime-local" wire:model="expected_completion" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Special Instructions</label>
                                    <textarea wire:model="special_instructions" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>{{ $editMode ? 'Update' : 'Create' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
