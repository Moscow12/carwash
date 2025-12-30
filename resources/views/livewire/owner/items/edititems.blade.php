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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('owner.list-items') }}">Items</a></li>
                    <li class="breadcrumb-item active">Edit Item</li>
                </ol>
            </nav>
            <h3 class="mb-0">Edit Item</h3>
        </div>
        <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
    </div>

    @if($item)
    <form wire:submit="save">
        <div class="row">
            {{-- Left Column - Main Details --}}
            <div class="col-lg-8">
                {{-- Basic Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="ti ti-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter item name">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select wire:model="type" class="form-select @error('type') is-invalid @enderror">
                                    <option value="Service">Service</option>
                                    <option value="product">Product</option>
                                </select>
                                @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Barcode / SKU</label>
                                <div class="input-group">
                                    <input type="text" wire:model="barcode" class="form-control @error('barcode') is-invalid @enderror" placeholder="Enter barcode or SKU">
                                    <button type="button" wire:click="openScannerModal" class="btn btn-outline-primary">
                                        <i class="ti ti-scan"></i>
                                    </button>
                                </div>
                                @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Enter item description"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="ti ti-currency-dollar me-2"></i>Pricing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cost Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" wire:model="cost_price" class="form-control @error('cost_price') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('cost_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" wire:model="selling_price" class="form-control @error('selling_price') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('selling_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Market Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" wire:model="market_price" class="form-control @error('market_price') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('market_price') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Commission (for Services) --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="ti ti-percentage me-2"></i>Commission</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Commission Amount</label>
                                <input type="number" step="0.01" wire:model="commission" class="form-control @error('commission') is-invalid @enderror" placeholder="0.00">
                                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Commission Type</label>
                                <select wire:model="commission_type" class="form-select @error('commission_type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="percentage">Percentage</option>
                                </select>
                                @error('commission_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Category, Settings, Image --}}
            <div class="col-lg-4">
                {{-- Category & Unit --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="ti ti-category me-2"></i>Classification</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Carwash <span class="text-danger">*</span></label>
                            <select wire:model.live="carwash_id" class="form-select @error('carwash_id') is-invalid @enderror">
                                <option value="">Select Carwash</option>
                                @foreach($ownerCarwashes as $carwash)
                                    <option value="{{ $carwash->id }}">{{ $carwash->name }}</option>
                                @endforeach
                            </select>
                            @error('carwash_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($availableCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Unit <span class="text-danger">*</span></label>
                            <select wire:model="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                <option value="">Select Unit</option>
                                @foreach($availableUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Settings --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="ti ti-settings me-2"></i>Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Track Stock <span class="text-danger">*</span></label>
                            <select wire:model="product_stock" class="form-select @error('product_stock') is-invalid @enderror">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                            @error('product_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Enable stock tracking for this item</small>
                        </div>
                        <div>
                            <label class="form-label">Require Plate Number <span class="text-danger">*</span></label>
                            <select wire:model="require_plate_number" class="form-select @error('require_plate_number') is-invalid @enderror">
                                <option value="no">No</option>
                                <option value="yes">Yes</option>
                            </select>
                            @error('require_plate_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Require vehicle plate number at checkout</small>
                        </div>
                    </div>
                </div>

                {{-- Image --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="card-title mb-0"><i class="ti ti-photo me-2"></i>Image</h5>
                    </div>
                    <div class="card-body">
                        @if($existingImage)
                            <div class="mb-3 text-center">
                                <img src="{{ Storage::url($existingImage) }}" alt="Item Image" class="img-fluid rounded mb-2" style="max-height: 150px;">
                                <div>
                                    <button type="button" wire:click="removeImage" wire:confirm="Are you sure you want to remove this image?" class="btn btn-sm btn-outline-danger">
                                        <i class="ti ti-trash me-1"></i> Remove Image
                                    </button>
                                </div>
                            </div>
                        @endif
                        <div>
                            <label class="form-label">{{ $existingImage ? 'Replace Image' : 'Upload Image' }}</label>
                            <input type="file" wire:model="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Max 2MB. Supported: JPG, PNG, GIF</small>
                        </div>
                        @if($image)
                            <div class="mt-2 text-center">
                                <p class="small text-muted mb-1">Preview:</p>
                                <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="img-fluid rounded" style="max-height: 100px;">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Save Button --}}
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <span wire:loading.remove wire:target="save">
                            <i class="ti ti-device-floppy me-1"></i> Save Changes
                        </span>
                        <span wire:loading wire:target="save">
                            <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                        </span>
                    </button>
                    <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
    @else
        <div class="alert alert-warning">
            <i class="ti ti-alert-triangle me-2"></i> Item not found.
        </div>
    @endif

    {{-- Barcode Scanner Modal --}}
    @if($showScannerModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="ti ti-scan me-2"></i>Scan Barcode</h5>
                    <button type="button" class="btn-close" wire:click="closeScannerModal"></button>
                </div>
                <div class="modal-body">
                    <div id="barcode-scanner" style="width: 100%; height: 300px;"></div>
                    <p class="text-muted text-center mt-2 mb-0">
                        <i class="ti ti-info-circle me-1"></i> Point your camera at a barcode
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeScannerModal" class="btn btn-secondary">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('livewire:initialized', function() {
            const html5QrCode = new Html5Qrcode("barcode-scanner");

            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 100 },
                    formatsToSupport: [
                        Html5QrcodeSupportedFormats.EAN_13,
                        Html5QrcodeSupportedFormats.EAN_8,
                        Html5QrcodeSupportedFormats.CODE_128,
                        Html5QrcodeSupportedFormats.CODE_39,
                        Html5QrcodeSupportedFormats.UPC_A,
                        Html5QrcodeSupportedFormats.UPC_E,
                    ]
                },
                (decodedText) => {
                    html5QrCode.stop();
                    @this.call('setBarcodeFromScanner', decodedText);
                },
                (errorMessage) => {}
            ).catch((err) => {
                console.log('Scanner error:', err);
            });

            Livewire.on('closeScannerModal', () => {
                html5QrCode.stop().catch(err => {});
            });
        });
    </script>
    @endif

    {{-- Loading Overlay --}}
    <div wire:loading.delay wire:loading.class="opacity-100" class="opacity-0 position-fixed top-0 end-0 m-3 p-2 bg-primary text-white rounded shadow" style="z-index: 1050; transition: opacity 0.2s;">
        <div class="d-flex align-items-center gap-2">
            <div class="spinner-border spinner-border-sm" role="status"></div>
            <span>Processing...</span>
        </div>
    </div>
</div>
