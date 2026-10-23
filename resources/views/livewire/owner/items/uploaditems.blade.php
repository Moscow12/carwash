<div class="upload-page">
    <style>
        .upload-page .stepper { display: flex; gap: .5rem; }
        .upload-page .step { flex: 1; display: flex; align-items: center; gap: .6rem; padding: .75rem 1rem; border-radius: 12px; background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); min-width: 0; }
        .upload-page .step .step-number { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; flex-shrink: 0; background: var(--bs-secondary-bg); }
        .upload-page .step.current { background: var(--bs-primary-bg-subtle); color: var(--bs-primary-text-emphasis); }
        .upload-page .step.current .step-number { background: var(--bs-primary); color: #fff; }
        .upload-page .step.done .step-number { background: var(--bs-success); color: #fff; }
        .upload-page .dropzone { border: 2px dashed var(--bs-border-color); border-radius: 16px; min-height: 240px; cursor: pointer; transition: border-color .12s, background-color .12s; }
        .upload-page .dropzone.dragging { border-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
        .upload-page .dropzone.disabled { cursor: not-allowed; opacity: .6; }
        .upload-page .summary-tile { border-radius: 12px; padding: .75rem 1rem; }
        .upload-page .preview-table thead th { white-space: nowrap; font-size: .8rem; text-transform: uppercase; letter-spacing: .02em; color: var(--bs-secondary-color); }
        .upload-page .action-bar { position: sticky; bottom: 0; z-index: 5; background: var(--bs-body-bg); border-top: 1px solid var(--bs-border-color); }
        @media (max-width: 767.98px) { .upload-page .step .step-label { display: none; } }
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

    @if (session()->has('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="ti ti-alert-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="mb-1">Import Items</h3>
            <p class="text-muted mb-0">Add many products and services at once from a CSV file</p>
        </div>
        <div class="d-flex gap-2">
            <button wire:click="downloadTemplate" class="btn btn-outline-primary">
                <i class="ti ti-download me-1"></i> Download template
            </button>
            <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to Items
            </a>
        </div>
    </div>

    {{-- Stepper --}}
    @php
        $readyCount = count(array_filter($parsedItems, fn ($i) => ! $i['_has_error']));
        $errorRowCount = count($parsedItems) - $readyCount;
        $currentStep = ! $business_id ? 1 : (! $showPreview ? 2 : ($importComplete ? 4 : 3));
        $steps = [1 => 'Choose business', 2 => 'Upload file', 3 => 'Review & import', 4 => 'Done'];
        $selectedBusinessName = collect($ownerBusinesses)->firstWhere('id', $business_id)?->name;
    @endphp
    <div class="stepper mb-4" aria-label="Import progress">
        @foreach($steps as $number => $label)
            <div class="step {{ $number === $currentStep ? 'current' : ($number < $currentStep ? 'done' : '') }}" wire:key="step-{{ $number }}" @if($number === $currentStep) aria-current="step" @endif>
                <span class="step-number">
                    @if($number < $currentStep) <i class="ti ti-check"></i> @else {{ $number }} @endif
                </span>
                <span class="step-label small fw-medium text-truncate">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    @if(! $showPreview)
        <div class="row g-4">
            {{-- Upload --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        @if(count($ownerBusinesses) > 1)
                            <label class="form-label fw-medium">Import into business</label>
                            <select wire:model.live="business_id" class="form-select mb-4 @error('business_id') is-invalid @enderror">
                                <option value="">Select business</option>
                                @foreach($ownerBusinesses as $business)
                                    <option value="{{ $business['id'] }}" wire:key="business-option-{{ $business['id'] }}">{{ $business['name'] }}</option>
                                @endforeach
                            </select>
                        @elseif($selectedBusinessName)
                            <div class="small text-muted mb-3"><i class="ti ti-building-store me-1"></i> Importing into <strong>{{ $selectedBusinessName }}</strong></div>
                        @endif
                        @error('business_id') <div class="text-danger small mb-3">{{ $message }}</div> @enderror

                        <label class="dropzone d-flex flex-column align-items-center justify-content-center text-center p-4 m-0 w-100 {{ ! $business_id ? 'disabled' : '' }}"
                               x-data="{ dragging: false }" :class="{ 'dragging': dragging }"
                               x-on:dragover.prevent="if ({{ $business_id ? 'true' : 'false' }}) dragging = true"
                               x-on:dragleave.prevent="dragging = false"
                               x-on:drop.prevent="dragging = false; if ({{ $business_id ? 'true' : 'false' }}) { $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true })) }">
                            <input type="file" x-ref="fileInput" wire:model="file" class="visually-hidden" accept=".csv,.txt" @disabled(! $business_id)>

                            <div wire:loading.remove wire:target="file,parseFile">
                                <i class="ti ti-cloud-upload text-primary mb-2" style="font-size: 3rem;"></i>
                                @if(! $business_id)
                                    <div class="fw-semibold">Choose a business first</div>
                                @else
                                    <div class="fw-semibold fs-5">Drop your CSV file here</div>
                                    <div class="text-muted">or <span class="text-primary text-decoration-underline">browse your computer</span></div>
                                    <small class="text-muted d-block mt-2">.csv up to 5MB · we'll check it straight away</small>
                                @endif
                            </div>
                            <div wire:loading wire:target="file,parseFile" class="text-primary">
                                <div class="spinner-border mb-2" role="status"></div>
                                <div class="fw-medium">Reading your file...</div>
                            </div>
                        </label>
                        @error('file') <div class="text-danger small mt-2"><i class="ti ti-alert-circle me-1"></i>{{ $message }}</div> @enderror

                        <div class="d-flex flex-wrap align-items-center gap-2 mt-3 small text-muted">
                            <i class="ti ti-info-circle"></i>
                            <span>Not sure about the format?</span>
                            <button type="button" wire:click="downloadTemplate" class="btn btn-link btn-sm p-0 text-decoration-none">Download the template</button>
                            <span>and fill it in.</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Help --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="ti ti-bulb text-warning me-1"></i> Good to know</h6>
                        <ul class="list-unstyled small mb-0 d-flex flex-column gap-2">
                            <li><i class="ti ti-check text-success me-1"></i> Keep the header row. Columns marked <code>(required)</code> must be filled.</li>
                            <li><i class="ti ti-check text-success me-1"></i> New categories and units are created for you.</li>
                            <li><i class="ti ti-check text-success me-1"></i> <code>type</code> is <code>Service</code> or <code>product</code>.</li>
                            <li><i class="ti ti-check text-success me-1"></i> Imported items are active straight away.</li>
                            <li><i class="ti ti-check text-success me-1"></i> You'll review every row before anything is saved.</li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <details>
                        <summary class="card-header bg-transparent fw-medium" style="cursor: pointer;">
                            <i class="ti ti-table me-1"></i> Column reference
                        </summary>
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
                                    @foreach($templateColumns as $column => $spec)
                                        <tr wire:key="template-column-{{ $column }}">
                                            <td><code>{{ $column }}</code></td>
                                            <td>
                                                @if($spec['required'])
                                                    <span class="badge bg-danger">Required</span>
                                                @else
                                                    <span class="badge bg-secondary">Optional</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $spec['description'] }}
                                                @if(!$spec['required'] && $spec['default'] && $spec['default'] !== 'empty')
                                                    <small class="text-muted d-block">Default: {{ $spec['default'] }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $spec['example'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    @else
        {{-- Done --}}
        @if($importComplete)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center py-5">
                    <div class="bg-{{ $successCount > 0 ? 'success' : 'danger' }}-subtle text-{{ $successCount > 0 ? 'success' : 'danger' }} rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
                        <i class="ti ti-{{ $successCount > 0 ? 'check' : 'x' }}" style="font-size: 2.25rem;"></i>
                    </div>
                    <h4 class="mb-1">{{ $successCount }} {{ Str::plural('item', $successCount) }} imported</h4>
                    <p class="text-muted mb-4">
                        @if($errorCount > 0)
                            {{ $errorCount }} {{ Str::plural('row', $errorCount) }} skipped because of errors. You can fix them in the file and import again.
                        @else
                            Everything in your file was imported.
                        @endif
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="{{ route('owner.list-items') }}" class="btn btn-primary">
                            <i class="ti ti-list me-1"></i> View items
                        </a>
                        <button wire:click="resetUpload" class="btn btn-outline-primary">
                            <i class="ti ti-file-upload me-1"></i> Import another file
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Review --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="summary-tile bg-body-tertiary">
                            <div class="small text-muted">Rows in file</div>
                            <div class="h4 mb-0">{{ count($parsedItems) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="summary-tile bg-success-subtle">
                            <div class="small text-success-emphasis">{{ $importComplete ? 'Imported' : 'Ready to import' }}</div>
                            <div class="h4 mb-0 text-success-emphasis">{{ $importComplete ? $successCount : $readyCount }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="summary-tile {{ $errorRowCount > 0 ? 'bg-danger-subtle' : 'bg-body-tertiary' }}">
                            <div class="small {{ $errorRowCount > 0 ? 'text-danger-emphasis' : 'text-muted' }}">Need fixing</div>
                            <div class="h4 mb-0 {{ $errorRowCount > 0 ? 'text-danger-emphasis' : '' }}">{{ $errorRowCount }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="summary-tile bg-info-subtle">
                            <div class="small text-info-emphasis">New categories / units</div>
                            <div class="h4 mb-0 text-info-emphasis">{{ count($newCategoryNames) }} / {{ count($newUnitNames) }}</div>
                        </div>
                    </div>
                </div>

                @if(!$importComplete && (count($newCategoryNames) > 0 || count($newUnitNames) > 0))
                    <div class="small text-muted mb-3">
                        <i class="ti ti-sparkles text-info me-1"></i>
                        <strong>Will be created on import:</strong>
                        @if(count($newCategoryNames) > 0)
                            <span class="ms-1">{{ count($newCategoryNames) }} {{ Str::plural('category', count($newCategoryNames)) }} ({{ implode(', ', $newCategoryNames) }})</span>
                        @endif
                        @if(count($newCategoryNames) > 0 && count($newUnitNames) > 0)
                            <span class="mx-1">&middot;</span>
                        @endif
                        @if(count($newUnitNames) > 0)
                            <span>{{ count($newUnitNames) }} {{ Str::plural('unit', count($newUnitNames)) }} ({{ implode(', ', $newUnitNames) }})</span>
                        @endif
                    </div>
                @endif

                <ul class="nav nav-pills gap-2 mb-3">
                    @foreach(['all' => ['All', count($parsedItems)], 'ready' => ['Ready', $readyCount], 'errors' => ['Errors', $errorRowCount]] as $filter => [$label, $count])
                        <li class="nav-item" wire:key="preview-filter-{{ $filter }}">
                            <button type="button" wire:click="$set('previewFilter', '{{ $filter }}')" class="nav-link py-1 px-3 {{ $previewFilter === $filter ? 'active' : '' }}">
                                {{ $label }} <span class="badge {{ $previewFilter === $filter ? 'bg-light text-primary' : 'bg-secondary-subtle text-secondary-emphasis' }} ms-1">{{ $count }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                @php
                    $visibleItems = array_filter($parsedItems, fn ($item) => match ($previewFilter) {
                        'ready' => ! $item['_has_error'],
                        'errors' => $item['_has_error'],
                        default => true,
                    });
                @endphp
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0 preview-table">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width: 60px;">Row</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Unit</th>
                                <th class="text-end">Selling Price</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($visibleItems as $index => $item)
                                @php $isService = strtolower($item['type'] ?? '') === 'service'; @endphp
                                <tr wire:key="upload-item-{{ $item['_row'] }}" class="{{ $item['_has_error'] ? 'table-danger' : (($item['_imported'] ?? false) ? 'table-success' : '') }}">
                                    <td class="text-muted">{{ $item['_row'] }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $item['name'] ?: '—' }}</div>
                                        <small class="text-muted">
                                            <span class="badge bg-{{ $isService ? 'success' : 'info' }}-subtle text-{{ $isService ? 'success' : 'info' }}-emphasis">{{ $item['type'] ?: '-' }}</span>
                                            {{ Str::limit($item['description'] ?? '', 40) }}
                                        </small>
                                    </td>
                                    <td>
                                        {{ $item['category_name'] ?: '-' }}
                                        @if(($item['_new_category'] ?? false) && !($item['_imported'] ?? false))
                                            <span class="badge bg-info-subtle text-info ms-1">New</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $item['unit_name'] ?: '-' }}
                                        @if(($item['_new_unit'] ?? false) && !($item['_imported'] ?? false))
                                            <span class="badge bg-info-subtle text-info ms-1">New</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-medium">
                                        TZS {{ is_numeric($item['selling_price'] ?? null) ? number_format((float) $item['selling_price'], 0) : '-' }}
                                    </td>
                                    <td>
                                        @if($item['_imported'] ?? false)
                                            <span class="badge bg-success"><i class="ti ti-check me-1"></i> Imported</span>
                                        @elseif($item['_has_error'])
                                            <span class="badge bg-danger mb-1"><i class="ti ti-x me-1"></i> {{ count($item['_errors']) }} {{ Str::plural('problem', count($item['_errors'])) }}</span>
                                            <ul class="list-unstyled small text-danger-emphasis mb-0">
                                                @foreach($item['_errors'] as $error)
                                                    <li>{{ preg_replace('/^Row \d+: /', '', $error) }}</li>
                                                @endforeach
                                            </ul>
                                        @elseif($item['_import_error'] ?? false)
                                            <span class="badge bg-danger" title="{{ $item['_import_error'] }}"><i class="ti ti-x me-1"></i> Failed</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success-emphasis"><i class="ti ti-circle-check me-1"></i> Ready</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if(!$importComplete && !($item['_imported'] ?? false))
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-sm btn-outline-danger" title="Leave this row out" aria-label="Remove row {{ $item['_row'] }}">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        {{ $previewFilter === 'errors' ? 'No rows with errors. 🎉' : 'No rows to show.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @unless($importComplete)
                <div class="action-bar card-footer d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 py-3">
                    <div class="small text-muted">
                        @if($errorRowCount > 0 && $readyCount > 0)
                            <i class="ti ti-info-circle me-1"></i> {{ $errorRowCount }} {{ Str::plural('row', $errorRowCount) }} with errors will be skipped.
                        @elseif($readyCount === 0)
                            <i class="ti ti-alert-circle me-1 text-danger"></i> No rows are ready. Fix the file and upload it again.
                        @else
                            <i class="ti ti-circle-check me-1 text-success"></i> All rows look good.
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button wire:click="resetUpload" class="btn btn-light">
                            <i class="ti ti-arrow-back-up me-1"></i> Choose another file
                        </button>
                        <button wire:click="importItems" class="btn btn-success" @disabled($readyCount === 0) wire:loading.attr="disabled" wire:target="importItems">
                            <span wire:loading.remove wire:target="importItems">
                                <i class="ti ti-upload me-1"></i> Import {{ $readyCount }} {{ Str::plural('item', $readyCount) }}
                            </span>
                            <span wire:loading wire:target="importItems">
                                <span class="spinner-border spinner-border-sm me-1"></span> Importing...
                            </span>
                        </button>
                    </div>
                </div>
            @endunless
        </div>
    @endif
</div>
