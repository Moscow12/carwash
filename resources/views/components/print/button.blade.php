{{--
    Print button for any screen. Prints the parts of the page marked with data-print-area
    (or the closest card) in the shared A4 design, with a business header and footer.

    <x-print.button title="Stock Report" :business="$business_id" :meta="['Period' => '...']" all />

    Add `all` on screens using App\Traits\WithPrintMode to print every filtered row, not just the visible page.
--}}
@props([
    'title',
    'business' => null,
    'meta' => [],
    'label' => 'Print',
    'all' => false,
])
<span class="d-print-none print-button-wrap" x-data="{ busy: false }">
    <button type="button"
            {{ $attributes->merge(['class' => 'btn btn-outline-primary btn-sm']) }}
            x-on:click="busy = true; ReportPrint.print($el, {{ $all ? '$wire' : 'null' }}).finally(() => busy = false)"
            x-bind:disabled="busy"
            data-print-title="{{ $title }}">
        <span x-show="!busy"><i class="ti ti-printer me-1"></i> {{ $label }}</span>
        <span x-show="busy" x-cloak><span class="spinner-border spinner-border-sm me-1"></span> Preparing...</span>
    </button>
    <template data-print-styles><x-print.styles with-theme /></template>
    {{-- Keyed on its content: Livewire doesn't morph <template> contents, so a changed header must replace the element --}}
    <template data-print-header wire:key="print-header-{{ md5(json_encode([$title, $business instanceof \App\Models\Business ? $business->id : $business, $meta])) }}"><x-print.header :title="$title" :business="$business" :meta="$meta" /></template>
    <template data-print-footer><x-print.footer /></template>
</span>
