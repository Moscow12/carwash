@props(['card'])

@php
    $typeColors = [
        'Shop' => 'info',
        'Services' => 'primary',
        'Service' => 'primary',
        'Rental' => 'success',
        'Hotel Room' => 'warning',
        'Menu' => 'danger',
    ];
    $badge = $typeColors[$card['type_label']] ?? 'secondary';
@endphp

<div class="card border-0 shadow-sm h-100 listing-card">
    {{-- Image / placeholder --}}
    <a href="{{ $card['shop_url'] ?? '#' }}" class="text-decoration-none">
        <div class="position-relative" style="height: 200px; overflow: hidden; border-radius: 16px 16px 0 0;">
            @if($card['image_url'])
                <img src="{{ $card['image_url'] }}" alt="{{ $card['title'] }}"
                     class="w-100 h-100" style="object-fit: cover;">
            @else
                <div class="w-100 h-100 d-flex align-items-center justify-content-center"
                     style="background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);">
                    <i class="fas fa-image text-muted" style="font-size: 3rem; opacity: .5;"></i>
                </div>
            @endif
            <span class="badge bg-{{ $badge }} position-absolute top-0 end-0 m-2">{{ $card['type_label'] }}</span>
        </div>
    </a>

    <div class="card-body d-flex flex-column">
        <h5 class="fw-bold mb-1 text-truncate" title="{{ $card['title'] }}">{{ $card['title'] }}</h5>

        @if($card['business'])
            <a href="{{ $card['shop_url'] }}" class="text-muted small text-decoration-none mb-2">
                <i class="fas fa-store me-1"></i>{{ $card['business']->name }}
            </a>
        @endif

        @if($card['description'])
            <p class="text-muted small mb-3" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                {{ $card['description'] }}
            </p>
        @endif

        <div class="mt-auto d-flex justify-content-between align-items-center">
            <span class="fs-5 fw-bold text-info">{{ $card['price_label'] ?? 'Enquire' }}</span>
            @if($card['shop_url'])
                <a href="{{ $card['shop_url'] }}" class="btn btn-sm btn-outline-cams">
                    View <i class="fas fa-arrow-right ms-1"></i>
                </a>
            @endif
        </div>
    </div>
</div>
