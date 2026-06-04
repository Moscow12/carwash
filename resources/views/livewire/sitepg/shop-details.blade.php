<div>
    @php
        $waNumber = $business->whatsapp ? preg_replace('/\D/', '', $business->whatsapp) : null;
        $phone = $business->whatsapp ?: $business->resentative_phone;
    @endphp

    {{-- Business header --}}
    <section class="hero-section" style="min-height: 45vh;">
        <div class="container position-relative">
            <div class="row align-items-center" style="min-height: 45vh;">
                <div class="col-lg-10 mx-auto">
                    <a href="{{ route('site.marketplace') }}" class="text-white-50 text-decoration-none small mb-3 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Back to Marketplace
                    </a>
                    <div class="d-flex align-items-center flex-wrap gap-4">
                        <div class="bg-white rounded-4 d-flex align-items-center justify-content-center shadow"
                             style="width: 110px; height: 110px; overflow: hidden;">
                            @if($business->logo)
                                <img src="{{ asset('storage/' . $business->logo) }}" alt="{{ $business->name }}"
                                     class="w-100 h-100" style="object-fit: cover;">
                            @else
                                <i class="fas fa-store text-info" style="font-size: 3rem;"></i>
                            @endif
                        </div>
                        <div class="text-white">
                            <span class="badge bg-info mb-2">{{ ucfirst(str_replace('_',' ', $business->type)) }}</span>
                            <h1 class="fw-bold mb-2">{{ $business->name }}</h1>
                            @if($business->full_location)
                                <p class="text-white-50 mb-2"><i class="fas fa-map-marker-alt me-2"></i>{{ $business->full_location }}</p>
                            @endif
                            @if($business->description)
                                <p class="text-white-50 mb-0" style="max-width: 600px;">{{ $business->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Contact / Enquire --}}
                    <div class="d-flex flex-wrap gap-2 mt-4">
                        @if($waNumber)
                            <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hi, I found your business on the CAMS marketplace and would like to enquire.') }}"
                               target="_blank" rel="noopener" class="btn btn-success">
                                <i class="fab fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        @endif
                        @if($phone)
                            <a href="tel:{{ preg_replace('/\s/', '', $phone) }}" class="btn btn-outline-light">
                                <i class="fas fa-phone me-1"></i> Call
                            </a>
                        @endif
                        @if($business->email)
                            <a href="mailto:{{ $business->email }}?subject={{ urlencode('Enquiry from CAMS marketplace') }}" class="btn btn-outline-light">
                                <i class="fas fa-envelope me-1"></i> Email
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Listings --}}
    <section class="section-padding">
        <div class="container">
            @if($total === 0)
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fs-1 d-block mb-3" style="opacity:.4;"></i>
                    <h5>No published listings yet</h5>
                    <p class="small mb-0">This business hasn't published any listings to the marketplace.</p>
                </div>
            @else
                @foreach($grouped as $typeLabel => $cards)
                    <div class="mb-5">
                        <div class="d-flex align-items-center mb-4">
                            <h3 class="section-title mb-0">{{ $typeLabel }}</h3>
                            <span class="badge bg-light text-muted ms-3">{{ $cards->count() }}</span>
                        </div>
                        <div class="row g-4">
                            @foreach($cards as $card)
                                <div class="col-lg-3 col-md-4 col-sm-6" wire:key="sd-{{ $card['type'] }}-{{ $card['id'] }}">
                                    @php
                                        // Booking-style listings prompt login instead of a price action.
                                        $needsBooking = in_array($card['type'], ['service', 'room', 'rental'], true);
                                    @endphp
                                    <x-listing-card :card="$card" />
                                    @if($needsBooking)
                                        <a href="{{ route('site.login') }}" class="btn btn-sm btn-cams w-100 mt-2">
                                            <i class="fas fa-calendar-check me-1"></i> Login to book
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>
</div>
