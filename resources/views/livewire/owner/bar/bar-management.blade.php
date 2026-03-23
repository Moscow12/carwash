<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Bar Management</h4>
            <p class="text-muted mb-0">Manage bar tabs, happy hours, and bottle service</p>
        </div>
        @if(in_array($activeTab, ['tabs', 'happy-hours']))
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i>
                @if($activeTab === 'tabs') Open Tab
                @elseif($activeTab === 'happy-hours') Add Happy Hour
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

    <!-- Business & Outlet Selection -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                @if($businesses->count() > 1)
                    <div class="col-md-6">
                        <label class="form-label">Select Business</label>
                        <select wire:model.live="selectedBusiness" class="form-select">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($outlets->count() > 0)
                    <div class="col-md-6">
                        <label class="form-label">Select Bar Outlet</label>
                        <select wire:model.live="selectedOutlet" class="form-select">
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($selectedOutlet)
        <!-- Statistics Dashboard -->
        @include('livewire.owner.bar.bar-management-stats')

        <!-- Quick Links -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h6 class="mb-0"><i class="ti ti-link me-2 text-primary"></i>Quick Links</h6>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('owner.list-items') }}" class="btn btn-sm btn-outline-primary">
                            <i class="ti ti-package me-1"></i> Item Stock
                        </a>
                        <a href="{{ route('owner.categories') }}" class="btn btn-sm btn-outline-info">
                            <i class="ti ti-category me-1"></i> Categories
                        </a>
                        <a href="{{ route('owner.barmanagement') }}?tab=menu-items" class="btn btn-sm btn-outline-success">
                            <i class="ti ti-glass me-1"></i> Bar Menu
                        </a>
                        <a href="{{ route('owner.bar.pos') }}" class="btn btn-sm btn-primary">
                            <i class="ti ti-cash-register me-1"></i> Open POS
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="card shadow-sm">
            <div class="card-header border-bottom">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'tabs' ? 'active' : '' }}"
                           wire:click.prevent="switchTab('tabs')"
                           href="#" role="tab">
                            <i class="ti ti-receipt me-1"></i> Bar Tabs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'happy-hours' ? 'active' : '' }}"
                           wire:click.prevent="switchTab('happy-hours')"
                           href="#" role="tab">
                            <i class="ti ti-clock me-1"></i> Happy Hours
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'bottle-service' ? 'active' : '' }}"
                           wire:click.prevent="switchTab('bottle-service')"
                           href="#" role="tab">
                            <i class="ti ti-bottle me-1"></i> Bottle Service
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'menu-items' ? 'active' : '' }}"
                           wire:click.prevent="switchTab('menu-items')"
                           href="#" role="tab">
                            <i class="ti ti-glass me-1"></i> Menu Items
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <!-- Search Bar -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text"><i class="ti ti-search"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                   class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Bar Tabs Tab -->
                    @if($activeTab === 'tabs')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tab Name</th>
                                        <th>Guest</th>
                                        <th>Status</th>
                                        <th>Opened At</th>
                                        <th>Total Amount</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($tabs as $tab)
                                        <tr>
                                            <td>
                                                <strong>{{ $tab->tab_name }}</strong>
                                            </td>
                                            <td>{{ $tab->guest?->full_name ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $tab->status === 'open' ? 'success' : ($tab->status === 'closed' ? 'secondary' : 'danger') }}">
                                                    {{ ucfirst($tab->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $tab->opened_at?->format('M d, Y H:i') }}</td>
                                            <td>{{ $tab->total_amount ? 'TZS ' . number_format($tab->total_amount, 2) : '-' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if($tab->status === 'open')
                                                        <button wire:click="editTab('{{ $tab->id }}')"
                                                                class="btn btn-outline-primary" title="Edit">
                                                            <i class="ti ti-edit"></i>
                                                        </button>
                                                        <button wire:click="closeTab('{{ $tab->id }}')"
                                                                wire:confirm="Close this tab?"
                                                                class="btn btn-outline-success" title="Close Tab">
                                                            <i class="ti ti-check"></i>
                                                        </button>
                                                        <button wire:click="voidTab('{{ $tab->id }}')"
                                                                wire:confirm="Void this tab?"
                                                                class="btn btn-outline-danger" title="Void">
                                                            <i class="ti ti-x"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="ti ti-inbox" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No bar tabs found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $tabs->links() }}
                        </div>
                    @endif

                    <!-- Happy Hours Tab -->
                    @if($activeTab === 'happy-hours')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu Item</th>
                                        <th>Happy Hour Price</th>
                                        <th>Time Range</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($happyHours as $happyHour)
                                        <tr>
                                            <td><strong>{{ $happyHour->menuItem?->name }}</strong></td>
                                            <td>TZS {{ number_format($happyHour->happy_hour_price, 2) }}</td>
                                            <td>{{ $happyHour->start_time }} - {{ $happyHour->end_time }}</td>
                                            <td>{{ $happyHour->days_of_week ?? 'All Days' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $happyHour->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($happyHour->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button wire:click="editHappyHour('{{ $happyHour->id }}')"
                                                            class="btn btn-outline-primary" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="deleteHappyHour('{{ $happyHour->id }}')"
                                                            wire:confirm="Delete this happy hour?"
                                                            class="btn btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="ti ti-inbox" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No happy hour prices configured</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $happyHours->links() }}
                        </div>
                    @endif

                    <!-- Bottle Service Tab -->
                    @if($activeTab === 'bottle-service')
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Menu Item</th>
                                        <th>Guest</th>
                                        <th>Charge</th>
                                        <th>Consumption</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bottleServices as $service)
                                        <tr>
                                            <td><strong>{{ $service->menuItem?->name }}</strong></td>
                                            <td>{{ $service->guest?->full_name }}</td>
                                            <td>TZS {{ number_format($service->bottle_charge, 2) }}</td>
                                            <td>{{ $service->consumption_percentage }}%</td>
                                            <td>
                                                <span class="badge bg-{{ $service->status === 'consumed' ? 'success' : ($service->status === 'delivered' ? 'primary' : 'warning') }}">
                                                    {{ ucfirst($service->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $service->created_at?->format('M d, Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="ti ti-inbox" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No bottle services found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $bottleServices->links() }}
                        </div>
                    @endif

                    <!-- Menu Items Tab -->
                    @if($activeTab === 'menu-items')
                        <!-- Category Filter -->
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <select wire:model.live="selectedCategory" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock Item</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menuItems as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->name }}</strong>
                                                @if($item->description)
                                                    <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->category)
                                                    <span class="badge bg-info">{{ $item->category->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td><strong class="text-success">TSh {{ number_format($item->price, 0) }}</strong></td>
                                            <td>
                                                @if($item->item)
                                                    <span class="badge bg-primary">{{ $item->item->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                                @if(!$item->is_available)
                                                    <span class="badge bg-warning">Out of Stock</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    @if($item->item_id)
                                                        <a href="{{ route('owner.history', ['itemId' => $item->item_id]) }}"
                                                           class="btn btn-outline-primary"
                                                           title="View Stock History"
                                                           target="_blank">
                                                            <i class="ti ti-history"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('owner.hotel.pos-outlets') }}"
                                                       class="btn btn-outline-info"
                                                       title="Manage Menu"
                                                       target="_blank">
                                                        <i class="ti ti-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                <i class="ti ti-glass-off" style="font-size: 3rem;"></i>
                                                <p class="mt-2">No menu items found</p>
                                                <a href="{{ route('owner.hotel.pos-outlets') }}" class="btn btn-sm btn-primary">
                                                    <i class="ti ti-plus me-1"></i> Add Menu Items
                                                </a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $menuItems->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">
            <i class="ti ti-info-circle me-2"></i>
            Please create a bar outlet first in Hotel → Food & Beverage → Outlets.
        </div>
    @endif

    <!-- Modal for Add/Edit Tab -->
    @if($showModal && $activeTab === 'tabs')
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Tab' : 'Open New Tab' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tab Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="tab_name" class="form-control @error('tab_name') is-invalid @enderror">
                            @error('tab_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Guest (Optional)</label>
                            <select wire:model="guest_id" class="form-select @error('guest_id') is-invalid @enderror">
                                <option value="">-- Select Guest --</option>
                                @foreach($guests as $guest)
                                    <option value="{{ $guest->id }}">{{ $guest->full_name }}</option>
                                @endforeach
                            </select>
                            @error('guest_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Charge to Folio (Optional)</label>
                            <select wire:model="folio_id" class="form-select @error('folio_id') is-invalid @enderror">
                                <option value="">-- Select Folio --</option>
                            </select>
                            @error('folio_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTab">
                            {{ $editMode ? 'Update' : 'Open Tab' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal for Add/Edit Happy Hour -->
    @if($showModal && $activeTab === 'happy-hours')
        <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editMode ? 'Edit Happy Hour' : 'Add Happy Hour Price' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Menu Item <span class="text-danger">*</span></label>
                            <select wire:model="menu_item_id" class="form-select @error('menu_item_id') is-invalid @enderror">
                                <option value="">-- Select Item --</option>
                                @foreach($menuItems as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} ({{ number_format($item->price, 2) }})</option>
                                @endforeach
                            </select>
                            @error('menu_item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Happy Hour Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" wire:model="happy_hour_price" class="form-control @error('happy_hour_price') is-invalid @enderror">
                            @error('happy_hour_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Time <span class="text-danger">*</span></label>
                                <input type="time" wire:model="start_time" class="form-control @error('start_time') is-invalid @enderror">
                                @error('start_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Time <span class="text-danger">*</span></label>
                                <input type="time" wire:model="end_time" class="form-control @error('end_time') is-invalid @enderror">
                                @error('end_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Days of Week (Optional)</label>
                            <input type="text" wire:model="days_of_week" class="form-control" placeholder="e.g., Mon,Tue,Wed or leave empty for all days">
                            <small class="text-muted">Comma-separated days</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="happy_hour_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveHappyHour">
                            {{ $editMode ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
