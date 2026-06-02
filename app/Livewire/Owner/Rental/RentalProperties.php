<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\countries;
use App\Models\districts;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\regions;
use App\Models\street;
use App\Models\wards;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class RentalProperties extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Business / catalog context
    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $landlordFilter = '';

    // Modal state
    public $showModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $propertyId = null;
    public $viewProperty = null;

    // Form fields (mirror properties table)
    public $landlord_id = '';
    public $property_name = '';
    public $property_type = 'apartment';
    public $country_id = '';
    public $region_id = '';
    public $district_id = '';
    public $ward_id = '';
    public $street_id = '';
    public $postal_address = '';
    public $description = '';
    public $status = 'active';

    // Cascading location lists for the modal
    public $allCountries;
    public $allRegions;
    public $allDistricts;
    public $allWards;
    public $allStreets;

    public function mount(): void
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())
            ->where('type', 'rental')
            ->orderBy('name')
            ->get();

        if ($this->ownerBusinesses->isNotEmpty()) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }

        $this->allCountries = countries::orderBy('name')->get();
        $this->allRegions = regions::orderBy('name')->get();
        $this->allDistricts = collect();
        $this->allWards = collect();
        $this->allStreets = collect();
    }

    // ─── Reactivity ──────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }
    public function updatingLandlordFilter(): void { $this->resetPage(); }

    public function updatedSelectedBusiness(): void
    {
        $this->reset(['landlordFilter']);
        $this->resetPage();
    }

    public function updatedRegionId($value): void
    {
        $this->allDistricts = $value ? districts::where('region_id', $value)->orderBy('name')->get() : collect();
        $this->district_id = '';
        $this->ward_id = '';
        $this->street_id = '';
        $this->allWards = collect();
        $this->allStreets = collect();
    }

    public function updatedDistrictId($value): void
    {
        $this->allWards = $value ? wards::where('district_id', $value)->orderBy('name')->get() : collect();
        $this->ward_id = '';
        $this->street_id = '';
        $this->allStreets = collect();
    }

    public function updatedWardId($value): void
    {
        $this->allStreets = $value ? street::where('ward_id', $value)->orderBy('name')->get() : collect();
        $this->street_id = '';
    }

    // ─── Modal: Add ──────────────────────────────────────────────

    public function openAddModal(): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $property = $this->scopedPropertyQuery()->find($id);
        if (!$property) return;

        $this->propertyId = $property->id;
        $this->landlord_id = $property->landlord_id;
        $this->property_name = $property->property_name;
        $this->property_type = $property->property_type;
        $this->country_id = $property->country_id ?: '';
        $this->region_id = $property->region_id ?: '';
        $this->district_id = $property->district_id ?: '';
        $this->ward_id = $property->ward_id ?: '';
        $this->street_id = $property->street_id ?: '';
        $this->postal_address = $property->postal_address ?: '';
        $this->description = $property->description ?: '';
        $this->status = $property->status;

        // Hydrate cascade lists so the edit modal selects render correctly
        $this->allDistricts = $this->region_id
            ? districts::where('region_id', $this->region_id)->orderBy('name')->get()
            : collect();
        $this->allWards = $this->district_id
            ? wards::where('district_id', $this->district_id)->orderBy('name')->get()
            : collect();
        $this->allStreets = $this->ward_id
            ? street::where('ward_id', $this->ward_id)->orderBy('name')->get()
            : collect();

        $this->editMode = true;
        $this->showModal = true;
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $this->viewProperty = $this->scopedPropertyQuery()
            ->with(['landlord', 'region', 'district', 'ward', 'street'])
            ->withCount('units')
            ->find($id);

        if ($this->viewProperty) {
            $this->showViewModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewProperty = null;
    }

    // ─── Save / Delete / Toggle ──────────────────────────────────

    public function saveProperty(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        // Coerce empty-string selects to null so optional FK columns don't violate constraints
        foreach (['country_id', 'region_id', 'district_id', 'ward_id', 'street_id', 'postal_address', 'description'] as $opt) {
            if ($this->$opt === '') {
                $this->$opt = null;
            }
        }

        $businessId = $this->selectedBusiness;
        /** @param string $attribute */
        $landlordsScope = function (string $attribute, $value, \Closure $fail) use ($businessId) {
            unset($attribute); // closure signature is fixed by Laravel
            $belongs = Landlord::where('id', $value)
                ->where('business_id', $businessId)
                ->exists();
            if (!$belongs) {
                $fail('The selected landlord does not belong to this business.');
            }
        };

        $data = $this->validate([
            'landlord_id' => ['required', 'uuid', $landlordsScope],
            'property_name' => 'required|string|min:2|max:150',
            'property_type' => 'required|in:apartment,standalone,hostel,commercial',
            'country_id' => 'nullable|uuid|exists:countries,id',
            'region_id' => 'nullable|uuid|exists:regions,id',
            'district_id' => 'nullable|uuid|exists:districts,id',
            'ward_id' => 'nullable|uuid|exists:wards,id',
            'street_id' => 'nullable|uuid|exists:streets,id',
            'postal_address' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            if ($this->editMode && $this->propertyId) {
                $property = $this->scopedPropertyQuery()->find($this->propertyId);
                if (!$property) return;
                $property->update($data);
                session()->flash('message', 'Property updated successfully.');
            } else {
                Property::create($data);
                session()->flash('message', 'Property created successfully.');
            }
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving property: ' . $e->getMessage());
        }
    }

    public function deleteProperty(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $property = $this->scopedPropertyQuery()->find($id);
        if (!$property) return;

        if ($property->units()->count() > 0) {
            session()->flash('error', 'Cannot delete a property that has rental units. Deactivate it instead.');
            return;
        }

        try {
            $property->delete();
            session()->flash('message', 'Property deleted.');
        } catch (\Throwable) {
            session()->flash('error', 'Error deleting property.');
        }
    }

    public function toggleStatus(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $property = $this->scopedPropertyQuery()->find($id);
        if (!$property) return;

        $property->update([
            'status' => $property->status === 'active' ? 'inactive' : 'active',
        ]);

        session()->flash('message', 'Property status updated.');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    protected function ensureBusinessSelected(): bool
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a rental business first.');
            return false;
        }
        return true;
    }

    /**
     * Property queries restricted to landlords of the currently selected business.
     * Defence-in-depth: we always join through landlords to enforce business scope.
     */
    protected function scopedPropertyQuery()
    {
        return Property::whereHas('landlord', function ($q) {
            $q->where('business_id', $this->selectedBusiness);
        });
    }

    public function resetForm(): void
    {
        $this->reset([
            'propertyId', 'editMode',
            'landlord_id', 'property_name',
            'country_id', 'region_id', 'district_id', 'ward_id', 'street_id',
            'postal_address', 'description',
        ]);
        $this->property_type = 'apartment';
        $this->status = 'active';
        $this->allDistricts = collect();
        $this->allWards = collect();
        $this->allStreets = collect();
        $this->resetValidation();
    }

    // ─── Render ──────────────────────────────────────────────────

    public function render()
    {
        $landlords = $this->selectedBusiness
            ? Landlord::where('business_id', $this->selectedBusiness)->active()->orderBy('name')->get()
            : collect();

        if (!$this->selectedBusiness) {
            $properties = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'units' => 0];
        } else {
            $properties = $this->scopedPropertyQuery()
                ->with(['landlord:id,name', 'region:id,name', 'district:id,name'])
                ->withCount('units')
                ->when($this->search, function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('property_name', 'like', '%' . $this->search . '%')
                           ->orWhere('postal_address', 'like', '%' . $this->search . '%')
                           ->orWhere('description', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->typeFilter, fn ($q) => $q->where('property_type', $this->typeFilter))
                ->when($this->landlordFilter, fn ($q) => $q->where('landlord_id', $this->landlordFilter))
                ->latest()
                ->paginate(12);

            $base = $this->scopedPropertyQuery();
            $stats = [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'active')->count(),
                'inactive' => (clone $base)->where('status', 'inactive')->count(),
                'units' => (clone $base)->withCount('units')->get()->sum('units_count'),
            ];
        }

        return view('livewire.owner.rental.rentalproperties', [
            'properties' => $properties,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'landlords' => $landlords,
        ]);
    }
}
