# Select2 Component Usage Guide

## Overview
The Select2 component provides a searchable, user-friendly dropdown with Bootstrap 5 styling and full Livewire integration.

## Component Files
- **Component:** `/resources/views/components/forms/select2.blade.php`
- **Auto-init Script:** `/public/assets/js/select2-init.js`
- **Updated Input Component:** `/resources/views/components/forms/input.blade.php` (supports `type="select"`)

## Basic Usage

### Method 1: Using the Dedicated Select2 Component

```blade
<x-forms.select2
    name="country_id"
    label="Country"
    :options="$countries"
    wire:model="country_id"
    required
/>
```

### Method 2: Using the Input Component with type="select"

```blade
<x-forms.input
    type="select"
    name="category_id"
    label="Category"
    :options="$categories"
    wire:model="category_id"
/>
```

### Method 3: Manual HTML (already added select2 class everywhere)

```blade
<select wire:model="region_id" class="form-select select2" data-placeholder="Search regions...">
    <option value="">Select Region</option>
    @foreach($regions as $region)
        <option value="{{ $region->id }}">{{ $region->name }}</option>
    @endforeach
</select>
```

## Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | string | **required** | Field name for form submission and Livewire binding |
| `label` | string | null | Label text shown above the select |
| `placeholder` | string | "Select {label}" | Placeholder text when no option is selected |
| `options` | array | [] | Options array (key => value pairs) |
| `selected` | mixed | null | Pre-selected value |
| `required` | boolean | false | Makes the field required |
| `disabled` | boolean | false | Disables the select field |
| `multiple` | boolean | false | Enables multiple selection |
| `allowClear` | boolean | true | Shows clear button (when not required) |
| `searchable` | boolean | true | Enables search functionality |
| `colSm` | string | '12' | Bootstrap column width for small screens |
| `colMd` | string | null | Bootstrap column width for medium screens |
| `colLg` | string | null | Bootstrap column width for large screens |
| `wrapper` | boolean | true | Wraps component in column div |
| `helperText` | string | null | Helper text shown below the select |

## Examples

### 1. Simple Select with Livewire

```blade
<x-forms.select2
    name="status"
    label="Status"
    :options="['active' => 'Active', 'inactive' => 'Inactive']"
    wire:model="status"
    required
/>
```

### 2. Select with Custom Placeholder

```blade
<x-forms.select2
    name="carwash_id"
    label="Car Wash Location"
    placeholder="Choose your preferred location..."
    :options="$carwashes->pluck('name', 'id')"
    wire:model="carwash_id"
/>
```

### 3. Multiple Selection

```blade
<x-forms.select2
    name="services[]"
    label="Services"
    :options="$services"
    wire:model="selectedServices"
    multiple
/>
```

### 4. With Helper Text

```blade
<x-forms.select2
    name="payment_method"
    label="Payment Method"
    :options="['cash' => 'Cash', 'card' => 'Card', 'mobile' => 'Mobile Money']"
    helperText="Select your preferred payment method"
    wire:model="paymentMethod"
/>
```

### 5. With Optgroups

```blade
@php
$groupedOptions = [
    'Tanzania' => [
        '1' => 'Dar es Salaam',
        '2' => 'Dodoma',
    ],
    'Kenya' => [
        '3' => 'Nairobi',
        '4' => 'Mombasa',
    ]
];
@endphp

<x-forms.select2
    name="city_id"
    label="City"
    :options="$groupedOptions"
    wire:model="city_id"
/>
```

### 6. Custom Grid Layout

```blade
<div class="row">
    <x-forms.select2
        name="region_id"
        label="Region"
        :options="$regions"
        wire:model="region_id"
        colSm="12"
        colMd="6"
        colLg="4"
    />

    <x-forms.select2
        name="district_id"
        label="District"
        :options="$districts"
        wire:model="district_id"
        colSm="12"
        colMd="6"
        colLg="4"
    />
</div>
```

### 7. Disabled State

```blade
<x-forms.select2
    name="country_id"
    label="Country"
    :options="$countries"
    wire:model="country_id"
    disabled
/>
```

### 8. Pre-selected Value

```blade
<x-forms.select2
    name="status"
    label="Status"
    :options="['active' => 'Active', 'inactive' => 'Inactive']"
    selected="active"
    wire:model="status"
/>
```

### 9. In a Modal

```blade
@if($showModal)
    <div class="modal fade show d-block">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <x-forms.select2
                        name="category_id"
                        label="Category"
                        :options="$categories"
                        wire:model="category_id"
                        required
                    />
                </div>
            </div>
        </div>
    </div>
@endif
```

### 10. Without Wrapper (Custom Layout)

```blade
<div class="custom-container">
    <x-forms.select2
        name="item_id"
        label="Item"
        :options="$items"
        wire:model="item_id"
        wrapper="false"
    />
</div>
```

## Livewire Integration

The component fully supports all Livewire binding modifiers:

```blade
{{-- Standard binding --}}
<x-forms.select2 name="status" :options="$statuses" wire:model="status" />

{{-- Deferred binding --}}
<x-forms.select2 name="status" :options="$statuses" wire:model.defer="status" />

{{-- Live binding --}}
<x-forms.select2 name="status" :options="$statuses" wire:model.live="status" />

{{-- Debounced binding --}}
<x-forms.select2 name="status" :options="$statuses" wire:model.live.debounce.500ms="status" />
```

## Features

✅ **Automatic Initialization** - All select elements with `select2` class are auto-initialized
✅ **Search Functionality** - Built-in search for easy option filtering
✅ **Livewire Compatible** - Full two-way data binding support
✅ **Modal Support** - Works perfectly inside Bootstrap modals
✅ **Bootstrap 5 Theme** - Matches your application's design
✅ **Validation Support** - Shows Laravel validation errors
✅ **Multiple Selection** - Support for selecting multiple options
✅ **Optgroup Support** - Organize options into groups
✅ **Responsive** - Works on all device sizes
✅ **Accessible** - Keyboard navigation and ARIA support

## Troubleshooting

### Select2 not initializing
- Ensure jQuery is loaded before Select2
- Check that `/assets/js/select2-init.js` is included
- Verify the select has the `select2` class

### Not working in modals
- The auto-init script handles this automatically
- Ensure modal is rendered when `$showModal` is true

### Livewire not updating
- Use `wire:model` or `wire:model.defer`
- The component automatically syncs with Livewire

## Migration from Plain Select

**Before:**
```blade
<select wire:model="country_id" class="form-select @error('country_id') is-invalid @enderror">
    <option value="">Select Country</option>
    @foreach($countries as $country)
        <option value="{{ $country->id }}">{{ $country->name }}</option>
    @endforeach
</select>
```

**After:**
```blade
<x-forms.select2
    name="country_id"
    label="Country"
    :options="$countries->pluck('name', 'id')"
    wire:model="country_id"
/>
```

Much cleaner and more maintainable! 🎉
