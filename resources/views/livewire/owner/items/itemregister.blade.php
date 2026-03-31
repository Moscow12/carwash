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
            <h3 class="mb-1">{{ $editMode ? 'Edit Item' : 'Add New Item' }}</h3>
            <p class="text-muted mb-0">{{ $editMode ? 'Update item information' : 'Create a new product or service' }}</p>
        </div>
        <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- Registration Form --}}
    <form wire:submit="save">
        <div class="row g-4">
            {{-- Left Column --}}
            <div class="col-lg-8">
                {{-- Basic Information --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-info-circle me-1"></i> Basic Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($ownerBusinesses->count() > 1)
                            <div class="col-md-6">
                                <x-forms.select2
                                    name="business_id"
                                    label="Business"
                                    placeholder="Select Business"
                                    :options="collect($ownerBusinesses)->pluck('name', 'id')"
                                    wire:model.live="business_id"
                                    required
                                    wrapper="false"
                                />
                            </div>
                            @endif

                            <div class="col-md-6">
                                <x-forms.select2
                                    name="category_id"
                                    label="Category"
                                    placeholder="Select Category"
                                    :options="collect($availableCategories)->pluck('name', 'id')"
                                    wire:model="category_id"
                                    required
                                    :disabled="empty($availableCategories)"
                                    wrapper="false"
                                />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., Full Car Wash, Interior Cleaning">
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Barcode</label>
                                <div class="input-group">
                                    <input type="text" wire:model="barcode"
                                           class="form-control @error('barcode') is-invalid @enderror"
                                           placeholder="Enter barcode or scan">
                                    <button type="button" wire:click="openScannerModal"
                                            class="btn btn-outline-primary"
                                            title="Scan Barcode">
                                        <i class="ti ti-scan"></i>
                                    </button>
                                </div>
                                @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                <small class="text-muted">Optional - Use the camera to scan or enter manually</small>
                            </div>

                            <div class="col-md-6">
                                <x-forms.select2
                                    name="type"
                                    label="Type"
                                    placeholder="Select Type"
                                    :options="collect(['Service' => 'Service', 'product' => 'Product'])"
                                    wire:model="type"
                                    required
                                    wrapper="false"
                                />
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Brief description of the item/service"></textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-currency-dollar me-1"></i> Pricing
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Cost Price (TZS) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="cost_price" class="form-control @error('cost_price') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Selling Price (TZS) <span class="text-danger">*</span></label>
                                <input type="number" wire:model="selling_price" class="form-control @error('selling_price') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Market Price (TZS)</label>
                                <input type="number" wire:model="market_price" class="form-control @error('market_price') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('market_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Unit & Commission --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-settings me-1"></i> Settings
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <x-forms.select2
                                    name="unit_id"
                                    label="Unit"
                                    placeholder="Select Unit"
                                    :options="collect($availableUnits)->mapWithKeys(fn($u) => [$u->id => $u->name . ' (' . $u->symbol . ')'])"
                                    wire:model="unit_id"
                                    required
                                    wrapper="false"
                                />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Commission</label>
                                <input type="number" wire:model="commission" class="form-control @error('commission') is-invalid @enderror" placeholder="0" min="0" step="0.01">
                                @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <x-forms.select2
                                    name="commission_type"
                                    label="Commission Type"
                                    placeholder="Select Commission Type"
                                    :options="collect(['' => 'Select Type', 'fixed' => 'Fixed Amount', 'percentage' => 'Percentage'])"
                                    wire:model="commission_type"
                                    wrapper="false"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Options --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-toggle-left me-1"></i> Options
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Track Stock?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="product_stock" value="yes" id="stockYes">
                                        <label class="form-check-label" for="stockYes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="product_stock" value="no" id="stockNo">
                                        <label class="form-check-label" for="stockNo">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Require Plate Number?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="require_plate_number" value="yes" id="plateYes">
                                        <label class="form-check-label" for="plateYes">Yes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="require_plate_number" value="no" id="plateNo">
                                        <label class="form-check-label" for="plateNo">No</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="status" value="active" id="statusActive">
                                        <label class="form-check-label" for="statusActive">
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" wire:model="status" value="inactive" id="statusInactive">
                                        <label class="form-check-label" for="statusInactive">
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column --}}
            <div class="col-lg-4">
                {{-- Image Upload --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-transparent border-bottom">
                        <h6 class="mb-0 text-primary">
                            <i class="ti ti-photo me-1"></i> Item Image
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Upload Image</label>
                            <input type="file" wire:model="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Recommended: Square image, max 2MB</small>
                        </div>

                        {{-- Image Preview --}}
                        <div class="text-center">
                            @if($image)
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-2">New Image Preview:</small>
                                    <img src="{{ $image->temporaryUrl() }}" class="rounded img-thumbnail" style="max-height: 200px;">
                                </div>
                            @endif
                            @if($existingImage && !$image)
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-2">Current Image:</small>
                                    <img src="{{ Storage::url($existingImage) }}" class="rounded img-thumbnail" style="max-height: 200px;">
                                </div>
                            @endif
                            @if(!$image && !$existingImage)
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <div class="text-center text-muted">
                                        <i class="ti ti-photo fs-1"></i>
                                        <p class="mb-0 small">No image</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <span wire:loading.remove wire:target="save">
                                    <i class="ti ti-{{ $editMode ? 'check' : 'plus' }} me-1"></i>
                                    {{ $editMode ? 'Update Item' : 'Create Item' }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                                </span>
                            </button>
                            <a href="{{ route('owner.list-items') }}" class="btn btn-outline-secondary">
                                <i class="ti ti-x me-1"></i> Cancel
                            </a>
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
