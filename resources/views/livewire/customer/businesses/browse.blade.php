<div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Browse Carwashes</h3>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <input type="text" wire:model.live="search" class="form-control" placeholder="Search carwashes...">
                </div>
                <div class="col-md-6">
                    <x-forms.select2
                        name="selectedRegion"
                        :options="$regions"
                        wire:model.live="selectedRegion"
                        placeholder="All Regions"
                        wrapper="false"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Carwash Cards -->
    <div class="row g-4">
        @forelse($businesses as $business)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        @if($business->logo)
                        <img src="{{ asset('storage/' . $business->logo) }}" alt="{{ $business->name }}" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                        @else
                        <div class="bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <i class="ti ti-car-garage fs-3 text-primary"></i>
                        </div>
                        @endif
                        <div>
                            <h5 class="mb-1">{{ $business->name }}</h5>
                            <small class="text-muted">{{ $business->regions->name ?? '' }}</small>
                        </div>
                    </div>
                    <p class="text-muted small mb-3">
                        <i class="ti ti-map-pin me-1"></i> {{ $business->address }}
                    </p>
                    @if($business->operating_hours)
                    <p class="text-muted small mb-3">
                        <i class="ti ti-clock me-1"></i> {{ $business->operating_hours }}
                    </p>
                    @endif
                    <a href="{{ route('customer.business.details', $business->id) }}" class="btn btn-primary btn-sm w-100">
                        View Services
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="ti ti-car-garage fs-1 text-muted mb-3"></i>
                    <h5>No carwashes found</h5>
                    <p class="text-muted">Try adjusting your search filters</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $businesses->links() }}
    </div>
</div>
