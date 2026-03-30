<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Lost & Found</h4>
            <p class="text-muted mb-0">Track and manage lost and found items</p>
        </div>
        <button wire:click="openModal" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> Log New Item
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
                                <i class="ti ti-inbox"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Items</h6>
                                <h3 class="mb-0">{{ $stats['total'] }}</h3>
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
                                <i class="ti ti-search"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Unclaimed</h6>
                                <h3 class="mb-0">{{ $stats['found'] }}</h3>
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
                                <i class="ti ti-user-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Claimed</h6>
                                <h3 class="mb-0">{{ $stats['claimed'] }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm border-start border-secondary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-secondary text-white me-3">
                                <i class="ti ti-trash"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Disposed</h6>
                                <h3 class="mb-0">{{ $stats['disposed'] }}</h3>
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
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Search item name, location...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category</label>
                        <select wire:model.live="filterCategory" class="form-select">
                            <option value="">All Categories</option>
                            <option value="electronics">Electronics</option>
                            <option value="clothing">Clothing</option>
                            <option value="jewelry">Jewelry</option>
                            <option value="documents">Documents</option>
                            <option value="personal_items">Personal Items</option>
                            <option value="luggage">Luggage</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select wire:model.live="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="found">Found</option>
                            <option value="claimed">Claimed</option>
                            <option value="disposed">Disposed</option>
                            <option value="donated">Donated</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items List -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Lost & Found Items</h6>
            </div>
            <div class="card-body">
                @if($items->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Found Date</th>
                                    <th>Location</th>
                                    <th>Guest</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Items will be displayed here -->
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="ti ti-inbox-off fs-1 text-muted"></i>
                        <h5 class="mt-3">No Lost & Found Items</h5>
                        <p class="text-muted">Start logging found items to track and return them to guests</p>
                        <button wire:click="openModal" class="btn btn-primary mt-2">
                            <i class="ti ti-plus me-1"></i> Log First Item
                        </button>
                    </div>

                    <!-- Information Cards -->
                    <div class="row g-3 mt-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3">Common Lost & Found Categories:</h6>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-primary text-white me-3">
                                    <i class="ti ti-device-mobile"></i>
                                </div>
                                <div>
                                    <h6>Electronics</h6>
                                    <small class="text-muted">Phones, chargers, laptops, cameras, headphones</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-info text-white me-3">
                                    <i class="ti ti-shirt"></i>
                                </div>
                                <div>
                                    <h6>Clothing</h6>
                                    <small class="text-muted">Jackets, hats, scarves, shoes, accessories</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-warning text-white me-3">
                                    <i class="ti ti-diamond"></i>
                                </div>
                                <div>
                                    <h6>Jewelry</h6>
                                    <small class="text-muted">Rings, necklaces, watches, bracelets</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-danger text-white me-3">
                                    <i class="ti ti-file-text"></i>
                                </div>
                                <div>
                                    <h6>Documents</h6>
                                    <small class="text-muted">Passports, IDs, credit cards, tickets</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-success text-white me-3">
                                    <i class="ti ti-briefcase"></i>
                                </div>
                                <div>
                                    <h6>Personal Items</h6>
                                    <small class="text-muted">Keys, wallets, sunglasses, books</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-start">
                                <div class="avatar bg-secondary text-white me-3">
                                    <i class="ti ti-luggage"></i>
                                </div>
                                <div>
                                    <h6>Luggage</h6>
                                    <small class="text-muted">Suitcases, bags, backpacks</small>
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
                <h6 class="text-primary mb-3"><i class="ti ti-info-circle me-2"></i>Lost & Found Best Practices</h6>
                <div class="row">
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Log items immediately when found</li>
                            <li>Take photos of valuable items</li>
                            <li>Record exact location and date found</li>
                            <li>Link to guest profile when possible</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="mb-0">
                            <li>Store items securely in designated area</li>
                            <li>Contact guests about valuable items</li>
                            <li>Follow local laws for item disposal</li>
                            <li>Maintain items for minimum 90 days</li>
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
                <p class="text-muted">Please select a hotel to manage lost & found items</p>
            </div>
        </div>
    @endif

    <!-- Log Item Modal -->
    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-plus me-2"></i>
                            Log Lost & Found Item
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                    <select wire:model="category" class="form-select @error('category') is-invalid @enderror">
                                        <option value="electronics">📱 Electronics</option>
                                        <option value="clothing">👕 Clothing</option>
                                        <option value="jewelry">💍 Jewelry</option>
                                        <option value="documents">📄 Documents</option>
                                        <option value="personal_items">🔑 Personal Items</option>
                                        <option value="luggage">🧳 Luggage</option>
                                        <option value="other">📦 Other</option>
                                    </select>
                                    @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Item Name <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="item_name" class="form-control @error('item_name') is-invalid @enderror" placeholder="e.g., iPhone 13 Pro">
                                    @error('item_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Description <span class="text-danger">*</span></label>
                                    <textarea wire:model="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Detailed description of the item, color, brand, distinctive features..."></textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Date Found <span class="text-danger">*</span></label>
                                    <input type="date" wire:model="found_date" class="form-control @error('found_date') is-invalid @enderror">
                                    @error('found_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Location Found <span class="text-danger">*</span></label>
                                    <input type="text" wire:model="found_location" class="form-control @error('found_location') is-invalid @enderror" placeholder="e.g., Room 205, Lobby, Restaurant">
                                    @error('found_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Room (if applicable)</label>
                                    <select wire:model="room_id" class="form-select">
                                        <option value="">-- Not in a room --</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}">Room {{ $room->number }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Associated Guest (if known)</label>
                                    <select wire:model="guest_id" class="form-select">
                                        <option value="">-- Unknown Guest --</option>
                                        @foreach($guests as $guest)
                                            <option value="{{ $guest->id }}">{{ $guest->full_name }} - {{ $guest->email }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select wire:model="status" class="form-select @error('status') is-invalid @enderror">
                                        <option value="found">🔍 Found - Unclaimed</option>
                                        <option value="claimed">✅ Claimed - Returned to owner</option>
                                        <option value="disposed">🗑️ Disposed</option>
                                        <option value="donated">❤️ Donated</option>
                                    </select>
                                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Photo of Item</label>
                                    <input type="file" wire:model="photo" class="form-control @error('photo') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Max 5MB (JPG, PNG, PDF)</small>

                                    @if($photo)
                                        <div class="mt-2 alert alert-success">
                                            <i class="ti ti-check me-2"></i>
                                            File selected: {{ $photo->getClientOriginalName() }}
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Internal Notes</label>
                                    <textarea wire:model="notes" class="form-control" rows="2" placeholder="Any additional notes for staff..."></textarea>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3">
                                <i class="ti ti-info-circle me-2"></i>
                                <small><strong>Tip:</strong> Take a photo and record all distinctive features to help identify the rightful owner.</small>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ti ti-device-floppy me-1"></i> Log Item
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
