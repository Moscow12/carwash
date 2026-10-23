<div class="item-form-page">
    <style>
        .item-form-page .type-option { cursor: pointer; border: 2px solid var(--bs-border-color); border-radius: 14px; transition: border-color .12s, background-color .12s; }
        .item-form-page .type-option:hover { border-color: var(--bs-primary-border-subtle); }
        .item-form-page .type-option.selected { border-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
        .item-form-page .type-icon { width: 44px; height: 44px; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .item-form-page .section-title { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color); font-weight: 600; }
        .item-form-page .option-row { border-radius: 12px; background: var(--bs-tertiary-bg); }
        .item-form-page .form-switch .form-check-input { cursor: pointer; width: 2.4em; height: 1.3em; }
        .item-form-page .image-drop { border: 2px dashed var(--bs-border-color); border-radius: 14px; min-height: 200px; cursor: pointer; transition: border-color .12s, background-color .12s; }
        .item-form-page .image-drop.dragging { border-color: var(--bs-primary); background: var(--bs-primary-bg-subtle); }
        @media (min-width: 992px) { .item-form-page .sticky-aside { position: sticky; top: 90px; } }
        [x-cloak] { display: none !important; }
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
            <h3 class="mb-1">{{ $editMode ? 'Edit Item' : 'Add Item' }}</h3>
            <p class="text-muted mb-0">Fields marked <span class="text-danger">*</span> are required. Everything else can be added later.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('owner.uploaditems') }}" class="btn btn-outline-primary">
                <i class="ti ti-file-upload me-1"></i> Import CSV
            </a>
            <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to Items
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2">
            <i class="ti ti-alert-circle me-1"></i> Please fix the highlighted {{ Str::plural('field', $errors->count()) }} below.
        </div>
    @endif

    <form wire:submit="save"
          x-data="{
              cost: @entangle('cost_price'),
              sell: @entangle('selling_price'),
              get profit() { return (Number(this.sell) || 0) - (Number(this.cost) || 0); },
              get margin() { return Number(this.sell) > 0 ? this.profit * 100 / Number(this.sell) : null; },
              money(value) { return 'TZS ' + (Number(value) || 0).toLocaleString(undefined, { maximumFractionDigits: 2 }); }
          }">
        <div class="row g-4">
            {{-- Left Column --}}
            <div class="col-lg-8">
                {{-- Type --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title mb-3">1 · What are you adding?</div>
                        <div class="row g-3" role="radiogroup" aria-label="Item type">
                            @foreach([
                                'product' => ['label' => 'Product', 'icon' => 'ti-package', 'color' => 'info', 'hint' => 'Goods you sell, like drinks or spare parts. Stock can be tracked.'],
                                'Service' => ['label' => 'Service', 'icon' => 'ti-tools', 'color' => 'success', 'hint' => 'Work you do, like a car wash or repair. No stock.'],
                            ] as $value => $option)
                                <div class="col-sm-6" wire:key="type-option-{{ $value }}">
                                    <label class="type-option d-flex align-items-start gap-3 p-3 h-100 m-0 {{ $type === $value ? 'selected' : '' }}">
                                        <input type="radio" class="visually-hidden" wire:model.live="type" value="{{ $value }}">
                                        <span class="type-icon bg-{{ $option['color'] }}-subtle text-{{ $option['color'] }}"><i class="ti {{ $option['icon'] }} fs-4"></i></span>
                                        <span>
                                            <span class="fw-semibold d-block">{{ $option['label'] }}</span>
                                            <small class="text-muted">{{ $option['hint'] }}</small>
                                        </span>
                                        @if($type === $value)
                                            <i class="ti ti-circle-check-filled text-primary ms-auto fs-5"></i>
                                        @endif
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('type') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Details --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title mb-3">2 · Details</div>
                        <div class="row g-3">
                            @if($ownerBusinesses->count() > 1)
                                <div class="col-12">
                                    <label class="form-label">Business <span class="text-danger">*</span></label>
                                    <select wire:model.live="business_id" class="form-select @error('business_id') is-invalid @enderror">
                                        <option value="">Select business</option>
                                        @foreach($ownerBusinesses as $business)
                                            <option value="{{ $business->id }}" wire:key="business-option-{{ $business->id }}">{{ $business->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('business_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div class="col-12">
                                <label class="form-label">{{ $type === 'Service' ? 'Service' : 'Product' }} name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" x-init="$nextTick(() => $el.focus())" x-on:item-form-reset.window="$el.focus()"
                                       class="form-control form-control-lg @error('name') is-invalid @enderror"
                                       placeholder="{{ $type === 'Service' ? 'e.g. Full Car Wash' : 'e.g. Coca-Cola 500ml' }}" maxlength="255" autocomplete="off">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>Category <span class="text-danger">*</span></span>
                                    @if($business_id && ! $showNewCategory)
                                        <button type="button" wire:click="$set('showNewCategory', true)" class="btn btn-link btn-sm p-0 text-decoration-none">
                                            <i class="ti ti-plus"></i> New category
                                        </button>
                                    @endif
                                </label>
                                @if($showNewCategory)
                                    <div class="input-group">
                                        <input type="text" wire:model="newCategoryName" wire:keydown.enter.prevent="createCategory" x-init="$nextTick(() => $el.focus())"
                                               class="form-control @error('newCategoryName') is-invalid @enderror" placeholder="New category name" maxlength="255">
                                        <button type="button" wire:click="createCategory" class="btn btn-primary">Add</button>
                                        <button type="button" wire:click="$set('showNewCategory', false)" class="btn btn-outline-secondary" aria-label="Cancel new category"><i class="ti ti-x"></i></button>
                                    </div>
                                    @error('newCategoryName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                @else
                                    <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror" @disabled(!$business_id)>
                                        <option value="">{{ !$business_id ? 'Select a business first' : (count($availableCategories) ? 'Select category' : 'No categories yet — add one') }}</option>
                                        @foreach($availableCategories as $category)
                                            <option value="{{ $category->id }}" wire:key="category-option-{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unit <span class="text-danger">*</span></label>
                                <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                    <option value="">Select unit</option>
                                    @foreach($availableUnits as $unit)
                                        <option value="{{ $unit->id }}" wire:key="unit-option-{{ $unit->id }}">{{ $unit->name }}{{ $unit->symbol ? ' ('.$unit->symbol.')' : '' }}</option>
                                    @endforeach
                                </select>
                                @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">SKU</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ti ti-hash"></i></span>
                                    <input type="text" class="form-control bg-body-tertiary" value="{{ $editMode ? ($existingSku ?? '-') : ($nextSku ?? 'Select a business') }}" readonly tabindex="-1" aria-describedby="skuHelp">
                                </div>
                                <small id="skuHelp" class="text-muted">{{ $editMode ? 'Assigned automatically' : 'Assigned automatically when you save' }}</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Barcode <span class="text-muted small">(optional)</span></label>
                                <div class="input-group">
                                    <input type="text" wire:model="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Type or scan" autocomplete="off">
                                    <button type="button" wire:click="openScannerModal" class="btn btn-outline-primary" title="Scan with camera">
                                        <i class="ti ti-scan"></i>
                                    </button>
                                </div>
                                @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description <span class="text-muted small">(optional)</span></label>
                                <input type="text" wire:model="description" class="form-control @error('description') is-invalid @enderror" placeholder="Short note shown on details" maxlength="500">
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title mb-3">3 · Pricing</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cost price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" x-model="cost" class="form-control @error('cost_price') is-invalid @enderror" placeholder="0" min="0" step="0.01" inputmode="decimal">
                                </div>
                                @error('cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">What it costs you</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Selling price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" x-model="sell" class="form-control fw-semibold @error('selling_price') is-invalid @enderror" placeholder="0" min="0" step="0.01" inputmode="decimal">
                                </div>
                                @error('selling_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">What customers pay</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Market price <span class="text-muted small">(optional)</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" wire:model="market_price" class="form-control @error('market_price') is-invalid @enderror" placeholder="0" min="0" step="0.01" inputmode="decimal">
                                </div>
                                @error('market_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">Typical price elsewhere</small>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3 rounded-3 p-3 mt-3"
                             :class="margin === null ? 'bg-body-tertiary' : (profit < 0 ? 'bg-danger-subtle' : (margin < 15 ? 'bg-warning-subtle' : 'bg-success-subtle'))">
                            <div>
                                <div class="small text-muted">Profit per unit</div>
                                <div class="fw-bold" x-text="money(profit)"></div>
                            </div>
                            <div>
                                <div class="small text-muted">Margin</div>
                                <div class="fw-bold" x-text="margin === null ? '-' : margin.toFixed(1) + '%'"></div>
                            </div>
                            <small class="ms-md-auto" x-show="profit < 0" x-cloak><i class="ti ti-alert-triangle me-1"></i>Selling price is below cost.</small>
                            <small class="ms-md-auto text-muted" x-show="margin === null" x-cloak>Enter prices to see your margin.</small>
                        </div>
                    </div>
                </div>

                {{-- Commission & options --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="section-title mb-3">4 · Staff commission & options</div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Commission per sale <span class="text-muted small">(optional)</span></label>
                                <input type="number" wire:model.live.debounce.400ms="commission" class="form-control @error('commission') is-invalid @enderror" placeholder="0" min="0" step="0.01" inputmode="decimal">
                                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission type</label>
                                <div class="btn-group w-100" role="group" aria-label="Commission type">
                                    <input type="radio" class="btn-check" id="commissionFixed" wire:model="commission_type" value="fixed" @disabled(!$commission)>
                                    <label class="btn btn-outline-secondary" for="commissionFixed">Fixed (TZS)</label>
                                    <input type="radio" class="btn-check" id="commissionPercentage" wire:model="commission_type" value="percentage" @disabled(!$commission)>
                                    <label class="btn btn-outline-secondary" for="commissionPercentage">Percentage (%)</label>
                                </div>
                                @error('commission_type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                @if(!$commission)
                                    <small class="text-muted">Enter a commission amount first</small>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            @if($type === 'product')
                                <div class="option-row form-check form-switch p-3 ps-5 m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="optStock"
                                           x-bind:checked="$wire.product_stock === 'yes'" x-on:change="$wire.product_stock = $event.target.checked ? 'yes' : 'no'">
                                    <label class="form-check-label fw-medium" for="optStock">
                                        Track stock
                                        <small class="d-block text-muted fw-normal">Deduct stock on each sale and warn when it runs low.</small>
                                    </label>
                                </div>
                            @endif
                            <div class="option-row form-check form-switch p-3 ps-5 m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="optPlate"
                                       x-bind:checked="$wire.require_plate_number === 'yes'" x-on:change="$wire.require_plate_number = $event.target.checked ? 'yes' : 'no'">
                                <label class="form-check-label fw-medium" for="optPlate">
                                    Ask for plate number at checkout
                                    <small class="d-block text-muted fw-normal">For vehicle services, so each sale is linked to a car. On by default for car wash businesses.</small>
                                </label>
                            </div>
                            <div class="option-row form-check form-switch p-3 ps-5 m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="optActive"
                                       x-bind:checked="$wire.status === 'active'" x-on:change="$wire.status = $event.target.checked ? 'active' : 'inactive'">
                                <label class="form-check-label fw-medium" for="optActive">
                                    Active
                                    <small class="d-block text-muted fw-normal">Inactive items are hidden from the POS.</small>
                                </label>
                            </div>
                            <div class="option-row form-check form-switch p-3 ps-5 m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="optPublish" wire:model="is_published">
                                <label class="form-check-label fw-medium" for="optPublish">
                                    <i class="ti ti-world me-1 text-info"></i>Publish to public marketplace
                                    <small class="d-block text-muted fw-normal">Visible to everyone on the public site (needs a photo and active status).</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">
                <div class="sticky-aside">
                    {{-- Image --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <div class="section-title mb-3">Photo <span class="text-muted text-lowercase fw-normal">(optional)</span></div>
                            <label class="image-drop d-flex flex-column align-items-center justify-content-center text-center p-3 m-0 w-100"
                                   x-data="{ dragging: false }" :class="{ 'dragging': dragging }"
                                   x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false"
                                   x-on:drop.prevent="dragging = false; $refs.imageInput.files = $event.dataTransfer.files; $refs.imageInput.dispatchEvent(new Event('change', { bubbles: true }))">
                                <input type="file" x-ref="imageInput" wire:model="image" class="visually-hidden" accept="image/*">
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" alt="" class="rounded-3 mb-2" style="max-height: 180px; max-width: 100%; object-fit: cover;">
                                    <small class="text-muted">Click or drop to replace</small>
                                @elseif($existingImage)
                                    <img src="{{ Storage::url($existingImage) }}" alt="" class="rounded-3 mb-2" style="max-height: 180px; max-width: 100%; object-fit: cover;">
                                    <small class="text-muted">Click or drop to replace</small>
                                @else
                                    <i class="ti ti-photo-plus fs-1 text-muted mb-2"></i>
                                    <span class="fw-medium">Drop a photo here or click to choose</span>
                                    <small class="text-muted">Square works best · max 2MB</small>
                                @endif
                                <span wire:loading wire:target="image" class="small text-primary mt-2"><span class="spinner-border spinner-border-sm me-1"></span> Uploading...</span>
                            </label>
                            @error('image') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Summary & actions --}}
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="section-title mb-2">Summary</div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Type</span>
                                <span class="fw-medium">{{ $type === 'Service' ? 'Service' : 'Product' }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Selling price</span>
                                <span class="fw-semibold" x-text="money(sell)"></span>
                            </div>
                            <div class="d-flex justify-content-between small mb-3">
                                <span class="text-muted">Margin</span>
                                <span class="fw-semibold" :class="profit < 0 ? 'text-danger' : ''" x-text="margin === null ? '-' : margin.toFixed(1) + '%'"></span>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" wire:loading.attr="disabled" wire:target="save,image">
                                    <span wire:loading.remove wire:target="save">
                                        <i class="ti ti-check me-1"></i> {{ $editMode ? 'Save changes' : 'Save item' }}
                                    </span>
                                    <span wire:loading wire:target="save">
                                        <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                    </span>
                                </button>
                                @unless($editMode)
                                    <button type="button" wire:click="save(true)" class="btn btn-outline-primary" wire:loading.attr="disabled" wire:target="save,image">
                                        <i class="ti ti-plus me-1"></i> Save & add another
                                    </button>
                                @endunless
                                <a href="{{ route('owner.list-items') }}" class="btn btn-link text-muted text-decoration-none">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Barcode Scanner Modal --}}
    @if($showScannerModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.9);" wire:keydown.escape="closeScannerModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow bg-dark">
                <div class="modal-header border-bottom bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="ti ti-scan me-2"></i> Scan Barcode
                    </h5>
                    <button type="button" wire:click="closeScannerModal" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body p-2 bg-dark">
                    {{-- Camera preview --}}
                    <div class="scanner-container position-relative">
                        <video id="item-scanner-preview" class="w-100 rounded" style="max-height: 400px; background: #000;" autoplay playsinline></video>

                        {{-- Scanning overlay --}}
                        <div class="scanner-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                            <div class="scanner-frame" style="width: 80%; height: 150px; border: 3px solid #0d6efd; border-radius: 10px; box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);"></div>
                        </div>

                        {{-- Loading indicator --}}
                        <div id="item-scanner-loading" class="position-absolute top-50 start-50 translate-middle text-center text-white">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <div>Starting camera...</div>
                        </div>

                        {{-- Status Messages --}}
                        <div id="item-scanner-status" class="position-absolute bottom-0 start-0 end-0 text-center py-2 bg-dark bg-opacity-75 text-white" style="display: none;"></div>
                    </div>

                    {{-- Instructions --}}
                    <div class="text-center text-white mt-3">
                        <i class="ti ti-info-circle me-1"></i>
                        <small>Position the barcode within the frame</small>
                    </div>

                    {{-- Manual Entry Option --}}
                    <div class="mt-3 pt-3 border-top border-secondary">
                        <label class="form-label small text-white-50">Or enter manually:</label>
                        <div class="input-group">
                            <input type="text" id="item-manual-barcode-input" class="form-control"
                                   placeholder="Enter barcode manually">
                            <button type="button" class="btn btn-primary" onclick="window.submitItemManualBarcode()">
                                <i class="ti ti-check me-1"></i> Use
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary bg-dark">
                    <button type="button" wire:click="closeScannerModal" class="btn btn-light w-100">
                        <i class="ti ti-x me-1"></i> Close Scanner
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ZXing Barcode Scanner Library - Always available --}}
    <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
    <script>
        // Make variables global
        window.itemBarcodeReader = null;
        window.itemScannerInitialized = false;

        window.initItemBarcodeScanner = async function() {
            // Wait for ZXing library to load
            if (typeof ZXing === 'undefined') {
                console.log('ZXing not loaded yet, retrying...');
                setTimeout(window.initItemBarcodeScanner, 100);
                return;
            }

            const preview = document.getElementById('item-scanner-preview');
            const loading = document.getElementById('item-scanner-loading');
            const statusEl = document.getElementById('item-scanner-status');

            if (!preview) {
                console.log('Scanner preview element not found');
                return;
            }

            if (window.itemScannerInitialized) {
                console.log('Scanner already initialized');
                return;
            }

            window.itemScannerInitialized = true;

            try {
                // Show loading
                if (loading) loading.style.display = 'block';
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-dark bg-opacity-75 text-info';
                    statusEl.innerHTML = '<i class="ti ti-camera me-1"></i> Initializing camera...';
                }

                // Initialize code reader
                window.itemBarcodeReader = new ZXing.BrowserMultiFormatReader();

                // Get video devices
                const videoInputDevices = await window.itemBarcodeReader.listVideoInputDevices();

                if (videoInputDevices.length === 0) {
                    throw new Error('No camera found');
                }

                // Prefer back camera on mobile
                let selectedDeviceId = videoInputDevices[0]?.deviceId;
                const backCamera = videoInputDevices.find(device =>
                    device.label.toLowerCase().includes('back') ||
                    device.label.toLowerCase().includes('rear') ||
                    device.label.toLowerCase().includes('environment')
                );
                if (backCamera) {
                    selectedDeviceId = backCamera.deviceId;
                }

                // Hide loading, show ready status
                if (loading) loading.style.display = 'none';
                if (statusEl) {
                    statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-dark bg-opacity-75 text-success';
                    statusEl.innerHTML = '<i class="ti ti-camera-check me-1"></i> Camera ready - Point at barcode';
                }

                // Start decoding
                window.itemBarcodeReader.decodeFromVideoDevice(selectedDeviceId, 'item-scanner-preview', (result, err) => {
                    if (result) {
                        // Barcode detected
                        const code = result.getText();
                        console.log('Barcode scanned:', code);

                        // Show success message
                        if (statusEl) {
                            statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-success text-white';
                            statusEl.innerHTML = '<i class="ti ti-check me-1"></i> Barcode captured: ' + code;
                        }

                        // Vibration feedback if supported
                        if (navigator.vibrate) {
                            navigator.vibrate(200);
                        }

                        // Play beep sound
                        window.playBeep();

                        // Send to Livewire after short delay to show success message
                        setTimeout(() => {
                            window.cleanupItemBarcodeScanner();
                            @this.call('setBarcodeFromScanner', code);
                        }, 800);
                    }

                    if (err && err.name !== 'NotFoundException') {
                        console.error('Scanner error:', err);
                    }
                });

            } catch (err) {
                console.error('Failed to start scanner:', err);
                window.itemScannerInitialized = false;
                if (loading) loading.style.display = 'none';
                if (statusEl) {
                    statusEl.style.display = 'block';
                    statusEl.className = 'position-absolute bottom-0 start-0 end-0 text-center py-2 bg-danger text-white';
                    statusEl.innerHTML = '<i class="ti ti-camera-off me-1"></i> Camera not available. Use manual entry below.';
                }
            }
        };

        window.cleanupItemBarcodeScanner = function() {
            window.itemScannerInitialized = false;

            if (window.itemBarcodeReader) {
                try {
                    window.itemBarcodeReader.reset();
                } catch (e) {
                    console.log('Error resetting scanner:', e);
                }
                window.itemBarcodeReader = null;
            }

            // Stop all video streams
            const preview = document.getElementById('item-scanner-preview');
            if (preview && preview.srcObject) {
                const tracks = preview.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                preview.srcObject = null;
            }
        };

        window.submitItemManualBarcode = function() {
            var input = document.getElementById('item-manual-barcode-input');
            if (input && input.value.trim()) {
                window.cleanupItemBarcodeScanner();
                @this.call('setBarcodeFromScanner', input.value.trim());
            }
        };

        // Play beep sound on successful scan
        window.playBeep = function() {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = 800;
                oscillator.type = 'sine';
                gainNode.gain.value = 0.3;

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            } catch (err) {
                console.log('Audio not supported');
            }
        };

        // Handle Enter key in manual input
        document.addEventListener('keypress', function(e) {
            if (e.target.id === 'item-manual-barcode-input' && e.key === 'Enter') {
                e.preventDefault();
                window.submitItemManualBarcode();
            }
        });

        // Listen for modal open event from Livewire
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('scanner-opened', () => {
                setTimeout(() => {
                    window.initItemBarcodeScanner();
                }, 300);
            });
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            window.cleanupItemBarcodeScanner();
        });
    </script>
</div>
