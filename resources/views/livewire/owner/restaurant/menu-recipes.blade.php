<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Menu Recipes</h4>
            <p class="text-muted mb-0">Define recipes for menu items to enable automatic stock deduction</p>
        </div>
        @if($selectedMenuItem)
            <button wire:click="openModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Ingredient
            </button>
        @endif
    </div>

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

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                @if($businesses->count() > 1)
                    <div class="col-md-6">
                        <label class="form-label">Business</label>
                        <select wire:model.live="selectedBusiness" class="form-select">
                            @foreach($businesses as $business)
                                <option value="{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-6">
                    <label class="form-label">Outlet</label>
                    <select wire:model.live="selectedOutlet" class="form-select">
                        <option value="">Select Outlet</option>
                        @foreach($outlets as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if($selectedOutlet)
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3"><i class="ti ti-chef-hat"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Items with Recipes</h6>
                                <h3 class="mb-0">{{ $stats['items_with_recipes'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3"><i class="ti ti-list"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Total Ingredients</h6>
                                <h3 class="mb-0">{{ $stats['total_ingredients'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3"><i class="ti ti-calculator"></i></div>
                            <div>
                                <h6 class="text-muted mb-1">Avg Ingredients</h6>
                                <h3 class="mb-0">{{ number_format($stats['avg_cost'], 1) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left: Menu Items List -->
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="ti ti-list me-2"></i>Menu Items</h6>
                    </div>
                    <div class="card-body">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control mb-3" placeholder="Search menu items...">
                        <div class="list-group">
                            @forelse($menuItems as $item)
                                <a href="#" wire:click.prevent="selectMenuItem('{{ $item->id }}')"
                                   class="list-group-item list-group-item-action {{ $selectedMenuItem == $item->id ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $item->name }}</h6>
                                            @if($item->category)
                                                <small class="text-muted">{{ $item->category->name }}</small>
                                            @endif
                                        </div>
                                        <span class="badge bg-primary">{{ number_format($item->price, 2) }}</span>
                                    </div>
                                </a>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="ti ti-list-off fs-1"></i>
                                    <p class="mt-2">No menu items found</p>
                                </div>
                            @endforelse
                        </div>
                        <div class="mt-3">{{ $menuItems->links() }}</div>
                    </div>
                </div>
            </div>

            <!-- Right: Recipe Ingredients -->
            <div class="col-md-7">
                @if($selectedMenuItem)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0"><i class="ti ti-chef-hat me-2"></i>Recipe Ingredients</h6>
                                @if($recipes->count() > 0)
                                    <span class="badge bg-success">Total Cost: {{ number_format($totalCost, 2) }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if($recipes->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Ingredient</th>
                                                <th>Quantity</th>
                                                <th>Unit</th>
                                                <th>Cost</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($recipes as $recipe)
                                                <tr>
                                                    <td>
                                                        <div>{{ $recipe->item->name }}</div>
                                                        @if($recipe->is_optional)
                                                            <span class="badge bg-secondary">Optional</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $recipe->quantity }}</td>
                                                    <td>{{ $recipe->unit->name }}</td>
                                                    <td>{{ number_format($recipe->item->cost_price * $recipe->quantity, 2) }}</td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button wire:click="editRecipe('{{ $recipe->id }}')" class="btn btn-warning">
                                                                <i class="ti ti-edit"></i>
                                                            </button>
                                                            <button wire:click="deleteRecipe('{{ $recipe->id }}')" class="btn btn-danger"
                                                                    onclick="return confirm('Delete this ingredient?')">
                                                                <i class="ti ti-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="ti ti-chef-hat-off fs-1"></i>
                                    <p class="mt-2">No ingredients added yet</p>
                                    <button wire:click="openModal" class="btn btn-primary btn-sm mt-2">
                                        <i class="ti ti-plus me-1"></i>Add First Ingredient
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="ti ti-click fs-1 text-muted"></i>
                            <h5 class="mt-3">Select Menu Item</h5>
                            <p class="text-muted">Select a menu item from the left to view or add recipe ingredients</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-building fs-1 text-muted"></i>
                <h5 class="mt-3">Select Outlet</h5>
                <p class="text-muted">Please select an outlet to manage menu recipes</p>
            </div>
        </div>
    @endif

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ti ti-plus me-2"></i>{{ $editMode ? 'Edit' : 'Add' }} Ingredient</h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="saveRecipe">
                            <div class="mb-3">
                                <label class="form-label">Stock Item <span class="text-danger">*</span></label>
                                <select wire:model="item_id" class="form-select @error('item_id') is-invalid @enderror">
                                    <option value="">Select Stock Item</option>
                                    @foreach($stockItems as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} (Stock: {{ $item->quantity }})</option>
                                    @endforeach
                                </select>
                                @error('item_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror">
                                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Unit <span class="text-danger">*</span></label>
                                        <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                            <option value="">Select Unit</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input type="checkbox" wire:model="is_optional" class="form-check-input" id="isOptional">
                                    <label class="form-check-label" for="isOptional">Optional Ingredient</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea wire:model="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-check me-1"></i>{{ $editMode ? 'Update' : 'Add' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
