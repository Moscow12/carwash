<div class="container-fluid py-4">
    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check-circle me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="ti ti-menu-2 me-2"></i>Bar Menu Items</h4>
                <div class="d-flex gap-2">
                    @if($selectedOutlet)
                        <button class="btn btn-primary btn-sm" wire:click="openCreateModal">
                            <i class="ti ti-plus me-1"></i>Add New Item
                        </button>
                    @endif
                    <select wire:model.live="selectedBusiness" class="form-select form-select-sm" style="width: auto;">
                        <option value="">Select Business</option>
                        @foreach($businesses as $business)
                            <option value="{{ $business->id }}">{{ $business->name }}</option>
                        @endforeach
                    </select>
                    @if($outlets->count() > 0)
                        <select wire:model.live="selectedOutlet" class="form-select form-select-sm" style="width: auto;">
                            <option value="">Select Outlet</option>
                            @foreach($outlets as $outlet)
                                <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($selectedOutlet)
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" wire:model.live="search" class="form-control" placeholder="Search menu items...">
            </div>
            <div class="col-md-4">
                <select wire:model.live="selectedCategory" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="ti ti-list me-2"></i>Menu Items</h5>
                        <div class="text-muted">
                            <small>Total: <strong>{{ $menuItems->total() }}</strong> items</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock Item</th>
                                        <th class="text-center">Current Stock</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menuItems as $index => $item)
                                        <tr>
                                            <td class="text-center text-muted">
                                                <strong>{{ $menuItems->firstItem() + $index }}</strong>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <div>
                                                        <strong class="d-block">{{ $item->name }}</strong>
                                                        @if($item->description)
                                                            <small class="text-muted d-block">{{ Str::limit($item->description, 50) }}</small>
                                                        @endif
                                                        <div class="mt-1">
                                                            @if($item->is_vegetarian)
                                                                <span class="badge bg-success-subtle text-success me-1" title="Vegetarian">
                                                                    <i class="ti ti-leaf"></i> Veg
                                                                </span>
                                                            @endif
                                                            @if($item->is_vegan)
                                                                <span class="badge bg-info-subtle text-info me-1" title="Vegan">
                                                                    <i class="ti ti-plant"></i> Vegan
                                                                </span>
                                                            @endif
                                                            @if($item->prep_time_mins)
                                                                <span class="badge bg-secondary-subtle text-secondary" title="Preparation Time">
                                                                    <i class="ti ti-clock"></i> {{ $item->prep_time_mins }}min
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary">
                                                    {{ $item->category?->name ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">TSh {{ number_format($item->price, 0) }}</strong>
                                                @if($item->cost_price)
                                                    <br><small class="text-muted">Cost: TSh {{ number_format($item->cost_price, 0) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($item->item)
                                                    <div class="d-flex align-items-center">
                                                        <i class="ti ti-package text-primary me-1"></i>
                                                        <span>{{ $item->item->name }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-muted fst-italic">
                                                        <i class="ti ti-unlink me-1"></i>Not linked
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($item->item)
                                                    @php
                                                        $qty = $this->getCurrentStock($item->item_id);
                                                        $reorderLevel = $item->item->reorder_level ?? 10;
                                                        $stockStatus = $qty <= 0 ? 'out' : ($qty <= $reorderLevel ? 'low' : 'ok');

                                                        if ($stockStatus === 'out') {
                                                            $badgeClass = 'bg-danger';
                                                            $iconClass = 'ti-alert-circle';
                                                        } elseif ($stockStatus === 'low') {
                                                            $badgeClass = 'bg-warning';
                                                            $iconClass = 'ti-alert-triangle';
                                                        } else {
                                                            $badgeClass = 'bg-success';
                                                            $iconClass = 'ti-check';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}"
                                                          title="Reorder Level: {{ $reorderLevel }} {{ $item->item->unit?->name ?? 'pcs' }}"
                                                          data-bs-toggle="tooltip">
                                                        <i class="ti {{ $iconClass }}"></i>
                                                        {{ number_format($qty, 2) }} {{ $item->item->unit?->name ?? 'pcs' }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="ti ti-minus"></i>
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center align-items-center">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           wire:click="toggleStatus('{{ $item->id }}')"
                                                           {{ $item->status === 'active' ? 'checked' : '' }}
                                                           style="cursor: pointer;">
                                                </div>
                                                <span class="badge bg-{{ $item->status === 'active' ? 'success' : 'secondary' }} mt-1">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ti ti-dots-vertical"></i> Actions
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item" href="#" wire:click.prevent="openEditModal('{{ $item->id }}')">
                                                                <i class="ti ti-pencil me-2"></i>Edit
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item" href="#" wire:click.prevent="openLinkStockModal('{{ $item->id }}')">
                                                                <i class="ti ti-{{ $item->item_id ? 'link' : 'unlink' }} me-2"></i>{{ $item->item_id ? 'Change Stock Link' : 'Link to Stock' }}
                                                            </a>
                                                        </li>
                                                        @if($item->item_id)
                                                            <li>
                                                                <a class="dropdown-item" href="#" wire:click.prevent="openUpdateStockModal('{{ $item->id }}')">
                                                                    <i class="ti ti-package me-2"></i>Update Stock
                                                                </a>
                                                            </li>
                                                        @endif
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="#" wire:click.prevent="openDeleteModal('{{ $item->id }}')">
                                                                <i class="ti ti-trash me-2"></i>Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="ti ti-inbox fs-1 d-block mb-3 opacity-50"></i>
                                                    <h5>No menu items found</h5>
                                                    <p class="mb-0">Try adjusting your search or filters</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($menuItems->hasPages())
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Showing {{ $menuItems->firstItem() }} to {{ $menuItems->lastItem() }} of {{ $menuItems->total() }} items
                                </div>
                                <div>
                                    {{ $menuItems->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Please select a business and outlet to view menu items.
                </div>
            </div>
        </div>
    @endif

    <!-- Link Stock Modal -->
    @if($showLinkStockModal && $linkingItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="ti ti-link me-2"></i>Link to Stock Item</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeLinkStockModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>Menu Item:</h6>
                            <p class="mb-0"><strong>{{ $linkingItem->name }}</strong></p>
                        </div>

                        @if($linkingItem->item_id)
                            <div class="alert alert-warning">
                                <i class="ti ti-alert-circle me-2"></i>
                                Currently linked to: <strong>{{ $linkingItem->item?->name }}</strong>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                This menu item is not linked to any stock item yet.
                            </div>
                        @endif

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Select Stock Item</label>
                                <button type="button" class="btn btn-sm btn-success" wire:click="openCreateStockModal">
                                    <i class="ti ti-plus"></i> Add Stock Item
                                </button>
                            </div>
                            <select class="form-select @error('linkStockItemId') is-invalid @enderror"
                                    wire:model="linkStockItemId">
                                <option value="">No Stock Link (Remove Link)</option>
                                @foreach($stockItems as $stockItem)
                                    <option value="{{ $stockItem->id }}">
                                        {{ $stockItem->name }}
                                        (Current: {{ number_format($stockItem->current_stock ?? 0, 2) }} {{ $stockItem->unit?->name ?? 'pcs' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('linkStockItemId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted d-block mt-1">
                                <i class="ti ti-info-circle"></i>
                                Linking enables automatic stock deduction when this item is sold
                            </small>
                        </div>

                        @if($linkStockItemId)
                            @php
                                $selectedStock = $stockItems->firstWhere('id', $linkStockItemId);
                            @endphp
                            @if($selectedStock)
                                <div class="card border-success">
                                    <div class="card-body">
                                        <h6 class="card-title text-success">
                                            <i class="ti ti-check-circle"></i> Selected Stock Item
                                        </h6>
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <td class="text-muted">Name:</td>
                                                <td><strong>{{ $selectedStock->name }}</strong></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Current Stock:</td>
                                                <td>
                                                    <span class="badge bg-{{ ($selectedStock->current_stock ?? 0) > 0 ? 'success' : 'danger' }}">
                                                        {{ number_format($selectedStock->current_stock ?? 0, 2) }} {{ $selectedStock->unit?->name ?? 'pcs' }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Cost Price:</td>
                                                <td>TSh {{ number_format($selectedStock->cost_price ?? 0, 0) }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeLinkStockModal">Cancel</button>
                        <button type="button" class="btn btn-info" wire:click="updateStockLink">
                            <i class="ti ti-check me-1"></i>{{ $linkStockItemId ? 'Update Link' : 'Remove Link' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Modal -->
    @if($showCreateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="ti ti-plus me-2"></i>Add New Menu Item</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCreateModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="createMenuItem">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('createName') is-invalid @enderror"
                                           wire:model="createName" placeholder="Enter menu item name">
                                    @error('createName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('createCategoryId') is-invalid @enderror"
                                            wire:model="createCategoryId">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('createCategoryId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="createDescription" rows="2"
                                              placeholder="Enter description"></textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (TSh) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('createPrice') is-invalid @enderror"
                                           wire:model="createPrice" step="0.01" min="0" placeholder="0.00">
                                    @error('createPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cost Price (TSh)</label>
                                    <input type="number" class="form-control @error('createCostPrice') is-invalid @enderror"
                                           wire:model="createCostPrice" step="0.01" min="0" placeholder="0.00">
                                    @error('createCostPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Prep Time (mins)</label>
                                    <input type="number" class="form-control @error('createPrepTimeMins') is-invalid @enderror"
                                           wire:model="createPrepTimeMins" min="1" placeholder="15">
                                    @error('createPrepTimeMins') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Stock Item Link</label>
                                    <select class="form-select @error('createItemId') is-invalid @enderror"
                                            wire:model="createItemId">
                                        <option value="">No Stock Link</option>
                                        @foreach($stockItems as $stockItem)
                                            <option value="{{ $stockItem->id }}">
                                                {{ $stockItem->name }} ({{ $stockItem->qty ?? 0 }} {{ $stockItem->unit?->name ?? 'pcs' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Link to inventory item for automatic stock deduction</small>
                                    @error('createItemId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="ti ti-info-circle me-2"></i>
                                        <strong>Tip:</strong> New menu items are created as "Active" by default. You can deactivate them later using the status toggle.
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="createIsAvailable" id="createIsAvailable">
                                                <label class="form-check-label" for="createIsAvailable">
                                                    Available for Sale
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="createIsVegetarian" id="createIsVegetarian">
                                                <label class="form-check-label" for="createIsVegetarian">
                                                    <i class="ti ti-leaf text-success"></i> Vegetarian
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="createIsVegan" id="createIsVegan">
                                                <label class="form-check-label" for="createIsVegan">
                                                    <i class="ti ti-plant text-info"></i> Vegan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Photo</label>
                                    <input type="file" wire:model="createImage" class="form-control @error('createImage') is-invalid @enderror" accept="image/*">
                                    @error('createImage') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="createImage" class="form-text">Uploading…</div>
                                    @if($createImage)
                                        <img src="{{ $createImage->temporaryUrl() }}" class="rounded border mt-2" style="width:80px;height:80px;object-fit:cover;">
                                    @endif
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch p-2 rounded border bg-light" style="padding-left: 3rem !important;">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="createMenuPublish" style="cursor:pointer;width:2.5em;height:1.4em;"
                                               wire:model="createIsPublished">
                                        <label class="form-check-label ms-2 fw-medium" for="createMenuPublish" style="cursor:pointer;">
                                            <i class="ti ti-world me-1 text-info"></i>Publish to public marketplace
                                            <small class="d-block text-muted fw-normal">Visible to everyone on the public site (add a photo for best results).</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCreateModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="createMenuItem">
                            <i class="ti ti-check me-1"></i>Create Menu Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Modal -->
    @if($showEditModal && $editingItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-pencil me-2"></i>Edit Menu Item</h5>
                        <button type="button" class="btn-close" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="updateMenuItem">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('editName') is-invalid @enderror"
                                           wire:model="editName" placeholder="Enter menu item name">
                                    @error('editName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select class="form-select @error('editCategoryId') is-invalid @enderror"
                                            wire:model="editCategoryId">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('editCategoryId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="editDescription" rows="2"
                                              placeholder="Enter description"></textarea>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Price (TSh) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('editPrice') is-invalid @enderror"
                                           wire:model="editPrice" step="0.01" min="0" placeholder="0.00">
                                    @error('editPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cost Price (TSh)</label>
                                    <input type="number" class="form-control @error('editCostPrice') is-invalid @enderror"
                                           wire:model="editCostPrice" step="0.01" min="0" placeholder="0.00">
                                    @error('editCostPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Prep Time (mins)</label>
                                    <input type="number" class="form-control @error('editPrepTimeMins') is-invalid @enderror"
                                           wire:model="editPrepTimeMins" min="1" placeholder="15">
                                    @error('editPrepTimeMins') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Stock Item Link</label>
                                    <select class="form-select @error('editItemId') is-invalid @enderror"
                                            wire:model="editItemId">
                                        <option value="">No Stock Link</option>
                                        @foreach($stockItems as $stockItem)
                                            <option value="{{ $stockItem->id }}">
                                                {{ $stockItem->name }} ({{ $stockItem->qty ?? 0 }} {{ $stockItem->unit?->name ?? 'pcs' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Link to inventory item for automatic stock deduction</small>
                                    @error('editItemId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="editIsAvailable" id="editIsAvailable">
                                                <label class="form-check-label" for="editIsAvailable">
                                                    Available for Sale
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="editIsVegetarian" id="editIsVegetarian">
                                                <label class="form-check-label" for="editIsVegetarian">
                                                    <i class="ti ti-leaf text-success"></i> Vegetarian
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       wire:model="editIsVegan" id="editIsVegan">
                                                <label class="form-check-label" for="editIsVegan">
                                                    <i class="ti ti-plant text-info"></i> Vegan
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Photo</label>
                                    <input type="file" wire:model="editImage" class="form-control @error('editImage') is-invalid @enderror" accept="image/*">
                                    @error('editImage') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                    <div wire:loading wire:target="editImage" class="form-text">Uploading…</div>
                                    <div class="d-flex gap-2 mt-2">
                                        @if($editImage)
                                            <img src="{{ $editImage->temporaryUrl() }}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">
                                        @elseif($editExistingImage)
                                            <img src="{{ asset('storage/' . $editExistingImage) }}" class="rounded border" style="width:80px;height:80px;object-fit:cover;">
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-check form-switch p-2 rounded border bg-light" style="padding-left: 3rem !important;">
                                        <input class="form-check-input" type="checkbox" role="switch"
                                               id="editMenuPublish" style="cursor:pointer;width:2.5em;height:1.4em;"
                                               wire:model="editIsPublished">
                                        <label class="form-check-label ms-2 fw-medium" for="editMenuPublish" style="cursor:pointer;">
                                            <i class="ti ti-world me-1 text-info"></i>Publish to public marketplace
                                            <small class="d-block text-muted fw-normal">Visible to everyone on the public site (add a photo for best results).</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="updateMenuItem">
                            <i class="ti ti-device-floppy me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Create Stock Item Modal -->
    @if($showCreateStockModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.7); z-index: 1060;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="ti ti-plus me-2"></i>Add New Stock Item</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeCreateStockModal"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('newStockName') is-invalid @enderror"
                                       wire:model="newStockName" placeholder="e.g., Coca Cola, Tusker Beer">
                                @error('newStockName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Initial Quantity <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('newStockQty') is-invalid @enderror"
                                           wire:model="newStockQty" step="0.01" min="0" placeholder="0">
                                    @error('newStockQty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Unit</label>
                                    <select class="form-select @error('newStockUnitId') is-invalid @enderror"
                                            wire:model="newStockUnitId">
                                        <option value="">Select Unit</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('newStockUnitId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cost Price (TSh)</label>
                                    <input type="number" class="form-control @error('newStockCostPrice') is-invalid @enderror"
                                           wire:model="newStockCostPrice" step="0.01" min="0" placeholder="0.00">
                                    @error('newStockCostPrice') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Reorder Level</label>
                                    <input type="number" class="form-control @error('newStockReorderLevel') is-invalid @enderror"
                                           wire:model="newStockReorderLevel" min="0" placeholder="10">
                                    @error('newStockReorderLevel') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    <small class="text-muted">Alert when stock falls below this level</small>
                                </div>
                            </div>

                            <div class="alert alert-info mb-0">
                                <i class="ti ti-info-circle me-2"></i>
                                <small>The new stock item will be automatically selected for linking after creation.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCreateStockModal">Cancel</button>
                        <button type="button" class="btn btn-success" wire:click="createStockItem">
                            <i class="ti ti-check me-1"></i>Create Stock Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Update Stock Modal -->
    @if($showUpdateStockModal && $updatingStockItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="ti ti-package me-2"></i>Update Stock</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeUpdateStockModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info mb-3">
                            <div class="d-flex align-items-center">
                                <i class="ti ti-info-circle fs-4 me-2"></i>
                                <div>
                                    <strong>{{ $updatingStockItem->name }}</strong><br>
                                    <small>Stock Item: {{ $updatingStockItem->item->name ?? '-' }}</small>
                                </div>
                            </div>
                        </div>

                        @if($updatingStockItem->item)
                            <div class="card bg-light mb-3">
                                <div class="card-body py-2">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <small class="text-muted d-block">Current Stock</small>
                                            <h4 class="mb-0">{{ $updatingStockItem->item->qty ?? 0 }}</h4>
                                            <small class="text-muted">{{ $updatingStockItem->item->unit?->name ?? 'pcs' }}</small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Reorder Level</small>
                                            <h4 class="mb-0 {{ ($updatingStockItem->item->qty ?? 0) <= ($updatingStockItem->item->reorder_level ?? 10) ? 'text-warning' : 'text-success' }}">
                                                {{ $updatingStockItem->item->reorder_level ?? 10 }}
                                            </h4>
                                            <small class="text-muted">{{ $updatingStockItem->item->unit?->name ?? 'pcs' }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form>
                            <div class="mb-3">
                                <label class="form-label">Action <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" id="actionAdd" wire:model="updateStockAction" value="add" checked>
                                    <label class="btn btn-outline-success" for="actionAdd">
                                        <i class="ti ti-plus me-1"></i>Add Stock
                                    </label>

                                    <input type="radio" class="btn-check" id="actionSubtract" wire:model="updateStockAction" value="subtract">
                                    <label class="btn btn-outline-danger" for="actionSubtract">
                                        <i class="ti ti-minus me-1"></i>Subtract Stock
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('updateStockQty') is-invalid @enderror"
                                       wire:model="updateStockQty" step="0.01" min="0.01" placeholder="Enter quantity">
                                @error('updateStockQty') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" wire:model="updateStockNotes" rows="2"
                                          placeholder="Optional notes for this stock adjustment"></textarea>
                            </div>

                            @if($updateStockQty > 0 && $updatingStockItem->item)
                                <div class="alert alert-{{ $updateStockAction === 'add' ? 'success' : 'warning' }} mb-0">
                                    <i class="ti ti-calculator me-2"></i>
                                    <strong>New Stock Level:</strong>
                                    @if($updateStockAction === 'add')
                                        {{ ($updatingStockItem->item->qty ?? 0) + $updateStockQty }}
                                    @else
                                        {{ max(0, ($updatingStockItem->item->qty ?? 0) - $updateStockQty) }}
                                    @endif
                                    {{ $updatingStockItem->item->unit?->name ?? 'pcs' }}
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeUpdateStockModal">Cancel</button>
                        <button type="button" class="btn btn-primary" wire:click="updateStock">
                            <i class="ti ti-check me-1"></i>Update Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal && $deletingItem)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="ti ti-alert-triangle me-2"></i>Confirm Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body">
                        @if($transactionCount > 0)
                            <div class="alert alert-warning">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>Cannot Delete!</strong> This menu item has been used in <strong>{{ $transactionCount }}</strong> transaction(s).
                            </div>
                            <p>Menu items with transaction history cannot be deleted to maintain data integrity.</p>
                            <p class="mb-0"><strong>Suggestion:</strong> You can deactivate this item instead using the status toggle.</p>
                        @else
                            <p>Are you sure you want to delete <strong>{{ $deletingItem->name }}</strong>?</p>
                            <p class="text-danger mb-0"><i class="ti ti-alert-circle me-1"></i>This action cannot be undone.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">Cancel</button>
                        @if($transactionCount === 0)
                            <button type="button" class="btn btn-danger" wire:click="deleteMenuItem">
                                <i class="ti ti-trash me-1"></i>Delete
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
