<div>
    {{-- Page Header --}}
    <section class="hero-section" style="min-height: 40vh;">
        <div class="container position-relative">
            <div class="row align-items-center" style="min-height: 40vh;">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="hero-title">The <span class="gradient-text">Marketplace</span></h1>
                    <p class="hero-subtitle">
                        Browse products, rentals, hotel rooms and more from businesses on CAMS
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding">
        <div class="container">
            {{-- Filters --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label small text-muted mb-1">Search</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" wire:model.live.debounce.400ms="search"
                                       class="form-control form-control-site border-start-0 ps-0" placeholder="Search listings...">
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted mb-1">Listing type</label>
                            <select wire:model.live="listingType" class="form-select">
                                <option value="">All types</option>
                                @foreach($listingTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label small text-muted mb-1">Business type</label>
                            <select wire:model.live="businessType" class="form-select">
                                <option value="">All businesses</option>
                                @foreach($businessTypes as $t)
                                    <option value="{{ $t }}">{{ ucfirst(str_replace('_',' ', $t)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label small text-muted mb-1">Category</label>
                            <select wire:model.live="category" class="form-select">
                                <option value="">All</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if($search || $businessType || $listingType || $category)
                        <div class="mt-3">
                            <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear filters
                            </button>
                            <span class="text-muted small ms-2">{{ $listings->total() }} result(s)</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Grid --}}
            <div class="row g-4">
                @forelse($listings as $card)
                    <div class="col-lg-3 col-md-4 col-sm-6" wire:key="listing-{{ $card['type'] }}-{{ $card['id'] }}">
                        <x-listing-card :card="$card" />
                    </div>
                @empty
                    <div class="col-12">
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-store-slash fs-1 d-block mb-3" style="opacity:.4;"></i>
                            <h5>No listings found</h5>
                            <p class="small mb-0">Try adjusting your filters, or check back later as businesses publish more.</p>
                        </div>
                    </div>
                @endforelse
            </div>

            @if($listings->hasPages())
                <div class="d-flex justify-content-center mt-5">{{ $listings->links() }}</div>
            @endif
        </div>
    </section>
</div>
