<div class="categories-page">
    <style>
        .categories-page .stat-pill { cursor: pointer; border: 2px solid transparent; transition: border-color .12s, transform .12s; }
        .categories-page .stat-pill:hover { transform: translateY(-1px); }
        .categories-page .stat-pill.active { border-color: var(--bs-primary); }
        .categories-page .category-icon { width: 42px; height: 42px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .categories-page .table thead th { white-space: nowrap; font-size: .8rem; text-transform: uppercase; letter-spacing: .02em; color: var(--bs-secondary-color); }
        .categories-page .row-actions .btn { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
        .categories-page .form-switch .form-check-input { cursor: pointer; width: 2.4em; height: 1.3em; }
    </style>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ti ti-check me-2"></i>{{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">Categories</h3>
            <p class="text-muted mb-0">Group your products and services so they're easy to find on the POS and in reports</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
                <i class="ti ti-list me-1"></i> Items
            </a>
            <button wire:click="openModal" class="btn btn-primary" @disabled(!$hasBusinesses)>
                <i class="ti ti-plus me-1"></i> Add Category
            </button>
        </div>
    </div>

    @if(!$hasBusinesses)
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i>
            Register a business first, then you can add categories.
            <a href="{{ route('owner.mybusiness') }}" class="alert-link">Register business</a>
        </div>
    @endif

    {{-- Stats double as status filters --}}
    @php
        $statPills = [
            ['filter' => '', 'label' => 'All', 'value' => $stats['total'], 'color' => 'primary', 'icon' => 'ti-category'],
            ['filter' => 'active', 'label' => 'Active', 'value' => $stats['active'], 'color' => 'success', 'icon' => 'ti-circle-check'],
            ['filter' => 'inactive', 'label' => 'Inactive', 'value' => $stats['inactive'], 'color' => 'secondary', 'icon' => 'ti-circle-off'],
        ];
    @endphp
    <div class="row g-3 mb-4">
        @foreach($statPills as $pill)
            <div class="col-4 col-lg-3" wire:key="stat-{{ $pill['label'] }}">
                <div class="card shadow-sm stat-pill h-100 {{ $statusFilter === $pill['filter'] ? 'active' : '' }}"
                     role="button" tabindex="0"
                     wire:click="$set('statusFilter', '{{ $pill['filter'] }}')"
                     wire:keydown.enter="$set('statusFilter', '{{ $pill['filter'] }}')"
                     aria-pressed="{{ $statusFilter === $pill['filter'] ? 'true' : 'false' }}">
                    <div class="card-body d-flex align-items-center gap-3 py-3">
                        <span class="category-icon bg-{{ $pill['color'] }}-subtle text-{{ $pill['color'] }} d-none d-sm-inline-flex"><i class="ti {{ $pill['icon'] }} fs-5"></i></span>
                        <div>
                            <div class="small text-muted">{{ $pill['label'] }}</div>
                            <div class="h4 mb-0">{{ number_format($pill['value']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-12 col-lg-3">
            <div class="card shadow-sm h-100 bg-body-tertiary border-0">
                <div class="card-body py-3 small">
                    <i class="ti ti-bulb text-warning me-1"></i>
                    @if($stats['empty'] > 0)
                        <strong>{{ $stats['empty'] }}</strong> {{ Str::plural('category', $stats['empty']) }} {{ $stats['empty'] === 1 ? 'has' : 'have' }} no items yet.
                    @else
                        Inactive categories are hidden from the POS but keep their items.
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="ti ti-search"></i></span>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search by name or description..." aria-label="Search categories">
                    </div>
                </div>
                @if($ownerBusinesses->count() > 1)
                    <div class="col-md-4">
                        <select wire:model.live="filterBusiness" class="form-select" aria-label="Business">
                            <option value="">All businesses</option>
                            @foreach($ownerBusinesses as $business)
                                <option value="{{ $business->id }}" wire:key="business-filter-{{ $business->id }}">{{ $business->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col text-md-end">
                    <span class="text-muted small">{{ $categories->total() }} {{ Str::plural('category', $categories->total()) }}</span>
                    @if($search !== '')
                        <button type="button" wire:click="$set('search', '')" class="btn btn-sm btn-link text-decoration-none">Clear search</button>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="bg-light">
                            <th class="border-0 ps-4" style="width: 60px;">S/N</th>
                            @include('livewire.owner.partials.sort-header', ['field' => 'name', 'label' => 'Category', 'class' => 'border-0'])
                            @if($ownerBusinesses->count() > 1)
                                <th class="border-0 d-none d-md-table-cell">Business</th>
                            @endif
                            @include('livewire.owner.partials.sort-header', ['field' => 'items_count', 'label' => 'Items', 'class' => 'border-0 text-center'])
                            <th class="border-0 text-center">Active</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody wire:loading.class="opacity-50" wire:target="search,filterBusiness,statusFilter,sortTable,gotoPage,nextPage,previousPage">
                        @forelse($categories as $category)
                        <tr wire:key="category-{{ $category->id }}" class="{{ $category->status === 'inactive' ? 'opacity-75' : '' }}">
                            <td class="ps-4 text-muted">{{ $categories->firstItem() + $loop->index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="category-icon bg-primary-subtle text-primary"><i class="ti ti-category fs-5"></i></span>
                                    <div class="min-w-0">
                                        <button type="button" wire:click="editCategory('{{ $category->id }}')" class="btn btn-link p-0 fw-semibold text-body text-decoration-none text-start">{{ $category->name }}</button>
                                        <div class="small text-muted text-truncate" style="max-width: 360px;">
                                            {{ $category->description ? Str::limit($category->description, 70) : 'No description' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            @if($ownerBusinesses->count() > 1)
                                <td class="d-none d-md-table-cell text-muted">{{ $category->business->name ?? '-' }}</td>
                            @endif
                            <td class="text-center">
                                <span class="badge rounded-pill {{ $category->items_count > 0 ? 'bg-primary-subtle text-primary-emphasis' : 'bg-body-tertiary text-muted' }} px-3">
                                    {{ number_format($category->items_count) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-flex justify-content-center m-0">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           @checked($category->status === 'active')
                                           wire:click="toggleStatus('{{ $category->id }}')"
                                           wire:loading.attr="disabled" wire:target="toggleStatus('{{ $category->id }}')"
                                           aria-label="{{ $category->status === 'active' ? 'Deactivate' : 'Activate' }} {{ $category->name }}"
                                           title="{{ $category->status === 'active' ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="row-actions d-inline-flex gap-1">
                                    <button wire:click="editCategory('{{ $category->id }}')" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit {{ $category->name }}">
                                        <i class="ti ti-pencil"></i>
                                    </button>
                                    <button wire:click="confirmDelete('{{ $category->id }}')" class="btn btn-sm btn-outline-danger"
                                            title="{{ $category->items_count > 0 ? 'Has items — move or delete them first' : 'Delete' }}"
                                            aria-label="Delete {{ $category->name }}">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $ownerBusinesses->count() > 1 ? 6 : 5 }}" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="ti ti-category display-4 d-block mb-3 opacity-50"></i>
                                    @if($search !== '' || $statusFilter !== '')
                                        <h5>No categories match</h5>
                                        <button type="button" wire:click="$set('statusFilter', '')" class="btn btn-outline-primary btn-sm me-1" @if($statusFilter === '') hidden @endif>Show all statuses</button>
                                        <button type="button" wire:click="$set('search', '')" class="btn btn-outline-primary btn-sm" @if($search === '') hidden @endif>Clear search</button>
                                    @else
                                        <h5>No categories yet</h5>
                                        <p class="mb-3">Create categories like "Drinks" or "Full Wash" to organise your items.</p>
                                        <button wire:click="openModal" class="btn btn-primary btn-sm" @disabled(!$hasBusinesses)>
                                            <i class="ti ti-plus me-1"></i> Add your first category
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($categories->hasPages())
        <div class="card-footer bg-transparent border-top">
            {{ $categories->links() }}
        </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeModal">
        <div class="modal-dialog modal-dialog-centered modal-fullscreen-sm-down" style="max-width: 520px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'pencil' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Category' : 'New Category' }}
                    </h5>
                    <button type="button" wire:click="closeModal" class="btn-close"></button>
                </div>

                <form wire:submit="save">
                    <div class="modal-body">
                        <div class="row g-3">
                            @if(!$hasBusinesses)
                                <div class="col-12">
                                    <div class="alert alert-warning mb-0">
                                        <i class="ti ti-alert-triangle me-2"></i>
                                        You need to register a business first before adding categories.
                                        <a href="{{ route('owner.mybusiness') }}" class="alert-link">Register Business</a>
                                    </div>
                                </div>
                            @elseif($ownerBusinesses->count() > 1)
                                <div class="col-12">
                                    <label class="form-label">Business <span class="text-danger">*</span></label>
                                    <select wire:model="business_id" class="form-select @error('business_id') is-invalid @enderror">
                                        <option value="">Select business</option>
                                        @foreach($ownerBusinesses as $business)
                                            <option value="{{ $business->id }}" wire:key="business-form-{{ $business->id }}">{{ $business->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif
                            @error('business_id')
                                @if($ownerBusinesses->count() <= 1)
                                    <div class="col-12">
                                        <div class="alert alert-danger mb-0">{{ $message }}</div>
                                    </div>
                                @endif
                            @enderror

                            <div class="col-12">
                                <label class="form-label">Category name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" x-data x-init="$nextTick(() => $el.focus())" x-on:category-form-reset.window="$el.focus()"
                                       class="form-control form-control-lg @error('name') is-invalid @enderror" placeholder="e.g. Drinks, Premium Wash" maxlength="255" autocomplete="off">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12" x-data="{ length: {{ mb_strlen($description ?? '') }} }">
                                <label class="form-label d-flex justify-content-between">
                                    <span>Description <span class="text-muted small">(optional)</span></span>
                                    <small class="text-muted" x-text="length + '/500'"></small>
                                </label>
                                <textarea wire:model="description" x-on:input="length = $event.target.value.length"
                                          class="form-control @error('description') is-invalid @enderror" rows="2" maxlength="500" placeholder="What goes in this category?"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch p-3 ps-5 rounded-3 bg-body-tertiary m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="categoryActive"
                                           x-data x-bind:checked="$wire.status === 'active'"
                                           x-on:change="$wire.status = $event.target.checked ? 'active' : 'inactive'">
                                    <label class="form-check-label fw-medium" for="categoryActive">
                                        Active
                                        <small class="d-block text-muted fw-normal">Inactive categories are hidden from the POS.</small>
                                    </label>
                                </div>
                                @error('status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 d-flex flex-wrap gap-2">
                        @unless($editMode)
                            <div class="form-check me-auto">
                                <input class="form-check-input" type="checkbox" id="addAnother" wire:model="addAnother">
                                <label class="form-check-label small" for="addAnother">Add another after saving</label>
                            </div>
                        @endunless
                        <button type="button" wire:click="closeModal" class="btn btn-light">Cancel</button>
                        <button type="submit" class="btn btn-primary" @disabled(!$hasBusinesses)>
                            <span wire:loading.remove wire:target="save">
                                <i class="ti ti-check me-1"></i> {{ $editMode ? 'Save changes' : 'Create category' }}
                            </span>
                            <span wire:loading wire:target="save">
                                <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if($showDeleteModal && $deletingCategory)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);" wire:keydown.escape="closeDeleteModal">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-body text-center py-4">
                    @if($deletingCategory['items_count'] > 0)
                        <div class="bg-warning-subtle text-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="ti ti-alert-triangle fs-2"></i>
                        </div>
                        <h5 class="mb-2">Can't delete "{{ $deletingCategory['name'] }}" yet</h5>
                        <p class="text-muted mb-0">
                            It still has <strong>{{ $deletingCategory['items_count'] }} {{ Str::plural('item', $deletingCategory['items_count']) }}</strong>.
                            Deleting it would delete those items too. Move them to another category first, or switch the category off instead.
                        </p>
                    @else
                        <div class="bg-danger-subtle text-danger rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            <i class="ti ti-trash fs-2"></i>
                        </div>
                        <h5 class="mb-2">Delete "{{ $deletingCategory['name'] }}"?</h5>
                        <p class="text-muted mb-0">This category has no items. This can't be undone.</p>
                    @endif
                </div>
                <div class="modal-footer border-0 justify-content-center gap-2">
                    @if($deletingCategory['items_count'] > 0)
                        <button type="button" wire:click="closeDeleteModal" class="btn btn-light">Close</button>
                        <a href="{{ route('owner.list-items') }}" class="btn btn-primary">
                            <i class="ti ti-list me-1"></i> Go to items
                        </a>
                    @else
                        <button type="button" wire:click="closeDeleteModal" class="btn btn-light">Cancel</button>
                        <button type="button" wire:click="deleteCategory" class="btn btn-danger">
                            <span wire:loading.remove wire:target="deleteCategory">
                                <i class="ti ti-trash me-1"></i> Delete
                            </span>
                            <span wire:loading wire:target="deleteCategory">
                                <span class="spinner-border spinner-border-sm me-1"></span> Deleting...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
