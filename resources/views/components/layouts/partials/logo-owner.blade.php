@php
    $settings = Auth::user()->getCurrentBusinessSettings();
    $logoUrl = $settings && $settings->business_logo
        ? asset('storage/' . $settings->business_logo)
        : asset('images/brand/logo/logo-icon.svg');
    $businessName = $settings && $settings->business_name
        ? $settings->business_name
        : 'CAMS Owner';
@endphp
<div class="brand-logo">
    <a class='d-none d-md-flex align-items-center gap-2' href="{{ route('owner.dashboard') }}">
    <img src="{{ $logoUrl }}" alt="{{ $businessName }}" style="max-height: 40px; max-width: 40px; object-fit: contain;" />
    <span class="fw-bold fs-4 site-logo-text">{{ $businessName }}</span>
    </a>
</div>
