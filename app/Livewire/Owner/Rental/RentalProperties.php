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

    // ─── Search-picker state ─────────────────────────────────────
    // Landlord filter picker (filters bar)
    public $landlordFilterSearch = '';
    public $showLandlordFilterDropdown = false;

    // Form pickers (landlord + cascading location)
    public $landlordSearch = '';
    public $countrySearch = '';
    public $regionSearch = '';
    public $districtSearch = '';
    public $wardSearch = '';
    public $streetSearch = '';

    public $showLandlordDropdown = false;
    public $showCountryDropdown = false;
    public $showRegionDropdown = false;
    public $showDistrictDropdown = false;
    public $showWardDropdown = false;
    public $showStreetDropdown = false;

    public function mount(): void
    {
        $this->ownerBusinesses = Business::where('owner_id', Auth::id())
            ->where('type', 'rental')
            ->orderBy('name')
            ->get();

        if ($this->ownerBusinesses->isNotEmpty()) {
            $this->selectedBusiness = $this->ownerBusinesses->first()->id;
        }
    }

    // ─── Reactivity ──────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingTypeFilter(): void { $this->resetPage(); }

    public function updatedSelectedBusiness(): void
    {
        $this->reset(['landlordFilter', 'landlordFilterSearch']);
        $this->resetPage();
    }

    // ─── Landlord filter picker (filters bar) ────────────────────

    public function selectLandlordFilter(string $id): void
    {
        $this->landlordFilter = $id;
        $this->landlordFilterSearch = '';
        $this->showLandlordFilterDropdown = false;
        $this->resetPage();
    }

    public function clearLandlordFilter(): void
    {
        $this->reset(['landlordFilter', 'landlordFilterSearch']);
        $this->resetPage();
    }

    // ─── Landlord form picker ────────────────────────────────────

    public function selectLandlord(string $id): void
    {
        $this->landlord_id = $id;
        $this->landlordSearch = '';
        $this->showLandlordDropdown = false;
    }

    public function clearLandlord(): void
    {
        $this->reset(['landlord_id', 'landlordSearch']);
    }

    // ─── Cascading location pickers ──────────────────────────────

    public function selectCountry(string $id): void
    {
        $this->country_id = $id;
        $this->countrySearch = '';
        $this->showCountryDropdown = false;
        $this->reset(['region_id', 'district_id', 'ward_id', 'street_id']);
    }

    public function clearCountry(): void
    {
        $this->reset([
            'country_id', 'countrySearch',
            'region_id', 'district_id', 'ward_id', 'street_id',
        ]);
    }

    public function selectRegion(string $id): void
    {
        $this->region_id = $id;
        $this->regionSearch = '';
        $this->showRegionDropdown = false;
        $this->reset(['district_id', 'ward_id', 'street_id']);
    }

    public function clearRegion(): void
    {
        $this->reset([
            'region_id', 'regionSearch',
            'district_id', 'ward_id', 'street_id',
        ]);
    }

    public function selectDistrict(string $id): void
    {
        $this->district_id = $id;
        $this->districtSearch = '';
        $this->showDistrictDropdown = false;
        $this->reset(['ward_id', 'street_id']);
    }

    public function clearDistrict(): void
    {
        $this->reset(['district_id', 'districtSearch', 'ward_id', 'street_id']);
    }

    public function selectWard(string $id): void
    {
        $this->ward_id = $id;
        $this->wardSearch = '';
        $this->showWardDropdown = false;
        $this->reset(['street_id']);
    }

    public function clearWard(): void
    {
        $this->reset(['ward_id', 'wardSearch', 'street_id']);
    }

    public function selectStreet(string $id): void
    {
        $this->street_id = $id;
        $this->streetSearch = '';
        $this->showStreetDropdown = false;
    }

    public function clearStreet(): void
    {
        $this->reset(['street_id', 'streetSearch']);
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

        // Older records may hold a region without a country — backfill so the
        // cascade displays correctly (the country picker drives the region list).
        if (!$this->country_id && $this->region_id) {
            $this->country_id = regions::find($this->region_id)?->country_id ?: '';
        }

        $this->reset([
            'landlordSearch', 'countrySearch', 'regionSearch',
            'districtSearch', 'wardSearch', 'streetSearch',
        ]);

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
            'landlordSearch', 'countrySearch', 'regionSearch',
            'districtSearch', 'wardSearch', 'streetSearch',
            'showLandlordDropdown', 'showCountryDropdown', 'showRegionDropdown',
            'showDistrictDropdown', 'showWardDropdown', 'showStreetDropdown',
        ]);
        $this->property_type = 'apartment';
        $this->status = 'active';
        $this->resetValidation();
    }

    // ─── Render ──────────────────────────────────────────────────

    /** Base query for landlords selectable in this business. */
    protected function landlordPool()
    {
        return Landlord::where('business_id', $this->selectedBusiness)->active();
    }

    public function render()
    {
        $landlordCount = $this->selectedBusiness ? $this->landlordPool()->count() : 0;

        // ─── Landlord pickers (form + filter) ──
        $selectedFormLandlord = $this->landlord_id ? Landlord::find($this->landlord_id) : null;
        $landlordResults = ($this->selectedBusiness && !$this->landlord_id)
            ? $this->landlordPool()
                ->when($this->landlordSearch, fn ($q) => $q->where(fn ($qq) => $qq
                    ->where('name', 'like', '%' . $this->landlordSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->landlordSearch . '%')))
                ->orderBy('name')->limit(30)->get(['id', 'name', 'phone'])
            : collect();

        $selectedFilterLandlord = $this->landlordFilter ? Landlord::find($this->landlordFilter) : null;
        $landlordFilterResults = ($this->selectedBusiness && !$this->landlordFilter)
            ? $this->landlordPool()
                ->when($this->landlordFilterSearch, fn ($q) => $q->where(fn ($qq) => $qq
                    ->where('name', 'like', '%' . $this->landlordFilterSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->landlordFilterSearch . '%')))
                ->orderBy('name')->limit(30)->get(['id', 'name', 'phone'])
            : collect();

        // ─── Cascading location picker data ──
        $selectedCountry = $this->country_id ? countries::find($this->country_id) : null;
        $selectedRegion = $this->region_id ? regions::find($this->region_id) : null;
        $selectedDistrict = $this->district_id ? districts::find($this->district_id) : null;
        $selectedWard = $this->ward_id ? wards::find($this->ward_id) : null;
        $selectedStreet = $this->street_id ? street::find($this->street_id) : null;

        $countryResults = $this->country_id
            ? collect()
            : countries::query()
                ->when($this->countrySearch, fn ($q) => $q->where('name', 'like', '%' . $this->countrySearch . '%'))
                ->orderBy('name')->limit(30)->get(['id', 'name']);

        $regionResults = (!$this->country_id || $this->region_id)
            ? collect()
            : regions::where('country_id', $this->country_id)
                ->when($this->regionSearch, fn ($q) => $q->where('name', 'like', '%' . $this->regionSearch . '%'))
                ->orderBy('name')->limit(30)->get(['id', 'name']);

        $districtResults = (!$this->region_id || $this->district_id)
            ? collect()
            : districts::where('region_id', $this->region_id)
                ->when($this->districtSearch, fn ($q) => $q->where('name', 'like', '%' . $this->districtSearch . '%'))
                ->orderBy('name')->limit(30)->get(['id', 'name']);

        $wardResults = (!$this->district_id || $this->ward_id)
            ? collect()
            : wards::where('district_id', $this->district_id)
                ->when($this->wardSearch, fn ($q) => $q->where('name', 'like', '%' . $this->wardSearch . '%'))
                ->orderBy('name')->limit(30)->get(['id', 'name']);

        $streetResults = (!$this->ward_id || $this->street_id)
            ? collect()
            : street::where('ward_id', $this->ward_id)
                ->when($this->streetSearch, fn ($q) => $q->where('name', 'like', '%' . $this->streetSearch . '%'))
                ->orderBy('name')->limit(30)->get(['id', 'name']);

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
            'landlordCount' => $landlordCount,
            'selectedFormLandlord' => $selectedFormLandlord,
            'landlordResults' => $landlordResults,
            'selectedFilterLandlord' => $selectedFilterLandlord,
            'landlordFilterResults' => $landlordFilterResults,
            'selectedCountry' => $selectedCountry,
            'selectedRegion' => $selectedRegion,
            'selectedDistrict' => $selectedDistrict,
            'selectedWard' => $selectedWard,
            'selectedStreet' => $selectedStreet,
            'countryResults' => $countryResults,
            'regionResults' => $regionResults,
            'districtResults' => $districtResults,
            'wardResults' => $wardResults,
            'streetResults' => $streetResults,
        ]);
    }
}
