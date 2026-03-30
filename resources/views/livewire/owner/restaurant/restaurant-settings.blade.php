<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Restaurant Settings</h4>
            <p class="text-muted mb-0">Configure outlets, tables, menu categories, and menu items</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            @if(count($ownerBusinesses) > 1)
                <div style="width: 200px;">
                    <x-forms.select2
                        name="selectedBusiness"
                        :options="$ownerBusinesses"
                        wire:model.live="selectedBusiness"
                        wrapper="false"
                    />
                </div>
            @endif
            @if($activeTab !== 'outlets' && !empty($outlets))
                <div style="width: 200px;">
                    <select wire:model.live="selectedOutlet" class="form-select">
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet['id'] }}">{{ $outlet['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
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

    <div class="row">
        <!-- Left Sidebar - Tabs -->
        <div class="col-md-3 col-lg-2">
            <div class="card shadow-sm">
                <div class="card-body p-2">
                    <ul class="nav nav-pills flex-column" role="tablist">
                        @foreach($tabs as $key => $tab)
                            <li class="nav-item" role="presentation">
                                <button
                                    wire:click="setTab('{{ $key }}')"
                                    class="nav-link text-start w-100 {{ $activeTab === $key ? 'active' : '' }}"
                                    type="button"
                                >
                                    <i class="ti {{ $tab['icon'] }} me-2"></i>
                                    {{ $tab['label'] }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Outlets Tab -->
                    @if($activeTab === 'outlets')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0"><i class="ti ti-building-store me-2"></i>Outlets Management</h5>
                            <button wire:click="openOutletModal" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i> Add Outlet
                            </button>
                        </div>

                        @if(count($outlets) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Hours</th>
                                            <th class="text-center" style="width: 100px;">Status</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($outlets as $outlet)
                                            <tr>
                                                <td>
                                                    <i class="ti ti-building-store me-2 text-primary"></i>
                                                    <strong>{{ $outlet['name'] }}</strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">{{ ucfirst($outlet['type']) }}</span>
                                                </td>
                                                <td>{{ $outlet['open_time'] }} - {{ $outlet['close_time'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $outlet['status'] === 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($outlet['status']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="editOutlet('{{ $outlet['id'] }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="toggleOutletStatus('{{ $outlet['id'] }}')" class="btn btn-sm btn-outline-{{ $outlet['status'] === 'active' ? 'warning' : 'success' }}" title="{{ $outlet['status'] === 'active' ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ti ti-{{ $outlet['status'] === 'active' ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteOutlet('{{ $outlet['id'] }}')" wire:confirm="Are you sure you want to delete this outlet?" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>No outlets configured. Click "Add Outlet" to create one.
                            </div>
                        @endif
                    @endif

                    <!-- Tables Tab -->
                    @if($activeTab === 'tables')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0"><i class="ti ti-armchair me-2"></i>Tables Management</h5>
                            <button wire:click="openTableModal" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i> Add Table
                            </button>
                        </div>

                        @if(!$selectedOutlet)
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>Please select an outlet first to manage tables.
                            </div>
                        @elseif(count($tables) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Table #</th>
                                            <th>Capacity</th>
                                            <th>Section</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" style="width: 100px;">Active</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tables as $table)
                                            <tr>
                                                <td>
                                                    <i class="ti ti-armchair me-2 text-primary"></i>
                                                    <strong>{{ $table['table_number'] }}</strong>
                                                </td>
                                                <td>{{ $table['capacity'] }} seats</td>
                                                <td>{{ $table['section'] ?: '-' }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $table['status'] === 'available' ? 'success' : ($table['status'] === 'occupied' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($table['status']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $table['is_active'] ? 'success' : 'secondary' }}">
                                                        {{ $table['is_active'] ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="editTable('{{ $table['id'] }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="toggleTableStatus('{{ $table['id'] }}')" class="btn btn-sm btn-outline-{{ $table['is_active'] ? 'warning' : 'success' }}" title="{{ $table['is_active'] ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ti ti-{{ $table['is_active'] ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteTable('{{ $table['id'] }}')" wire:confirm="Are you sure you want to delete this table?" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>No tables configured. Click "Add Table" to create one.
                            </div>
                        @endif
                    @endif

                    <!-- Categories Tab -->
                    @if($activeTab === 'categories')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0"><i class="ti ti-category me-2"></i>Menu Categories</h5>
                            <button wire:click="openCategoryModal" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i> Add Category
                            </button>
                        </div>

                        @if(!$selectedOutlet)
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>Please select an outlet first to manage categories.
                            </div>
                        @elseif(count($categories) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th class="text-center">Sort Order</th>
                                            <th class="text-center" style="width: 100px;">Status</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($categories as $category)
                                            <tr>
                                                <td>
                                                    <i class="ti ti-category me-2 text-primary"></i>
                                                    <strong>{{ $category['name'] }}</strong>
                                                </td>
                                                <td>{{ $category['description'] ?: '-' }}</td>
                                                <td class="text-center">{{ $category['sort_order'] }}</td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $category['status'] === 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($category['status']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="editCategory('{{ $category['id'] }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="toggleCategoryStatus('{{ $category['id'] }}')" class="btn btn-sm btn-outline-{{ $category['status'] === 'active' ? 'warning' : 'success' }}" title="{{ $category['status'] === 'active' ? 'Deactivate' : 'Activate' }}">
                                                        <i class="ti ti-{{ $category['status'] === 'active' ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteCategory('{{ $category['id'] }}')" wire:confirm="Are you sure you want to delete this category?" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>No categories configured. Click "Add Category" to create one.
                            </div>
                        @endif
                    @endif

                    <!-- Menu Items Tab -->
                    @if($activeTab === 'menu_items')
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0"><i class="ti ti-chef-hat me-2"></i>Menu Items</h5>
                            <button wire:click="openMenuItemModal" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i> Add Menu Item
                            </button>
                        </div>

                        @if(!$selectedOutlet)
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>Please select an outlet first to manage menu items.
                            </div>
                        @else
                            <!-- Search Bar -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-search"></i></span>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="menuItemSearch"
                                        class="form-control"
                                        placeholder="Search menu items by name, description, or SKU..."
                                    >
                                    @if($menuItemSearch)
                                        <button class="btn btn-outline-secondary" wire:click="$set('menuItemSearch', '')" type="button">
                                            <i class="ti ti-x"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>

                            @if(count($menuItems) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th class="text-center">Available</th>
                                            <th class="text-center" style="width: 100px;">Status</th>
                                            <th class="text-center" style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($menuItems as $item)
                                            <tr>
                                                <td>
                                                    <i class="ti ti-chef-hat me-2 text-primary"></i>
                                                    <strong>{{ $item['name'] }}</strong>
                                                    @if(!empty($item['description']))
                                                        <br><small class="text-muted">{{ Str::limit($item['description'], 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $item['category']['name'] ?? '-' }}</td>
                                                <td><strong>TZS {{ number_format($item['price'], 2) }}</strong></td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $item['is_available'] ? 'success' : 'warning' }}">
                                                        {{ $item['is_available'] ? 'Yes' : 'No' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-{{ $item['status'] === 'active' ? 'success' : 'secondary' }}">
                                                        {{ ucfirst($item['status']) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="editMenuItem('{{ $item['id'] }}')" class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="ti ti-edit"></i>
                                                    </button>
                                                    <button wire:click="toggleMenuItemAvailability('{{ $item['id'] }}')" class="btn btn-sm btn-outline-{{ $item['is_available'] ? 'warning' : 'success' }}" title="{{ $item['is_available'] ? 'Mark Unavailable' : 'Mark Available' }}">
                                                        <i class="ti ti-{{ $item['is_available'] ? 'eye-off' : 'eye' }}"></i>
                                                    </button>
                                                    <button wire:click="deleteMenuItem('{{ $item['id'] }}')" wire:confirm="Are you sure you want to delete this menu item?" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="ti ti-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="ti ti-info-circle me-2"></i>
                                    @if($menuItemSearch)
                                        No menu items found matching "{{ $menuItemSearch }}". <button wire:click="$set('menuItemSearch', '')" class="btn btn-link p-0">Clear search</button>
                                    @else
                                        No menu items configured. Click "Add Menu Item" to create one.
                                    @endif
                                </div>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-pills .nav-link {
            border-radius: 0;
            padding: 0.75rem 1rem;
            color: #495057;
            transition: all 0.2s ease;
        }

        .nav-pills .nav-link:hover {
            background-color: #f8f9fa;
        }

        .nav-pills .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }

        .nav-pills .nav-link i {
            width: 20px;
        }
    </style>

    <!-- Outlet Modal -->
    @if($showOutletModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-building-store me-2"></i>
                            {{ $editingOutletId ? 'Edit Outlet' : 'Add Outlet' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeOutletModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Outlet Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="outletName" class="form-control @error('outletName') is-invalid @enderror" placeholder="e.g., Main Restaurant, Rooftop Bar">
                            @error('outletName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select wire:model="outletType" class="form-select">
                                <option value="restaurant">Restaurant</option>
                                <option value="bar">Bar</option>
                                <option value="cafe">Cafe</option>
                                <option value="lounge">Lounge</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Opening Time <span class="text-danger">*</span></label>
                                <input type="time" wire:model="outletOpenTime" class="form-control @error('outletOpenTime') is-invalid @enderror">
                                @error('outletOpenTime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Closing Time <span class="text-danger">*</span></label>
                                <input type="time" wire:model="outletCloseTime" class="form-control @error('outletCloseTime') is-invalid @enderror">
                                @error('outletCloseTime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="outletStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeOutletModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveOutlet">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingOutletId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Modal -->
    @if($showTableModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-armchair me-2"></i>
                            {{ $editingTableId ? 'Edit Table' : 'Add Table' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeTableModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Table Number <span class="text-danger">*</span></label>
                            <input type="text" wire:model="tableNumber" class="form-control @error('tableNumber') is-invalid @enderror" placeholder="e.g., T1, A1, 101">
                            @error('tableNumber')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Capacity (seats) <span class="text-danger">*</span></label>
                            <input type="number" wire:model="tableCapacity" class="form-control @error('tableCapacity') is-invalid @enderror" min="1">
                            @error('tableCapacity')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Section</label>
                            <input type="text" wire:model="tableSection" class="form-control" placeholder="e.g., Main Hall, Patio, VIP">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="tableStatus" class="form-select">
                                <option value="available">Available</option>
                                <option value="occupied">Occupied</option>
                                <option value="reserved">Reserved</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" wire:model="tableIsActive" class="form-check-input" id="tableIsActive">
                                <label class="form-check-label" for="tableIsActive">Table is Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeTableModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveTable">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingTableId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Category Modal -->
    @if($showCategoryModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-category me-2"></i>
                            {{ $editingCategoryId ? 'Edit Category' : 'Add Category' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeCategoryModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model="categoryName" class="form-control @error('categoryName') is-invalid @enderror" placeholder="e.g., Appetizers, Main Course, Drinks">
                            @error('categoryName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="categoryDescription" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <input type="file" wire:model="categoryImage" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" wire:model="categorySortOrder" class="form-control" min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="categoryStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCategoryModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveCategory">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingCategoryId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Menu Item Modal -->
    @if($showMenuItemModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-chef-hat me-2"></i>
                            {{ $editingMenuItemId ? 'Edit Menu Item' : 'Add Menu Item' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeMenuItemModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="menuItemName" class="form-control @error('menuItemName') is-invalid @enderror" placeholder="e.g., Grilled Chicken, Caesar Salad">
                                @error('menuItemName')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" wire:model="menuItemSku" class="form-control" placeholder="e.g., FOOD-001">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea wire:model="menuItemDescription" class="form-control" rows="2" placeholder="Describe the dish..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select wire:model="menuItemCategoryId" class="form-select @error('menuItemCategoryId') is-invalid @enderror">
                                    <option value="">Select Category</option>
                                    @foreach($availableCategories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('menuItemCategoryId')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" wire:model="menuItemPrice" class="form-control @error('menuItemPrice') is-invalid @enderror" min="0" step="0.01">
                                @error('menuItemPrice')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Cost Price</label>
                                <input type="number" wire:model="menuItemCostPrice" class="form-control" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prep Time (minutes)</label>
                                <input type="number" wire:model="menuItemPrepTime" class="form-control" min="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Printer Station</label>
                                <select wire:model="menuItemPrinterStation" class="form-select">
                                    <option value="kitchen">Kitchen</option>
                                    <option value="bar">Bar</option>
                                    <option value="grill">Grill</option>
                                    <option value="cold">Cold Section</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Item Image</label>
                            <input type="file" wire:model="menuItemImage" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Allergens</label>
                            <input type="text" wire:model="menuItemAllergens" class="form-control" placeholder="e.g., Nuts, Dairy, Gluten (comma-separated)">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="menuItemIsVegetarian" class="form-check-input" id="isVegetarian">
                                    <label class="form-check-label" for="isVegetarian">Vegetarian</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="menuItemIsVegan" class="form-check-input" id="isVegan">
                                    <label class="form-check-label" for="isVegan">Vegan</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="menuItemIsAvailable" class="form-check-input" id="isAvailable">
                                    <label class="form-check-label" for="isAvailable">Available</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select wire:model="menuItemStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeMenuItemModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="saveMenuItem">
                            <i class="ti ti-device-floppy me-1"></i>
                            {{ $editingMenuItemId ? 'Update' : 'Save' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
