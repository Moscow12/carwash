<div>
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Carwash Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    @if($carwash->logo)
                    <img src="{{ asset('storage/' . $carwash->logo) }}" alt="{{ $carwash->name }}" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                    @else
                    <div class="bg-primary bg-opacity-10 rounded d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                        <i class="ti ti-car-garage fs-1 text-primary"></i>
                    </div>
                    @endif
                </div>
                <div class="col-md-10">
                    <h2 class="mb-2">{{ $carwash->name }}</h2>
                    <p class="text-muted mb-2">
                        <i class="ti ti-map-pin me-1"></i> {{ $carwash->address }}
                        @if($carwash->regions) - {{ $carwash->regions->name }} @endif
                    </p>
                    @if($carwash->operating_hours)
                    <p class="text-muted mb-2">
                        <i class="ti ti-clock me-1"></i> {{ $carwash->operating_hours }}
                    </p>
                    @endif
                    @if($carwash->description)
                    <p class="mb-0">{{ $carwash->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Services -->
    <h4 class="mb-3">Available Services</h4>
    <div class="row g-4">
        @forelse($services as $service)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    @if($service->image)
                    <img src="{{ asset('storage/' . $service->image) }}" alt="{{ $service->name }}" class="rounded mb-3 w-100" style="height: 150px; object-fit: cover;">
                    @endif
                    <h5 class="mb-2">{{ $service->name }}</h5>
                    <p class="text-muted small mb-3">{{ $service->description }}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-5 fw-bold text-primary">{{ number_format($service->selling_price) }} TZS</span>
                        <button wire:click="openBookingModal('{{ $service->id }}')" class="btn btn-primary btn-sm">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="ti ti-tools fs-1 text-muted mb-3"></i>
                    <h5>No services available</h5>
                    <p class="text-muted">This carwash hasn't added any services yet</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Contact Info -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-transparent">
            <h5 class="mb-0">Contact Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Representative:</strong> {{ $carwash->resentative_name }}</p>
                    <p><strong>Phone:</strong> {{ $carwash->resentative_phone }}</p>
                    @if($carwash->email)
                    <p><strong>Email:</strong> {{ $carwash->email }}</p>
                    @endif
                </div>
                <div class="col-md-6">
                    @if($carwash->whatsapp)
                    <a href="https://wa.me/{{ $carwash->whatsapp }}" class="btn btn-outline-success btn-sm me-2" target="_blank">
                        <i class="ti ti-brand-whatsapp"></i> WhatsApp
                    </a>
                    @endif
                    @if($carwash->instagram)
                    <a href="{{ $carwash->instagram }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                        <i class="ti ti-brand-instagram"></i> Instagram
                    </a>
                    @endif
                    @if($carwash->facebook)
                    <a href="{{ $carwash->facebook }}" class="btn btn-outline-primary btn-sm" target="_blank">
                        <i class="ti ti-brand-facebook"></i> Facebook
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Modal -->
    @if($showBookingModal)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Book Service</h5>
                    <button type="button" class="btn-close" wire:click="closeBookingModal"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit="createBooking">
                        <div class="mb-3">
                            <label class="form-label">Booking Date & Time</label>
                            <input type="datetime-local" wire:model="bookingDate" class="form-control" required>
                            @error('bookingDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plate Number (Optional)</label>
                            <input type="text" wire:model="plateNumber" class="form-control" placeholder="e.g., T 123 ABC">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea wire:model="notes" class="form-control" rows="3" placeholder="Any special requests..."></textarea>
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary" wire:click="closeBookingModal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Confirm Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
