{{-- Profit margin badge (on selling price) with an amount underneath: per-unit profit by default, or $profitAmount when given.
     Pass item => null with an explicit margin for totals rows. --}}
@php
    $marginValue = $item ? $item->selling_margin : ($margin ?? null);
    $amount = $profitAmount ?? ($item ? $item->selling_price - $item->cost_price : 0);
@endphp
<td class="text-end">
    @if($marginValue === null)
        <span class="text-muted">-</span>
    @else
        @php $marginColor = (float) $marginValue < 0 ? 'danger' : ((float) $marginValue < 15 ? 'warning' : 'success'); @endphp
        <span class="badge bg-{{ $marginColor }}-subtle text-{{ $marginColor }}-emphasis">{{ number_format((float) $marginValue, 1) }}%</span>
        <small class="d-block {{ ($dark ?? false) ? 'text-white-50' : 'text-muted' }}">TZS {{ number_format($amount, 2) }}</small>
    @endif
</td>
