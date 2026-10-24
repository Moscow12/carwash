{{-- Document header: business on the left, report title and details on the right. --}}
@props([
    'title',
    'business' => null,      // Business model or id; null = all businesses
    'meta' => [],            // ['Period' => 'Oct 2026', 'Status' => 'Paid', ...]; empty values are skipped
])
@php
    $businessModel = $business instanceof \App\Models\Business
        ? $business->loadMissing('settings')
        : ($business ? \App\Models\Business::with('settings')->find($business) : null);
    $settings = $businessModel?->settings;
    $businessName = $settings?->business_name ?: ($businessModel?->name ?? 'All businesses');
    $address = $settings?->business_address ?: $businessModel?->address;
@endphp
<div class="doc-header">
    <div>
        @if($settings?->show_logo_on_receipt && $businessModel?->logo)
            <img src="{{ asset('storage/'.$businessModel->logo) }}" alt="" class="logo">
        @endif
        <h1>{{ $businessName }}</h1>
        @if($address)
            <div class="muted">{{ $address }}</div>
        @endif
        @if($settings?->business_phone)
            <div class="muted">Tel: {{ $settings->business_phone }}</div>
        @endif
    </div>
    <div class="meta">
        <h1>{{ $title }}</h1>
        @foreach(array_filter($meta, fn ($value) => $value !== null && $value !== '') as $label => $value)
            <div><strong>{{ $label }}:</strong> {{ $value }}</div>
        @endforeach
        {{ $slot }}
    </div>
</div>
