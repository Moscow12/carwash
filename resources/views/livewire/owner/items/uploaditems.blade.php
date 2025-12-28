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

    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Upload Items</h3>
            <p class="text-muted mb-0">Bulk import items and services via CSV file</p>
        </div>
        <a href="{{ route('owner.itemregister') }}" class="btn btn-outline-primary">
            <i class="ti ti-arrow-left me-1"></i> Back to Items
        </a>
    </div>

    <div class="row g-4">
        {{-- Left Column - Upload Section --}}
        <div class="col-lg-4">
            {{-- Step 1: Download Template --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary text-white rounded me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <span class="fw-bold">1</span>
                        </div>
                        <h6 class="mb-0">Download Template</h6>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Download the CSV template to see the required format for uploading items.
                    </p>
                    <button wire:click="downloadTemplate" class="btn btn-outline-primary w-100">
                        <i class="ti ti-download me-2"></i> Download CSV Template
                    </button>
                </div>
            </div>

            {{-- Step 2: Select Carwash --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary text-white rounded me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <span class="fw-bold">2</span>
                        </div>
                        <h6 class="mb-0">Select Carwash</h6>
                    </div>
                </div>
                <div class="card-body">
                    <select wire:model.live="carwash_id" class="form-select @error('carwash_id') is-invalid @enderror">
                        <option value="">Select Carwash</option>
                        @foreach($ownerCarwashes as $carwash)
                            <option value="{{ $carwash['id'] }}">{{ $carwash['name'] }}</option>
                        @endforeach
                    </select>
                    @error('carwash_id') <div class="invalid-feedback">{{ $message }}</div> @enderror

                    @if($carwash_id && count($availableCategories) > 0)
                        <div class="mt-3">
                            <small class="text-muted d-block mb-2">Available Categories:</small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($availableCategories as $cat)
                                    <span class="badge bg-light text-dark">{{ $cat['name'] }}</span>
                                @endforeach
                            </div>
                        </div>
                    @elseif($carwash_id && count($availableCategories) === 0)
                        <div class="alert alert-warning mt-3 mb-0 py-2">
                            <small><i class="ti ti-alert-triangle me-1"></i> No categories found. Please create categories first.</small>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Step 3: Upload File --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm bg-primary text-white rounded me-2 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                            <span class="fw-bold">3</span>
                        </div>
                        <h6 class="mb-0">Upload CSV File</h6>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <input type="file" wire:model="file" class="form-control @error('file') is-invalid @enderror" accept=".csv,.txt" {{ !$carwash_id ? 'disabled' : '' }}>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div wire:loading wire:target="file" class="text-primary small mt-2">
                            <span class="spinner-border spinner-border-sm me-1"></span> Uploading file...
                        </div>
                    </div>

                    <button wire:click="parseFile" class="btn btn-primary w-100" {{ !$carwash_id || !$file ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="parseFile">
                            <i class="ti ti-file-analytics me-2"></i> Parse & Preview
                        </span>
                        <span wire:loading wire:target="parseFile">
                            <span class="spinner-border spinner-border-sm me-1"></span> Parsing...
                        </span>
                    </button>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="card border-0 bg-light mt-4">
                <div class="card-body">
                    <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-1"></i> Instructions</h6>
                    <ul class="small text-muted mb-0 ps-3">
                        <li class="mb-1">Download the template first</li>
                        <li class="mb-1">Fill in your items data (keep the header row)</li>
                        <li class="mb-1">Use exact category and unit names</li>
                        <li class="mb-1">Type must be <code>Service</code> or <code>product</code></li>
                        <li class="mb-1">Use <code>yes</code> or <code>no</code> for boolean fields</li>
                        <li class="mb-1">Status must be <code>active</code> or <code>inactive</code></li>
                        <li>Maximum file size: 5MB</li>
                    </ul>
                </div>
            </div>

            {{-- Available Units --}}
            @if(count($availableUnits) > 0)
            <div class="card border-0 bg-light mt-3">
                <div class="card-body">
                    <h6 class="text-primary mb-2"><i class="ti ti-ruler-2 me-1"></i> Available Units</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($availableUnits as $u)
                            <span class="badge bg-white text-dark border">{{ $u['name'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column - Preview Section --}}
        <div class="col-lg-8">
            @if($showPreview)
                {{-- Preview Card --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><i class="ti ti-list-check me-2"></i> Preview Items</h5>
                            <small class="text-muted">{{ count($parsedItems) }} item(s) found</small>
                        </div>
                        <div class="d-flex gap-2">
                            @if(!$importComplete)
                                <button wire:click="resetUpload" class="btn btn-outline-secondary btn-sm">
                                    <i class="ti ti-refresh me-1"></i> Reset
                                </button>
                                <button wire:click="importItems" class="btn btn-success btn-sm" {{ count(array_filter($parsedItems, fn($i) => !$i['_has_error'])) === 0 ? 'disabled' : '' }}>
                                    <span wire:loading.remove wire:target="importItems">
                                        <i class="ti ti-upload me-1"></i> Import {{ count(array_filter($parsedItems, fn($i) => !$i['_has_error'])) }} Item(s)
                                    </span>
                                    <span wire:loading wire:target="importItems">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Importing...
                                    </span>
                                </button>
                            @else
                                <button wire:click="resetUpload" class="btn btn-primary btn-sm">
                                    <i class="ti ti-plus me-1"></i> Upload More
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Errors Summary --}}
                    @if(count($parseErrors) > 0)
                        <div class="card-body border-bottom bg-danger-subtle py-2">
                            <div class="d-flex align-items-center text-danger mb-2">
                                <i class="ti ti-alert-circle me-2"></i>
                                <strong>{{ count($parseErrors) }} validation error(s) found</strong>
                            </div>
                            <div class="small" style="max-height: 100px; overflow-y: auto;">
                                @foreach(array_slice($parseErrors, 0, 5) as $error)
                                    <div class="text-danger">{{ $error }}</div>
                                @endforeach
                                @if(count($parseErrors) > 5)
                                    <div class="text-muted">...and {{ count($parseErrors) - 5 }} more errors</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Import Summary --}}
                    @if($importComplete)
                        <div class="card-body border-bottom bg-light py-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="h4 mb-0 text-primary">{{ count($parsedItems) }}</div>
                                    <small class="text-muted">Total Items</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0 text-success">{{ $successCount }}</div>
                                    <small class="text-muted">Imported</small>
                                </div>
                                <div class="col-4">
                                    <div class="h4 mb-0 text-danger">{{ $errorCount }}</div>
                                    <small class="text-muted">Failed</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Items Table --}}
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="border-0" style="width: 40px;">#</th>
                                    <th class="border-0">Name</th>
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Category</th>
                                    <th class="border-0 text-end">Selling Price</th>
                                    <th class="border-0 text-center">Status</th>
                                    <th class="border-0 text-end" style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parsedItems as $index => $item)
                                <tr class="{{ $item['_has_error'] ? 'table-danger' : (($item['_imported'] ?? false) ? 'table-success' : '') }}">
                                    <td>
                                        <span class="badge bg-secondary">{{ $item['_row'] }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-{{ $item['type'] === 'Service' ? 'primary' : 'info' }}-subtle text-{{ $item['type'] === 'Service' ? 'primary' : 'info' }} rounded me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                <i class="ti ti-{{ $item['type'] === 'Service' ? 'wash' : 'package' }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $item['name'] ?? '-' }}</div>
                                                <small class="text-muted">{{ Str::limit($item['description'] ?? '', 30) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $item['type'] === 'Service' ? 'primary' : 'info' }}-subtle text-{{ $item['type'] === 'Service' ? 'primary' : 'info' }}">
                                            {{ $item['type'] ?? '-' }}
                                        </span>
                                    </td>
                                    <td>{{ $item['category_name'] ?? '-' }}</td>
                                    <td class="text-end fw-medium">
                                        TZS {{ number_format($item['selling_price'] ?? 0, 0) }}
                                    </td>
                                    <td class="text-center">
                                        @if($item['_imported'] ?? false)
                                            <span class="badge bg-success">
                                                <i class="ti ti-check me-1"></i> Imported
                                            </span>
                                        @elseif($item['_has_error'])
                                            <span class="badge bg-danger" title="{{ implode(', ', $item['_errors']) }}">
                                                <i class="ti ti-x me-1"></i> Error
                                            </span>
                                        @else
                                            <span class="badge bg-{{ strtolower($item['status'] ?? '') === 'active' ? 'success' : 'secondary' }}-subtle text-{{ strtolower($item['status'] ?? '') === 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($item['status'] ?? '-') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$importComplete && !($item['_imported'] ?? false))
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger" title="Remove">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                {{-- Empty State --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="avatar avatar-xl bg-primary-subtle text-primary rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i class="ti ti-cloud-upload fs-1"></i>
                        </div>
                        <h4>Upload Items via CSV</h4>
                        <p class="text-muted mb-4">
                            Bulk import your items and services quickly by uploading a CSV file.<br>
                            Follow the steps on the left to get started.
                        </p>
                        <div class="row g-3 justify-content-center">
                            <div class="col-auto">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2 px-3 text-center">
                                        <i class="ti ti-download text-primary fs-4 d-block mb-1"></i>
                                        <small class="text-muted">Download Template</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <i class="ti ti-arrow-right text-muted"></i>
                            </div>
                            <div class="col-auto">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2 px-3 text-center">
                                        <i class="ti ti-building-store text-primary fs-4 d-block mb-1"></i>
                                        <small class="text-muted">Select Carwash</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto d-flex align-items-center">
                                <i class="ti ti-arrow-right text-muted"></i>
                            </div>
                            <div class="col-auto">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2 px-3 text-center">
                                        <i class="ti ti-file-upload text-primary fs-4 d-block mb-1"></i>
                                        <small class="text-muted">Upload & Import</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sample Format Card --}}
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-transparent">
                        <h6 class="mb-0"><i class="ti ti-table me-2"></i> CSV Format Reference</h6>
                    </div>
                    <div class="card-body p-0">
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
                                    <tr>
                                        <td><code>name</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Item name</td>
                                        <td>Full Car Wash</td>
                                    </tr>
                                    <tr>
                                        <td><code>description</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Item description</td>
                                        <td>Complete cleaning service</td>
                                    </tr>
                                    <tr>
                                        <td><code>cost_price</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Cost price (number)</td>
                                        <td>5000</td>
                                    </tr>
                                    <tr>
                                        <td><code>selling_price</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Selling price (number)</td>
                                        <td>15000</td>
                                    </tr>
                                    <tr>
                                        <td><code>market_price</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Market price (number)</td>
                                        <td>20000</td>
                                    </tr>
                                    <tr>
                                        <td><code>type</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Service or product</td>
                                        <td>Service</td>
                                    </tr>
                                    <tr>
                                        <td><code>product_stock</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Track stock (yes/no)</td>
                                        <td>no</td>
                                    </tr>
                                    <tr>
                                        <td><code>require_plate_number</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Require plate (yes/no)</td>
                                        <td>yes</td>
                                    </tr>
                                    <tr>
                                        <td><code>commission</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>Commission amount</td>
                                        <td>1000</td>
                                    </tr>
                                    <tr>
                                        <td><code>commission_type</code></td>
                                        <td><span class="badge bg-secondary">No</span></td>
                                        <td>fixed or percentage</td>
                                        <td>fixed</td>
                                    </tr>
                                    <tr>
                                        <td><code>category_name</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Exact category name</td>
                                        <td>Premium Services</td>
                                    </tr>
                                    <tr>
                                        <td><code>unit_name</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>Exact unit name</td>
                                        <td>Per Service</td>
                                    </tr>
                                    <tr>
                                        <td><code>status</code></td>
                                        <td><span class="badge bg-danger">Yes</span></td>
                                        <td>active or inactive</td>
                                        <td>active</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
