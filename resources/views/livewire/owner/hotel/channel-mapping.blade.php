<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Channel Mapping</h4>
            <p class="text-muted mb-0">Map room types to OTA channels and configure sync settings</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Add Channel Mapping
        </button>
    </div>

    <!-- Flash Messages -->
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

    <!-- Hotel Selection -->
    @if($hotels->count() > 1)
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <label class="form-label">Select Hotel</label>
                <select wire:model.live="selectedHotel" class="form-select">
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    @endif

    @if($selectedHotel)
        <!-- Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white me-3">
                                <i class="ti ti-map-pin"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Mappings</h6>
                                <h3 class="mb-0">{{ $stats['total_mappings'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-success text-white me-3">
                                <i class="ti ti-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Active</h6>
                                <h3 class="mb-0">{{ $stats['active'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-info text-white me-3">
                                <i class="ti ti-world"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Channels Connected</h6>
                                <h3 class="mb-0">{{ $stats['channels_connected'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-warning text-white me-3">
                                <i class="ti ti-refresh"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Sync Enabled</h6>
                                <h3 class="mb-0">{{ $stats['sync_enabled'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search room type, channel...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Filter by Channel</label>
                        <select wire:model.live="filterChannel" class="form-select">
                            <option value="">All Channels</option>
                            <option value="booking_com">Booking.com</option>
                            <option value="expedia">Expedia</option>
                            <option value="airbnb">Airbnb</option>
                            <option value="agoda">Agoda</option>
                            <option value="hotels_com">Hotels.com</option>
                            <option value="tripadvisor">TripAdvisor</option>
                            <option value="google_hotel">Google Hotel Ads</option>
                            <option value="direct">Direct Booking</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Channel Mappings -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Channel Mappings</h6>
            </div>
            <div class="card-body">
                @if($mappings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Room Type</th>
                                    <th>Channel</th>
                                    <th>Channel Room ID</th>
                                    <th>Sync Settings</th>
                                    <th>Markup</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Mappings will be displayed here -->
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-map-off fs-1 text-muted"></i>
                        <h5 class="mt-3">No Channel Mappings</h5>
                        <p class="text-muted">Start mapping your room types to distribution channels</p>
                        <button wire:click="openModal" class="btn btn-primary mt-2">
                            <i class="ti ti-plus me-1"></i> Create First Mapping
                        </button>
                    </div>

                    <!-- Supported Channels -->
                    <div class="row g-3 mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Supported Distribution Channels:</h6>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-primary text-white mb-2 mx-auto">
                                        <i class="ti ti-building"></i>
                                    </div>
                                    <h6>Booking.com</h6>
                                    <small class="text-muted">World's largest OTA</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-info text-white mb-2 mx-auto">
                                        <i class="ti ti-plane"></i>
                                    </div>
                                    <h6>Expedia</h6>
                                    <small class="text-muted">Global travel platform</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-danger text-white mb-2 mx-auto">
                                        <i class="ti ti-home"></i>
                                    </div>
                                    <h6>Airbnb</h6>
                                    <small class="text-muted">Vacation rentals</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-warning text-white mb-2 mx-auto">
                                        <i class="ti ti-discount"></i>
                                    </div>
                                    <h6>Agoda</h6>
                                    <small class="text-muted">Asian market leader</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-success text-white mb-2 mx-auto">
                                        <i class="ti ti-building-hotel"></i>
                                    </div>
                                    <h6>Hotels.com</h6>
                                    <small class="text-muted">Expedia group brand</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-secondary text-white mb-2 mx-auto">
                                        <i class="ti ti-star"></i>
                                    </div>
                                    <h6>TripAdvisor</h6>
                                    <small class="text-muted">Reviews & bookings</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-dark text-white mb-2 mx-auto">
                                        <i class="ti ti-brand-google"></i>
                                    </div>
                                    <h6>Google Hotel Ads</h6>
                                    <small class="text-muted">Search visibility</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar bg-primary text-white mb-2 mx-auto">
                                        <i class="ti ti-link"></i>
                                    </div>
                                    <h6>Direct Booking</h6>
                                    <small class="text-muted">Your website</small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Information Panel -->
        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Channel Mapping Benefits</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li><strong>Automatic Sync:</strong> Real-time rate and availability updates</li>
                            <li><strong>Rate Parity:</strong> Maintain consistent pricing across channels</li>
                            <li><strong>Overbooking Prevention:</strong> Centralized inventory management</li>
                            <li><strong>Markup Control:</strong> Set different margins per channel</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li><strong>Restriction Sync:</strong> Min/max stay, CTA, CTD rules</li>
                            <li><strong>Multi-Channel Distribution:</strong> Reach global audience</li>
                            <li><strong>Performance Tracking:</strong> Monitor channel effectiveness</li>
                            <li><strong>Flexible Configuration:</strong> Channel-specific overrides</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="ti ti-bed fs-1 text-muted"></i>
                <h5 class="mt-3">No Hotel Selected</h5>
                <p class="text-muted">Please select a hotel to manage channel mappings</p>
            </div>
        </div>
    @endif

    <!-- Add/Edit Mapping Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-plus me-2"></i>
                            {{ $editMode ? 'Edit' : 'Add' }} Channel Mapping
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <!-- Basic Information -->
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">Basic Information</h6>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Room Type <span class="text-danger">*</span></label>
                                    <select wire:model="room_type_id" class="form-select @error('room_type_id') is-invalid @enderror">
                                        <option value="">-- Select Room Type --</option>
                                        @foreach($roomTypes as $roomType)
                                            <option value="{{ $roomType->id }}">{{ $roomType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('room_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Channel <span class="text-danger">*</span></label>
                                    <select wire:model="channel" class="form-select @error('channel') is-invalid @enderror">
                                        <option value="booking_com">🏨 Booking.com</option>
                                        <option value="expedia">✈️ Expedia</option>
                                        <option value="airbnb">🏠 Airbnb</option>
                                        <option value="agoda">💰 Agoda</option>
                                        <option value="hotels_com">🏢 Hotels.com</option>
                                        <option value="tripadvisor">⭐ TripAdvisor</option>
                                        <option value="google_hotel">🔍 Google Hotel Ads</option>
                                        <option value="direct">🔗 Direct Booking</option>
                                    </select>
                                    @error('channel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Channel Room ID <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="channel_room_id" class="form-control @error('channel_room_id') is-invalid @enderror" placeholder="e.g., 12345678">
                                    @error('channel_room_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">The room ID in the channel's system</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Channel Room Name (Optional)</label>
                                    <input type="text" wire:model="channel_room_name" class="form-control" placeholder="e.g., Deluxe King Room">
                                    <small class="text-muted">Name as displayed on the channel</small>
                                </div>

                                <!-- Sync Settings -->
                                <div class="col-12 mt-4">
                                    <h6 class="text-primary mb-3">Synchronization Settings</h6>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="sync_rates" id="syncRates">
                                        <label class="form-check-label" for="syncRates">
                                            <strong>Sync Rates</strong><br>
                                            <small class="text-muted">Auto-update room rates</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="sync_availability" id="syncAvailability">
                                        <label class="form-check-label" for="syncAvailability">
                                            <strong>Sync Availability</strong><br>
                                            <small class="text-muted">Auto-update inventory</small>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="sync_restrictions" id="syncRestrictions">
                                        <label class="form-check-label" for="syncRestrictions">
                                            <strong>Sync Restrictions</strong><br>
                                            <small class="text-muted">Min/max stay, CTA, CTD</small>
                                        </label>
                                    </div>
                                </div>

                                <!-- Pricing & Restrictions -->
                                <div class="col-12 mt-4">
                                    <h6 class="text-primary mb-3">Pricing & Restrictions</h6>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Markup Percentage</label>
                                    <div class="input-group">
                                        <input type="number" wire:model="markup_percentage" class="form-control @error('markup_percentage') is-invalid @enderror" placeholder="0" step="0.01" min="0" max="100">
                                        <span class="input-group-text">%</span>
                                    </div>
                                    @error('markup_percentage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Add markup to base rates</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Min Stay Override</label>
                                    <input type="number" wire:model="min_stay_override" class="form-control" placeholder="Default" min="1">
                                    <small class="text-muted">Override min nights (optional)</small>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Max Stay Override</label>
                                    <input type="number" wire:model="max_stay_override" class="form-control" placeholder="Default" min="1">
                                    <small class="text-muted">Override max nights (optional)</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="active">✅ Active - Sync enabled</option>
                                        <option value="inactive">❌ Inactive - Sync disabled</option>
                                        <option value="paused">⏸️ Paused - Temporarily stopped</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any special notes about this mapping..."></textarea>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="ti ti-info-circle me-2"></i>
                                <small><strong>Note:</strong> Changes will be synced to the channel automatically based on your sync settings.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i> Save Mapping
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
