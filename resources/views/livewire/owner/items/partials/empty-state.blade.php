{{-- Empty table message: offers to clear filters when they hide everything, otherwise to add the first item. --}}
@php
    $isFiltered = $search !== '' || $typeFilter !== '' || $categoryFilter !== '' || $unitFilter !== '' || $statusFilter !== '' || $stockFilter !== '';
@endphp
<div class="text-muted">
    <i class="ti {{ $icon }} fs-1 d-block mb-2"></i>
    @if($isFiltered)
        <div class="fw-semibold">No items match your filters</div>
        <button type="button" wire:click="clearFilters" class="btn btn-outline-primary btn-sm mt-2">
            <i class="ti ti-filter-off me-1"></i> Clear filters
        </button>
    @else
        <div class="fw-semibold">No items yet</div>
        <a href="{{ route('owner.itemregister') }}" class="btn btn-primary btn-sm mt-2">
            <i class="ti ti-plus me-1"></i> Add your first item
        </a>
    @endif
</div>
