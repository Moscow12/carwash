<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Food & Beverage Management</h4>
            <p class="text-muted mb-0">Manage outlets, tables, orders, and POS sessions</p>
        </div>
        @if(in_array($activeTab, ['outlets', 'tables']))
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                @if($activeTab === 'outlets') Add Outlet
                @else Add Table
                @endif
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
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-building-store"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Active Outlets</h6>
                                <h3 class="mb-0">{{ $stats['active_outlets'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-table"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Tables</h6>
                                <h3 class="mb-0">{{ $stats['total_tables'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-users"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Occupied Tables</h6>
                                <h3 class="mb-0">{{ $stats['occupied_tables'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-receipt"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Orders Today</h6>
                                <h3 class="mb-0">{{ $stats['orders_today'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'outlets' ? 'active' : '' }}"
                        wire:click="switchTab('outlets')"
                        type="button">
                    <i class="ti ti-building-store me-1"></i> Outlets
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'tables' ? 'active' : '' }}"
                        wire:click="switchTab('tables')"
                        type="button">
                    <i class="ti ti-table me-1"></i> Tables
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'orders' ? 'active' : '' }}"
                        wire:click="switchTab('orders')"
                        type="button">
                    <i class="ti ti-receipt me-1"></i> Orders
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'sessions' ? 'active' : '' }}"
                        wire:click="switchTab('sessions')"
                        type="button">
                    <i class="ti ti-cash-register me-1"></i> Sessions
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Outlets Tab -->
            @if($activeTab === 'outlets')
                <!-- Search -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search outlets...">
                    </div>
                </div>

                <!-- Outlets Table -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Operating Hours</th>
                                        <th>Tables</th>
                                        <th>Orders</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($outlets as $outlet)
                                        <tr>
                                            <td><strong>{{ $outlet->name }}</strong></td>
                                            <td>
                                                @php
                                                    $typeColors = [
                                                        'restaurant' => 'primary',
                                                        'bar' => 'danger',
                                                        'cafe' => 'info',
                                                        'room_service' => 'success',
                                                        'pool_bar' => 'warning',
                                                        'takeaway' => 'secondary',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $typeColors[$outlet->type] ?? 'secondary' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $outlet->type)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($outlet->open_time && $outlet->close_time)
                                                    <small>{{ $outlet->open_time }} - {{ $outlet->close_time }}</small>
                                                @else
                                                    <span class="text-muted">24/7</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-info">{{ $outlet->tables_count }}</span></td>
                                            <td><span class="badge bg-success">{{ $outlet->orders_count }}</span></td>
                                            <td>
                                                <span class="badge bg-{{ $outlet->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($outlet->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button wire:click="editOutlet('{{ $outlet->id }}')"
                                                            class="btn btn-sm btn-outline-primary"
                                                            title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="deleteOutlet('{{ $outlet->id }}')"
                                                            wire:confirm="Are you sure you want to delete this outlet?"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti ti-building-store fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No outlets found. Click "Add Outlet" to create one.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $outlets->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Tables Tab -->
            @if($activeTab === 'tables')
                <!-- Search -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search tables...">
                    </div>
                </div>

                <!-- Tables Grid -->
                <div class="row g-3">
                    @forelse($tables as $table)
                        <div class="col-md-3">
                            <div class="card shadow-sm h-100 border-start border-{{ $table->status === 'available' ? 'success' : ($table->status === 'occupied' ? 'danger' : 'warning') }} border-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">Table {{ $table->table_number }}</h5>
                                            <small class="text-muted">{{ $table->outlet->name }}</small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                <i class="ti ti-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" wire:click="editTable('{{ $table->id }}')">
                                                    <i class="ti ti-edit me-1"></i> Edit
                                                </a></li>
                                                <li><a class="dropdown-item text-danger" wire:click="deleteTable('{{ $table->id }}')" wire:confirm="Delete this table?">
                                                    <i class="ti ti-trash me-1"></i> Delete
                                                </a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <span class="badge bg-secondary">
                                            <i class="ti ti-users me-1"></i>{{ $table->capacity }} seats
                                        </span>
                                    </div>

                                    <div class="mb-2">
                                        @php
                                            $statusColors = [
                                                'available' => 'success',
                                                'occupied' => 'danger',
                                                'reserved' => 'warning',
                                                'cleaning' => 'info',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$table->status] ?? 'secondary' }} w-100">
                                            {{ ucfirst($table->status) }}
                                        </span>
                                    </div>

                                    @if($table->status !== 'available')
                                        <button wire:click="changeTableStatus('{{ $table->id }}', 'available')"
                                                class="btn btn-sm btn-outline-success w-100">
                                            <i class="ti ti-check me-1"></i> Mark Available
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="card shadow-sm">
                                <div class="card-body text-center py-5">
                                    <i class="ti ti-table fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">No tables found. Click "Add Table" to create one.</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $tables->links() }}
                </div>
            @endif

            <!-- Orders Tab -->
            @if($activeTab === 'orders')
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Order No.</th>
                                        <th>Outlet</th>
                                        <th>Table</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $order->order_no }}</span></td>
                                            <td>{{ $order->outlet->name }}</td>
                                            <td>
                                                @if($order->table)
                                                    Table {{ $order->table->table_number }}
                                                @else
                                                    <span class="text-muted">Walk-in</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ number_format($order->total_amount, 2) }}</strong></td>
                                            <td>
                                                @php
                                                    $orderStatusColors = [
                                                        'pending' => 'warning',
                                                        'preparing' => 'info',
                                                        'served' => 'success',
                                                        'paid' => 'success',
                                                        'cancelled' => 'danger',
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $orderStatusColors[$order->status] ?? 'secondary' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, H:i') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti ti-receipt fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No orders found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Sessions Tab -->
            @if($activeTab === 'sessions')
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Session No.</th>
                                        <th>Outlet</th>
                                        <th>Opened At</th>
                                        <th>Closed At</th>
                                        <th>Total Sales</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td><span class="badge bg-primary">{{ $session->session_no }}</span></td>
                                            <td>{{ $session->outlet->name }}</td>
                                            <td>{{ $session->opened_at->format('M d, H:i') }}</td>
                                            <td>
                                                @if($session->closed_at)
                                                    {{ $session->closed_at->format('M d, H:i') }}
                                                @else
                                                    <span class="badge bg-success">Open</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ number_format($session->total_sales, 2) }}</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $session->status === 'open' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($session->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="ti ti-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <i class="ti ti-cash-register fs-1 text-muted"></i>
                                                <p class="text-muted mt-2">No sessions found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $sessions->links() }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage F&B operations</p>
            </div>
        </div>
    @endif

    <!-- Outlet Modal -->
    @if($showModal && $activeTab === 'outlets')
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-building-store me-2"></i>
                            {{ $editMode ? 'Edit Outlet' : 'Add Outlet' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveOutlet">
                            <div class="mb-3">
                                <label class="form-label">Outlet Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="outlet_name" class="form-control @error('outlet_name') is-invalid @enderror">
                                @error('outlet_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="outlet_type" class="form-select @error('outlet_type') is-invalid @enderror">
                                    <option value="restaurant">Restaurant</option>
                                    <option value="bar">Bar</option>
                                    <option value="cafe">Cafe</option>
                                    <option value="room_service">Room Service</option>
                                    <option value="pool_bar">Pool Bar</option>
                                    <option value="takeaway">Takeaway</option>
                                </select>
                                @error('outlet_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Open Time</label>
                                    <input type="time" wire:model="open_time" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Close Time</label>
                                    <input type="time" wire:model="close_time" class="form-control">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="outlet_status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveOutlet">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Outlet' : 'Save Outlet' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Modal -->
    @if($showModal && $activeTab === 'tables')
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-table me-2"></i>
                            {{ $editMode ? 'Edit Table' : 'Add Table' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveTable">
                            <div class="mb-3">
                                <label class="form-label">Outlet <span class="text-danger">*</span></label>
                                <select wire:model="selectedOutlet" class="form-select @error('selectedOutlet') is-invalid @enderror">
                                    <option value="">-- Select Outlet --</option>
                                    @foreach($outlets as $outlet)
                                        <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedOutlet')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Table Number <span class="text-danger">*</span></label>
                                <input type="text" wire:model="table_number" class="form-control @error('table_number') is-invalid @enderror">
                                @error('table_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Capacity <span class="text-danger">*</span></label>
                                <input type="number" wire:model="table_capacity" class="form-control @error('table_capacity') is-invalid @enderror" min="1" max="20">
                                @error('table_capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="table_status" class="form-select">
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="reserved">Reserved</option>
                                    <option value="cleaning">Cleaning</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTable">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editMode ? 'Update Table' : 'Save Table' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
