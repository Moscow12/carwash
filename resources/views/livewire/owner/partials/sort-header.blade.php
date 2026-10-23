{{-- Clickable, sortable column header. Expects the component to expose $sortBy, $sortDirection and sortTable(). --}}
@php
    $isActive = $sortBy === $field;
    $icon = ! $isActive ? 'ti-arrows-sort' : ($sortDirection === 'asc' ? 'ti-sort-ascending' : 'ti-sort-descending');
@endphp
<th class="{{ $class ?? '' }}" @if($isActive) aria-sort="{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <button type="button" wire:click="sortTable('{{ $field }}')" class="btn btn-link p-0 text-reset fw-semibold text-decoration-none" title="Sort by {{ strtolower($label) }}">
        {{ $label }}
        <i class="ti {{ $icon }} ms-1 {{ $isActive ? 'text-primary' : 'text-muted' }}"></i>
    </button>
</th>
