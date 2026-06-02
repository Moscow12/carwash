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

    // Cascading lists
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

    // ─── Reactivity ─────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingLoginFilter(): void { $this->resetPage(); }
    public function updatedSelectedBusiness(): void { $this->resetPage(); }

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
        ]);
        $this->status = 'active';
        $this->allDistricts = collect();
        $this->allWards = collect();
        $this->allStreets = collect();
        $this->resetValidation();
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render()
    {
        // Users available to link (customers + owners + staff who don't already have a landlord
        // row on this business). Limit to reasonable size; user can type a UUID for edge cases.
        $linkableUsers = $this->selectedBusiness
            ? User::query()
                ->whereDoesntHave('landlordProfiles', fn ($q) => $q->where('business_id', $this->selectedBusiness)
                    ->when($this->landlordId, fn ($qq) => $qq->where('id', '!=', $this->landlordId)))
                ->orderBy('name')
                ->limit(200)
                ->get(['id', 'name', 'email'])
            : collect();

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
            'linkableUsers' => $linkableUsers,
        ]);
    }
}
