<div>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Stocktaking</h3>
            <p class="text-muted mb-0">Manage inventory and stock counts for your products</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="downloadTemplate" class="btn btn-outline-primary">
                <i class="ti ti-download me-1"></i> Template
            </button>
            <button wire:click="openImportModal" class="btn btn-outline-success">
                <i class="ti ti-upload me-1"></i> Import
            </button>
            <button wire:click="openAddModal" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Add Stock
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-primary-subtle text-primary rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-clipboard-list fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Records</div>
                            <div class="h4 mb-0">{{ number_format($stats['total']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-success-subtle text-success rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-check fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Received</div>
                            <div class="h4 mb-0">{{ number_format($stats['received']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-warning-subtle text-warning rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-clock fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Pending</div>
                            <div class="h4 mb-0">{{ number_format($stats['pending']) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg bg-info-subtle text-info rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                            <i class="ti ti-currency-dollar fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Total Value</div>
                            <div class="h4 mb-0">TZS {{ number_format($stats['total_value'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0">
                            <i class="ti ti-search"></i>
                        </span>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control border-start-0 ps-0" placeholder="Search by item name...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="selectedCarwash" class="form-select">
                        <option value="">All Carwashes</option>
                        @foreach($ownerCarwashes as $carwash)
                            <option value="{{ $carwash['id'] }}">{{ $carwash['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="statusFilter" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="received">Received</option>
                        <option value="pending">Pending</option>
                        <option value="canceled">Canceled</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <span class="text-muted small">{{ $stocktakings->total() }} record(s)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Stocktaking Table --}}
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">Date</th>
                        <th class="border-0">Item</th>
                        <th class="border-0 text-end">Quantity</th>
                        <th class="border-0 text-end">Unit Price</th>
                        <th class="border-0 text-end">Total</th>
                        <th class="border-0">Conducted By</th>
                        <th class="border-0 text-center">Status</th>
                        <th class="border-0 text-end" style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocktakings as $stocktaking)
                    <tr>
                        <td>
                            <div class="fw-medium">{{ $stocktaking->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $stocktaking->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-info-subtle text-info rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="ti ti-package"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $stocktaking->item->name ?? '-' }}</div>
                                    @if($stocktaking->notes)
                                        <small class="text-muted">{{ Str::limit($stocktaking->notes, 30) }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <span class="fw-medium">{{ number_format($stocktaking->quantity, 0) }}</span>
                        </td>
                        <td class="text-end">
                            TZS {{ number_format($stocktaking->price, 0) }}
                        </td>
                        <td class="text-end fw-medium">
                            TZS {{ number_format($stocktaking->quantity * $stocktaking->price, 0) }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm bg-secondary-subtle text-secondary rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="ti ti-user fs-6"></i>
                                </div>
                                <span>{{ $stocktaking->user->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="badge bg-{{ $stocktaking->status_badge_class }}-subtle text-{{ $stocktaking->status_badge_class }} border-0 dropdown-toggle" data-bs-toggle="dropdown">
                                    {{ ucfirst($stocktaking->stocktaking_status) }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $stocktaking->id }}', 'received')">
                                        <i class="ti ti-check text-success me-2"></i> Received
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $stocktaking->id }}', 'pending')">
                                        <i class="ti ti-clock text-warning me-2"></i> Pending
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="updateStatus('{{ $stocktaking->id }}', 'canceled')">
                                        <i class="ti ti-x text-danger me-2"></i> Canceled
                                    </a></li>
                                </ul>
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <button wire:click="openEditModal('{{ $stocktaking->id }}')" class="btn btn-outline-primary" title="Edit">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <button wire:click="deleteStocktaking('{{ $stocktaking->id }}')" wire:confirm="Are you sure you want to delete this stocktaking record?" class="btn btn-outline-danger" title="Delete">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="avatar avatar-lg bg-light text-muted rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-clipboard-list fs-1"></i>
                            </div>
                            <h6 class="text-muted">No stocktaking records found</h6>
                            <p class="text-muted small mb-3">Start by adding your first stock count or importing from CSV</p>
                            <button wire:click="openAddModal" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i> Add Stock
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($stocktakings->hasPages())
            <div class="card-footer bg-transparent">
                {{ $stocktakings->links() }}
            </div>
        @endif
    </div>

    {{-- Add/Edit Modal --}}
    @if($showModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-{{ $editMode ? 'edit' : 'plus' }} me-2"></i>
                        {{ $editMode ? 'Edit Stocktaking' : 'Add Stocktaking' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="saveStocktaking">
                        {{-- Item --}}
                        <div class="mb-3">
                            <label class="form-label">Product <span class="text-danger">*</span></label>
                            <select wire:model="item_id" class="form-select @error('item_id') is-invalid @enderror">
                                <option value="">Select Product</option>
                                @foreach($availableItems as $item)
                                    <option value="{{ $item['id'] }}">
                                        {{ $item['name'] }}
                                        (Stock: {{ $this->getItemBalance($item['id']) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('item_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if(count($availableItems) === 0)
                                <small class="text-warning">
                                    <i class="ti ti-alert-triangle me-1"></i>
                                    No products found. Only items with type "product" can be stocktaken.
                                </small>
                            @endif
                        </div>

                        <div class="row g-3 mb-3">
                            {{-- Quantity --}}
                            <div class="col-6">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" wire:model="quantity" class="form-control @error('quantity') is-invalid @enderror" placeholder="0" min="0.01" step="0.01">
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- Price --}}
                            <div class="col-6">
                                <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" wire:model="price" class="form-control @error('price') is-invalid @enderror" placeholder="0" min="0" step="1">
                                </div>
                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select wire:model="stocktaking_status" class="form-select @error('stocktaking_status') is-invalid @enderror">
                                <option value="pending">Pending</option>
                                <option value="received">Received</option>
                                <option value="canceled">Canceled</option>
                            </select>
                            @error('stocktaking_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">
                                <i class="ti ti-info-circle me-1"></i>
                                Setting status to "Received" will update the item's stock balance.
                            </small>
                        </div>

                        {{-- Notes --}}
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea wire:model="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>

                        <div class="d-flex gap-2 pt-3">
                            <button type="button" wire:click="closeModal" class="btn btn-light flex-fill">Cancel</button>
                            <button type="submit" class="btn btn-primary flex-fill">
                                <span wire:loading.remove wire:target="saveStocktaking">
                                    <i class="ti ti-check me-1"></i> {{ $editMode ? 'Update' : 'Save' }}
                                </span>
                                <span wire:loading wire:target="saveStocktaking">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Import Modal --}}
    @if($showImportModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 900px;">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title">
                        <i class="ti ti-upload me-2"></i> Import Stocktaking
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeImportModal"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    @if(!$showPreview)
                        {{-- Upload Section --}}
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ti ti-file-download me-2"></i> Step 1: Download Template</h6>
                                        <p class="text-muted small mb-3">Download the CSV template to see the required format.</p>
                                        <button wire:click="downloadTemplate" class="btn btn-outline-primary w-100">
                                            <i class="ti ti-download me-2"></i> Download Template
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0 h-100">
                                    <div class="card-body">
                                        <h6 class="mb-3"><i class="ti ti-upload me-2"></i> Step 2: Upload CSV</h6>
                                        <div class="mb-3">
                                            <input type="file" wire:model="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt">
                                            @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            <div wire:loading wire:target="file" class="text-primary small mt-2">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Uploading...
                                            </div>
                                        </div>
                                        <button wire:click="parseFile" class="btn btn-primary w-100" {{ !$file ? 'disabled' : '' }}>
                                            <span wire:loading.remove wire:target="parseFile">
                                                <i class="ti ti-file-analytics me-2"></i> Parse & Preview
                                            </span>
                                            <span wire:loading wire:target="parseFile">
                                                <span class="spinner-border spinner-border-sm me-1"></span> Parsing...
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Available Items Reference --}}
                        @if(count($availableItems) > 0)
                        <div class="card bg-light border-0 mt-4">
                            <div class="card-body">
                                <h6 class="mb-2"><i class="ti ti-package me-2"></i> Available Products</h6>
                                <p class="text-muted small mb-2">Use exact item names in your CSV file:</p>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($availableItems as $item)
                                        <span class="badge bg-white text-dark border">{{ $item['name'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning mt-4">
                            <i class="ti ti-alert-triangle me-2"></i>
                            No products found for the selected carwash. Only items with type "product" can be imported.
                        </div>
                        @endif

                        {{-- CSV Format Reference --}}
                        <div class="card border-0 mt-4">
                            <div class="card-header bg-transparent">
                                <h6 class="mb-0"><i class="ti ti-table me-2"></i> CSV Format</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Column</th>
                                            <th>Required</th>
                                            <th>Description</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <tr><td><code>item_name</code></td><td><span class="badge bg-danger">Yes</span></td><td>Exact item name</td><td>Air Freshener</td></tr>
                                        <tr><td><code>quantity</code></td><td><span class="badge bg-danger">Yes</span></td><td>Stock quantity</td><td>50</td></tr>
                                        <tr><td><code>price</code></td><td><span class="badge bg-danger">Yes</span></td><td>Unit price</td><td>500</td></tr>
                                        <tr><td><code>status</code></td><td><span class="badge bg-danger">Yes</span></td><td>received/pending/canceled</td><td>pending</td></tr>
                                        <tr><td><code>notes</code></td><td><span class="badge bg-secondary">No</span></td><td>Optional notes</td><td>Monthly count</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- Preview Section --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-0">Preview ({{ count($parsedItems) }} records)</h6>
                            </div>
                            <div class="d-flex gap-2">
                                @if(!$importComplete)
                                    <button wire:click="resetImport" class="btn btn-outline-secondary btn-sm">
                                        <i class="ti ti-refresh me-1"></i> Reset
                                    </button>
                                    <button wire:click="importItems" class="btn btn-success btn-sm" {{ count(array_filter($parsedItems, fn($i) => !$i['_has_error'])) === 0 ? 'disabled' : '' }}>
                                        <span wire:loading.remove wire:target="importItems">
                                            <i class="ti ti-upload me-1"></i> Import {{ count(array_filter($parsedItems, fn($i) => !$i['_has_error'])) }} Record(s)
                                        </span>
                                        <span wire:loading wire:target="importItems">
                                            <span class="spinner-border spinner-border-sm me-1"></span> Importing...
                                        </span>
                                    </button>
                                @else
                                    <button wire:click="closeImportModal" class="btn btn-primary btn-sm">
                                        <i class="ti ti-check me-1"></i> Done
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- Errors --}}
                        @if(count($parseErrors) > 0)
                        <div class="alert alert-danger py-2 mb-3">
                            <strong><i class="ti ti-alert-circle me-1"></i> {{ count($parseErrors) }} error(s) found</strong>
                            <div class="small mt-1" style="max-height: 80px; overflow-y: auto;">
                                @foreach(array_slice($parseErrors, 0, 5) as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                                @if(count($parseErrors) > 5)
                                    <div class="text-muted">...and {{ count($parseErrors) - 5 }} more</div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Import Summary --}}
                        @if($importComplete)
                        <div class="alert alert-info py-2 mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h5 mb-0">{{ count($parsedItems) }}</div>
                                    <small>Total</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-success">{{ $successCount }}</div>
                                    <small>Imported</small>
                                </div>
                                <div class="col-4">
                                    <div class="h5 mb-0 text-danger">{{ $errorCount }}</div>
                                    <small>Failed</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Preview Table --}}
                        <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>Item</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th>Status</th>
                                        <th class="text-center">Result</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parsedItems as $index => $item)
                                    <tr class="{{ $item['_has_error'] ? 'table-danger' : (($item['_imported'] ?? false) ? 'table-success' : '') }}">
                                        <td><span class="badge bg-secondary">{{ $item['_row'] }}</span></td>
                                        <td>{{ $item['item_name'] ?? '-' }}</td>
                                        <td class="text-end">{{ $item['quantity'] ?? '-' }}</td>
                                        <td class="text-end">{{ number_format($item['price'] ?? 0) }}</td>
                                        <td>
                                            <span class="badge bg-{{ ($item['status'] ?? '') === 'received' ? 'success' : (($item['status'] ?? '') === 'pending' ? 'warning' : 'danger') }}-subtle text-{{ ($item['status'] ?? '') === 'received' ? 'success' : (($item['status'] ?? '') === 'pending' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($item['status'] ?? '-') }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($item['_imported'] ?? false)
                                                <span class="badge bg-success"><i class="ti ti-check"></i></span>
                                            @elseif($item['_has_error'])
                                                <span class="badge bg-danger" title="{{ implode(', ', $item['_errors']) }}"><i class="ti ti-x"></i></span>
                                            @else
                                                <span class="badge bg-secondary"><i class="ti ti-minus"></i></span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(!$importComplete && !($item['_imported'] ?? false))
                                                <button wire:click="removeImportItem({{ $index }})" class="btn btn-sm btn-link text-danger p-0">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
