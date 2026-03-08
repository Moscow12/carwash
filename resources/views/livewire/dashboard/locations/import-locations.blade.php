<div>
    <!-- Import Button (for including in other views) -->
    <button wire:click="openModal" class="btn btn-success">
        <i class="ti ti-cloud-download me-1"></i>Import Tanzania Locations
    </button>

    <!-- Import Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-map-pin me-2"></i>
                            Import Tanzania Locations
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal" @if($importing) disabled @endif></button>
                    </div>

                    <div class="modal-body">
                        @if (!$importing)
                            <div class="alert alert-info">
                                <i class="ti ti-info-circle me-2"></i>
                                <strong>About this import:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>This will import 26 Regions</li>
                                    <li>158 Districts</li>
                                    <li>3,964 Wards</li>
                                    @if($includeStreets)
                                        <li>16,741 Streets</li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Include Streets Option -->
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="includeStreetsCheck" wire:model="includeStreets">
                                <label class="form-check-label" for="includeStreetsCheck">
                                    <strong>Include Streets</strong>
                                    <small class="d-block text-muted">Enable this to import all 16,741 streets (takes longer)</small>
                                </label>
                            </div>

                            <div class="alert alert-warning">
                                <i class="ti ti-alert-triangle me-2"></i>
                                <strong>Note:</strong> If locations already exist, they will be skipped. Only new locations will be added.
                            </div>

                            <p class="mb-0">Click "Start Import" to begin importing Tanzania locations into the database.</p>
                        @else
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                    <span class="visually-hidden">Importing...</span>
                                </div>
                                <h5>Importing locations...</h5>
                                <p class="text-muted mb-3">Please wait while we import the data.</p>

                                <!-- Progress Bar -->
                                <div class="progress mb-3" style="height: 25px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                         role="progressbar"
                                         style="width: {{ $progress }}%"
                                         aria-valuenow="{{ $progress }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                        {{ $progress }}%
                                    </div>
                                </div>

                                <!-- Stats -->
                                <div class="row g-3">
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body py-2 px-3 text-center">
                                                <h4 class="mb-0 text-success">{{ $stats['regions'] }}</h4>
                                                <small class="text-muted">Regions</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body py-2 px-3 text-center">
                                                <h4 class="mb-0 text-info">{{ $stats['districts'] }}</h4>
                                                <small class="text-muted">Districts</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-light">
                                            <div class="card-body py-2 px-3 text-center">
                                                <h4 class="mb-0 text-warning">{{ $stats['wards'] }}</h4>
                                                <small class="text-muted">Wards</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal" @if($importing) disabled @endif>
                            Cancel
                        </button>
                        <button type="button"
                                class="btn btn-primary"
                                wire:click="importTanzaniaLocations"
                                @if($importing) disabled @endif>
                            <span wire:loading.remove wire:target="importTanzaniaLocations">
                                <i class="ti ti-download me-1"></i>Start Import
                            </span>
                            <span wire:loading wire:target="importTanzaniaLocations">
                                <span class="spinner-border spinner-border-sm me-1"></span>Importing...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
