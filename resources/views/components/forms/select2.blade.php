{{-- resources/views/components/forms/select2.blade.php --}}
@props([
    'name',
    'label' => null,
    'placeholder' => null,
    'options' => [],
    'selected' => null,
    'required' => false,
    'disabled' => false,
    'multiple' => false,
    'allowClear' => true,
    'searchable' => true,
    'colSm' => '12',
    'colMd' => null,
    'colLg' => null,
    'wrapper' => true,
    'helperText' => null,
])

@php
    $colClasses = "col-sm-{$colSm}";
    if ($colMd) $colClasses .= " col-md-{$colMd}";
    if ($colLg) $colClasses .= " col-lg-{$colLg}";

    // Detect Livewire model binding dynamically
    $wireModel = collect($attributes->getAttributes())
        ->keys()
        ->first(fn($attr) => str_starts_with($attr, 'wire:model'));

    // Generate unique ID
    $uniqueId = $name . '_' . uniqid();

    // Default placeholder
    $defaultPlaceholder = $placeholder ?? ($label ? "Select {$label}" : "Select an option");
@endphp

@if($wrapper)
<div class="{{ $colClasses }}">
@endif

    <div class="mb-3">
        @if($label)
            <label for="{{ $uniqueId }}" class="form-label">
                {{ $label }}
                @if($required)<span class="text-danger">*</span>@endif
            </label>
        @endif

        <select
            name="{{ $name }}"
            id="{{ $uniqueId }}"
            class="form-select select2 @error($name) is-invalid @enderror"
            data-placeholder="{{ $defaultPlaceholder }}"
            {{ $attributes->except(['class']) }}
            @if(!$wireModel) wire:model.defer="{{ $name }}" @endif
            @if($required) required @endif
            @if($disabled) disabled @endif
            @if($multiple) multiple @endif>

            @if(!$multiple && !$required)
                <option value="">{{ $defaultPlaceholder }}</option>
            @endif

            @foreach($options as $key => $option)
                @if(is_array($option))
                    {{-- Optgroup support --}}
                    <optgroup label="{{ $key }}">
                        @foreach($option as $subKey => $subOption)
                            <option value="{{ $subKey }}" {{ $selected == $subKey ? 'selected' : '' }}>
                                {{ $subOption }}
                            </option>
                        @endforeach
                    </optgroup>
                @else
                    <option value="{{ $key }}" {{ $selected == $key ? 'selected' : '' }}>
                        {{ $option }}
                    </option>
                @endif
            @endforeach
        </select>

        @if($helperText)
            <small class="form-text text-muted">{{ $helperText }}</small>
        @endif

        @error($name)
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

@if($wrapper)
</div>
@endif
