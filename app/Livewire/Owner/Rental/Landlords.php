<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\countries;
use App\Models\districts;
use App\Models\Landlord;
use App\Models\regions;
use App\Models\street;
use App\Models\User;
use App\Models\wards;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class Landlords extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $loginFilter = ''; // '', 'linked', 'external'

    // Modal state
    public $showModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $landlordId = null;
    public $viewLandlord = null;

    // Form fields
    public $name = '';
    public $phone = '';
    public $email = '';
    public $address = '';
    public $user_id = '';
    public $country_id = '';
    public $region_id = '';
    public $district_id = '';
    public $ward_id = '';
    public $street_id = '';
    public $status = 'active';

    // ─── Search-picker state (user + cascading location) ─────────
    // Search terms
    public $userSearch = '';
    public $countrySearch = '';
    public $regionSearch = '';
    public $districtSearch = '';
    public $wardSearch = '';
    public $streetSearch = '';

    // Dropdown visibility
    public $showUserDropdown = false;
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

    // ─── Reactivity ─────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingLoginFilter(): void { $this->resetPage(); }
    public function updatedSelectedBusiness(): void { $this->resetPage(); }

    // ─── Platform-login (user) picker ───────────────────────────

    public function selectUser(string $id): void
    {
        $this->user_id = $id;
        $this->userSearch = '';
        $this->showUserDropdown = false;
    }

    public function clearUser(): void
    {
        $this->reset(['user_id', 'userSearch']);
    }

    // ─── Cascading location pickers ─────────────────────────────

    public function selectCountry(string $id): void
    {
        $this->country_id = $id;
        $this->countrySearch = '';
        $this->showCountryDropdown = false;
        // A new country invalidates everything below it
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

    // ─── Modal: open/close ──────────────────────────────────────

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

        $landlord = $this->scopedQuery()->find($id);
        if (!$landlord) return;

        $this->landlordId = $landlord->id;
        $this->name = $landlord->name;
        $this->phone = $landlord->phone;
        $this->email = $landlord->email ?: '';
        $this->address = $landlord->address ?: '';
        $this->user_id = $landlord->user_id ?: '';
        $this->country_id = $landlord->country_id ?: '';
        $this->region_id = $landlord->region_id ?: '';
        $this->district_id = $landlord->district_id ?: '';
        $this->ward_id = $landlord->ward_id ?: '';
        $this->street_id = $landlord->street_id ?: '';
        $this->status = $landlord->status;

        // Older records may hold a region without a country — backfill so the
        // cascade displays correctly (the country picker drives the region list).
        if (!$this->country_id && $this->region_id) {
            $this->country_id = regions::find($this->region_id)?->country_id ?: '';
        }

        $this->reset([
            'userSearch', 'countrySearch', 'regionSearch',
            'districtSearch', 'wardSearch', 'streetSearch',
        ]);

        $this->editMode = true;
        $this->showModal = true;
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $this->viewLandlord = $this->scopedQuery()
            ->with(['user:id,name,email', 'region:id,name', 'district:id,name', 'ward:id,name'])
            ->withCount(['properties', 'tenancyAgreements'])
            ->find($id);

        if ($this->viewLandlord) {
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
        $this->viewLandlord = null;
    }

    // ─── Save / Delete / Toggle ─────────────────────────────────

    public function saveLandlord(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        // Empty-string selects → null so optional FK columns / unique constraint behave
        foreach (['user_id', 'email', 'address', 'country_id', 'region_id', 'district_id', 'ward_id', 'street_id'] as $opt) {
            if ($this->$opt === '') {
                $this->$opt = null;
            }
        }

        $businessId = $this->selectedBusiness;
        $landlordId = $this->landlordId;

        // user_id must be unique per business (matches the DB unique key)
        $userUniqueRule = function (string $attribute, $value, \Closure $fail) use ($businessId, $landlordId) {
            unset($attribute);
            if ($value === null) return;
            $exists = Landlord::where('business_id', $businessId)
                ->where('user_id', $value)
                ->when($landlordId, fn ($q) => $q->where('id', '!=', $landlordId))
                ->exists();
            if ($exists) {
                $fail('This user is already registered as a landlord for this business.');
            }
        };

        $data = $this->validate([
            'name' => 'required|string|min:2|max:150',
            'phone' => 'required|string|min:7|max:25',
            'email' => 'nullable|email|max:200',
            'address' => 'nullable|string|max:255',
            'user_id' => ['nullable', 'uuid', 'exists:users,id', $userUniqueRule],
            'country_id' => 'nullable|uuid|exists:countries,id',
            'region_id' => 'nullable|uuid|exists:regions,id',
            'district_id' => 'nullable|uuid|exists:districts,id',
            'ward_id' => 'nullable|uuid|exists:wards,id',
            'street_id' => 'nullable|uuid|exists:streets,id',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            if ($this->editMode && $this->landlordId) {
                $landlord = $this->scopedQuery()->find($this->landlordId);
                if (!$landlord) return;
                $landlord->update($data);
                session()->flash('message', 'Landlord updated successfully.');
            } else {
                Landlord::create($data + ['business_id' => $businessId]);
                session()->flash('message', 'Landlord created successfully.');
            }
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving landlord: ' . $e->getMessage());
        }
    }

    public function deleteLandlord(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $landlord = $this->scopedQuery()->find($id);
        if (!$landlord) return;

        if ($landlord->properties()->count() > 0) {
            session()->flash('error', 'Cannot delete a landlord that owns properties. Deactivate instead.');
            return;
        }

        if ($landlord->tenancyAgreements()->count() > 0) {
            session()->flash('error', 'Cannot delete a landlord with tenancy agreements on record.');
            return;
        }

        try {
            $landlord->delete();
            session()->flash('message', 'Landlord deleted.');
        } catch (\Throwable) {
            session()->flash('error', 'Error deleting landlord.');
        }
    }

    public function toggleStatus(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $landlord = $this->scopedQuery()->find($id);
        if (!$landlord) return;

        $landlord->update([
            'status' => $landlord->status === 'active' ? 'inactive' : 'active',
        ]);
        session()->flash('message', 'Landlord status updated.');
    }

    /**
     * Promote myself (the logged-in owner) as a landlord on this business in one click.
     */
    public function quickAddSelf(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $me = Auth::user();
        $exists = Landlord::where('business_id', $this->selectedBusiness)
            ->where('user_id', $me->id)
            ->exists();

        if ($exists) {
            session()->flash('error', 'You are already a landlord on this business.');
            return;
        }

        Landlord::create([
            'business_id' => $this->selectedBusiness,
            'user_id' => $me->id,
            'name' => $me->name,
            'phone' => $me->phone ?: '-',
            'email' => $me->email,
            'status' => 'active',
        ]);

        session()->flash('message', 'You were added as a landlord on this business.');
    }

    // ─── Helpers ────────────────────────────────────────────────

    protected function ensureBusinessSelected(): bool
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a rental business first.');
            return false;
        }
        return true;
    }

    protected function scopedQuery()
    {
        return Landlord::where('business_id', $this->selectedBusiness);
    }

    public function resetForm(): void
    {
        $this->reset([
            'landlordId', 'editMode',
            'name', 'phone', 'email', 'address', 'user_id',
            'country_id', 'region_id', 'district_id', 'ward_id', 'street_id',
            'userSearch', 'countrySearch', 'regionSearch',
            'districtSearch', 'wardSearch', 'streetSearch',
            'showUserDropdown', 'showCountryDropdown', 'showRegionDropdown',
            'showDistrictDropdown', 'showWardDropdown', 'showStreetDropdown',
        ]);
        $this->status = 'active';
        $this->resetValidation();
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render()
    {
        // Users available to link (customers + owners + staff who don't already have a landlord
        // row on this business). Filtered by the picker's search term.
        $selectedUser = $this->user_id ? User::find($this->user_id) : null;
        $userResults = ($this->selectedBusiness && !$this->user_id)
            ? User::query()
                ->whereDoesntHave('landlordProfiles', fn ($q) => $q->where('business_id', $this->selectedBusiness)
                    ->when($this->landlordId, fn ($qq) => $qq->where('id', '!=', $this->landlordId)))
                ->when($this->userSearch, fn ($q) => $q->where(fn ($qq) => $qq
                    ->where('name', 'like', '%' . $this->userSearch . '%')
                    ->orWhere('email', 'like', '%' . $this->userSearch . '%')))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'email'])
            : collect();

        // ─── Cascading location picker data (selected + filtered results) ──
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
            $landlords = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'linked' => 0];
        } else {
            $landlords = $this->scopedQuery()
                ->with(['user:id,name,email', 'region:id,name', 'district:id,name'])
                ->withCount(['properties', 'tenancyAgreements'])
                ->when($this->search, function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('name', 'like', '%' . $this->search . '%')
                           ->orWhere('phone', 'like', '%' . $this->search . '%')
                           ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->loginFilter === 'linked', fn ($q) => $q->whereNotNull('user_id'))
                ->when($this->loginFilter === 'external', fn ($q) => $q->whereNull('user_id'))
                ->latest()
                ->paginate(12);

            $base = $this->scopedQuery();
            $stats = [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('status', 'active')->count(),
                'inactive' => (clone $base)->where('status', 'inactive')->count(),
                'linked' => (clone $base)->whereNotNull('user_id')->count(),
            ];
        }

        return view('livewire.owner.rental.landlords', [
            'landlords' => $landlords,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'selectedUser' => $selectedUser,
            'userResults' => $userResults,
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
