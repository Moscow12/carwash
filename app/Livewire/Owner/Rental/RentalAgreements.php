<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\countries;
use App\Models\customers;
use App\Models\districts;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\regions;
use App\Models\RentalUnit;
use App\Models\street;
use App\Models\TenancyAgreement;
use App\Models\wards;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class RentalAgreements extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $landlordFilter = '';
    public $propertyFilter = '';

    // Modal state
    public $showModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $agreementId = null;
    public $viewAgreement = null;

    // Form fields
    public $landlord_id = '';
    public $property_id = '';
    public $rental_unit_id = '';
    public $customer_id = '';
    public $start_date = '';
    public $end_date = '';
    public $duration_months = '';
    public $rent_amount = '';
    public $deposit_paid = 0;
    public $payment_frequency = 'monthly';
    public $agreement_status = 'draft';
    public $notes = '';

    // Quick-add-tenant
    public $quickTenant = false;
    public $qt_name = '';
    public $qt_phone = '';
    public $qt_email = '';

    // Cascading dropdown sources
    public $propertyOptions;
    public $unitOptions;

    // ─── Landlord location pickers (country → region → district → ward → street) ──
    public $country_id = '';
    public $region_id = '';
    public $district_id = '';
    public $ward_id = '';
    public $street_id = '';

    // Search terms
    public $countrySearch = '';
    public $regionSearch = '';
    public $districtSearch = '';
    public $wardSearch = '';
    public $streetSearch = '';

    // Dropdown visibility
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

        $this->propertyOptions = collect();
        $this->unitOptions = collect();
        $this->start_date = now()->toDateString();
    }

    // ─── Reactivity ──────────────────────────────────────────────

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatusFilter(): void { $this->resetPage(); }
    public function updatingLandlordFilter(): void { $this->resetPage(); }
    public function updatingPropertyFilter(): void { $this->resetPage(); }
    public function updatedSelectedBusiness(): void
    {
        $this->reset(['landlordFilter', 'propertyFilter']);
        $this->resetPage();
    }

    public function updatedLandlordId($value): void
    {
        $this->propertyOptions = $value
            ? Property::where('landlord_id', $value)->where('status', 'active')->orderBy('property_name')->get(['id', 'property_name'])
            : collect();
        $this->property_id = '';
        $this->rental_unit_id = '';
        $this->unitOptions = collect();

        // Pre-fill the landlord's stored location into the pickers
        $this->loadLandlordLocation($value ?: null);
    }

    public function updatedPropertyId($value): void
    {
        // Show vacant or reserved units; in edit mode also include the currently-selected unit
        $query = RentalUnit::where('property_id', $value);
        if ($this->editMode && $this->rental_unit_id) {
            $query->where(function ($q) {
                $q->whereIn('status', ['vacant', 'reserved'])
                  ->orWhere('id', $this->rental_unit_id);
            });
        } else {
            $query->whereIn('status', ['vacant', 'reserved']);
        }

        $this->unitOptions = $value
            ? $query->orderBy('unit_number')->get(['id', 'unit_number', 'monthly_rent', 'deposit_amount', 'status'])
            : collect();
        $this->rental_unit_id = '';
    }

    public function updatedRentalUnitId($value): void
    {
        if (!$value) return;
        $unit = RentalUnit::find($value);
        if ($unit) {
            // Pre-fill the rent from the unit if the user hasn't typed anything yet
            if ($this->rent_amount === '' || (float)$this->rent_amount === 0.0) {
                $this->rent_amount = $unit->monthly_rent;
            }
            if ((float)$this->deposit_paid === 0.0) {
                $this->deposit_paid = $unit->deposit_amount;
            }
        }
    }

    // ─── Term: duration drives end date ──────────────────────────

    public function updatedDurationMonths(): void { $this->recalcEndDate(); }
    public function updatedStartDate(): void { $this->recalcEndDate(); }

    /**
     * Given a start date and a whole-month duration, compute the end date as the
     * last day before the term rolls over (start + N months − 1 day).
     */
    protected function recalcEndDate(): void
    {
        $months = (int) $this->duration_months;
        if (!$this->start_date || $months < 1) {
            return;
        }

        try {
            $this->end_date = Carbon::parse($this->start_date)
                ->addMonths($months)
                ->subDay()
                ->toDateString();
        } catch (\Throwable) {
            // leave end_date untouched on an unparseable start date
        }
    }

    // ─── Landlord location pickers ───────────────────────────────

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

    /** Pre-fill the location pickers from a landlord's stored address. */
    protected function loadLandlordLocation(?string $landlordId): void
    {
        $this->reset([
            'country_id', 'region_id', 'district_id', 'ward_id', 'street_id',
            'countrySearch', 'regionSearch', 'districtSearch', 'wardSearch', 'streetSearch',
        ]);

        if (!$landlordId) return;

        $landlord = Landlord::find($landlordId);
        if (!$landlord) return;

        $this->country_id = $landlord->country_id ?? '';
        $this->region_id = $landlord->region_id ?? '';
        $this->district_id = $landlord->district_id ?? '';
        $this->ward_id = $landlord->ward_id ?? '';
        $this->street_id = $landlord->street_id ?? '';
    }

    // ─── Modal: open/close ───────────────────────────────────────

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

        $agreement = $this->scopedQuery()->find($id);
        if (!$agreement) return;

        $this->agreementId = $agreement->id;
        $this->landlord_id = $agreement->landlord_id;
        $this->updatedLandlordId($this->landlord_id);
        $this->property_id = $agreement->property_id;
        $this->updatedPropertyId($this->property_id);
        $this->rental_unit_id = $agreement->rental_unit_id;
        $this->customer_id = $agreement->customer_id;
        $this->start_date = $agreement->start_date?->toDateString();
        $this->end_date = $agreement->end_date?->toDateString();
        $this->duration_months = $agreement->durationInMonths() ?: '';
        $this->rent_amount = $agreement->rent_amount;
        $this->deposit_paid = $agreement->deposit_paid;
        $this->payment_frequency = $agreement->payment_frequency;
        $this->agreement_status = $agreement->agreement_status;
        $this->notes = $agreement->notes ?? '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $this->viewAgreement = $this->scopedQuery()
            ->with([
                'customer:id,name,phone,email',
                'landlord:id,name,phone',
                'property:id,property_name',
                'unit:id,unit_number,monthly_rent',
                'creator:id,name',
            ])
            ->withCount(['rentPayments', 'utilityBills', 'maintenanceRequests'])
            ->withSum('rentPayments as total_paid', 'amount_paid')
            ->find($id);

        if ($this->viewAgreement) {
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
        $this->viewAgreement = null;
    }

    // ─── Save / Terminate / Delete ──────────────────────────────

    public function saveAgreement(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        // If a duration was entered, derive end_date from it so the two always agree.
        if ($this->duration_months !== '' && $this->duration_months !== null) {
            $this->recalcEndDate();
        }

        // Empty → null for nullable fields
        foreach (['end_date', 'duration_months', 'notes'] as $opt) {
            if ($this->$opt === '') {
                $this->$opt = null;
            }
        }

        $businessId = $this->selectedBusiness;
        $agreementId = $this->agreementId;

        // Inline quick-add tenant
        if ($this->quickTenant) {
            $tenantData = $this->validate([
                'qt_name' => 'required|string|min:2|max:150',
                'qt_phone' => 'required|string|min:7|max:25',
                'qt_email' => 'nullable|email|max:200',
            ]);

            $newTenant = customers::create([
                'business_id' => $businessId,
                'user_id' => Auth::id(),
                'name' => $tenantData['qt_name'],
                'phone' => $tenantData['qt_phone'],
                'email' => $tenantData['qt_email'] ?: null,
                'status' => 'active',
            ]);
            $this->customer_id = $newTenant->id;
            $this->quickTenant = false;
        }

        // Property must belong to the selected business
        $propertyScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            $belongs = Property::where('id', $value)
                ->whereHas('landlord', fn ($q) => $q->where('business_id', $businessId))
                ->exists();
            if (!$belongs) $fail('The selected property does not belong to this business.');
        };

        // Unit must belong to the chosen property AND must not have a different active agreement
        $unitScope = function (string $a, $value, \Closure $fail) use ($agreementId) {
            unset($a);
            if (!RentalUnit::where('id', $value)->where('property_id', $this->property_id)->exists()) {
                $fail('The selected unit does not belong to that property.');
                return;
            }
            if ($this->agreement_status === 'active') {
                $clash = TenancyAgreement::where('rental_unit_id', $value)
                    ->where('agreement_status', 'active')
                    ->when($agreementId, fn ($q) => $q->where('id', '!=', $agreementId))
                    ->exists();
                if ($clash) $fail('Another active agreement already exists for this unit.');
            }
        };

        // Customer must belong to this business
        $customerScope = function (string $a, $value, \Closure $fail) use ($businessId) {
            unset($a);
            if (!customers::where('id', $value)->where('business_id', $businessId)->exists()) {
                $fail('Tenant must be a customer of this business.');
            }
        };

        $data = $this->validate([
            'landlord_id' => 'required|uuid|exists:landlords,id',
            'property_id' => ['required', 'uuid', $propertyScope],
            'rental_unit_id' => ['required', 'uuid', $unitScope],
            'customer_id' => ['required', 'uuid', $customerScope],
            'start_date' => 'required|date',
            'duration_months' => 'nullable|integer|min:1|max:600',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'rent_amount' => 'required|numeric|min:0.01',
            'deposit_paid' => 'required|numeric|min:0',
            'payment_frequency' => 'required|in:monthly,quarterly,semi_annual,annual',
            'agreement_status' => 'required|in:draft,active,terminated,expired,renewed',
            'notes' => 'nullable|string|max:2000',
            'country_id' => 'nullable|uuid|exists:countries,id',
            'region_id' => 'nullable|uuid|exists:regions,id',
            'district_id' => 'nullable|uuid|exists:districts,id',
            'ward_id' => 'nullable|uuid|exists:wards,id',
            'street_id' => 'nullable|uuid|exists:streets,id',
        ]);

        // Location is stored against the landlord, not the agreement.
        $location = array_filter(
            [
                'country_id' => $data['country_id'] ?? null,
                'region_id' => $data['region_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'ward_id' => $data['ward_id'] ?? null,
                'street_id' => $data['street_id'] ?? null,
            ],
            fn ($v) => $v !== null && $v !== ''
        );
        unset($data['country_id'], $data['region_id'], $data['district_id'], $data['ward_id'], $data['street_id']);

        try {
            DB::transaction(function () use ($data, $location) {
                // Persist the chosen location onto the landlord record
                if ($location) {
                    Landlord::where('id', $data['landlord_id'])->update($location);
                }

                if ($this->editMode && $this->agreementId) {
                    $agreement = $this->scopedQuery()->find($this->agreementId);
                    if (!$agreement) return;

                    $previousStatus = $agreement->agreement_status;
                    $previousUnitId = $agreement->rental_unit_id;

                    $agreement->update($data + ['created_by' => $agreement->created_by ?? Auth::id()]);

                    // If the unit was swapped or the status changed, re-sync unit occupancy
                    if ($previousUnitId !== $agreement->rental_unit_id) {
                        $this->refreshUnitStatus($previousUnitId);
                    }
                    $this->refreshUnitStatus($agreement->rental_unit_id);

                    session()->flash('message', 'Agreement updated.');
                } else {
                    $agreement = TenancyAgreement::create($data + ['created_by' => Auth::id()]);
                    $this->refreshUnitStatus($agreement->rental_unit_id);
                    session()->flash('message', 'Agreement created.');
                }
            });
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving agreement: ' . $e->getMessage());
        }
    }

    public function activate(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $agreement = $this->scopedQuery()->find($id);
        if (!$agreement) return;

        // Confirm the unit is still free for this agreement
        $clash = TenancyAgreement::where('rental_unit_id', $agreement->rental_unit_id)
            ->where('agreement_status', 'active')
            ->where('id', '!=', $agreement->id)
            ->exists();
        if ($clash) {
            session()->flash('error', 'Cannot activate — another active agreement holds this unit.');
            return;
        }

        DB::transaction(function () use ($agreement) {
            $agreement->update(['agreement_status' => 'active']);
            $this->refreshUnitStatus($agreement->rental_unit_id);
        });
        session()->flash('message', 'Agreement activated.');
    }

    public function terminate(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $agreement = $this->scopedQuery()->find($id);
        if (!$agreement) return;

        DB::transaction(function () use ($agreement) {
            $agreement->update([
                'agreement_status' => 'terminated',
                'end_date' => $agreement->end_date ?: now()->toDateString(),
            ]);
            $this->refreshUnitStatus($agreement->rental_unit_id);
        });
        session()->flash('message', 'Agreement terminated; unit released.');
    }

    public function deleteAgreement(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $agreement = $this->scopedQuery()->find($id);
        if (!$agreement) return;

        if ($agreement->rentPayments()->exists()) {
            session()->flash('error', 'Cannot delete — rent payments are recorded against this agreement.');
            return;
        }

        try {
            $unitId = $agreement->rental_unit_id;
            DB::transaction(function () use ($agreement, $unitId) {
                $agreement->delete();
                $this->refreshUnitStatus($unitId);
            });
            session()->flash('message', 'Agreement deleted.');
        } catch (\Throwable) {
            session()->flash('error', 'Error deleting agreement.');
        }
    }

    // ─── Helpers ────────────────────────────────────────────────

    /**
     * Reconcile rental_units.status with the presence of an active agreement.
     * - Active agreement found → 'occupied'
     * - Otherwise leave 'maintenance'/'reserved' alone, but flip 'occupied' → 'vacant'
     */
    protected function refreshUnitStatus(?string $unitId): void
    {
        if (!$unitId) return;
        $unit = RentalUnit::find($unitId);
        if (!$unit) return;

        $hasActive = TenancyAgreement::where('rental_unit_id', $unitId)
            ->where('agreement_status', 'active')
            ->exists();

        if ($hasActive && $unit->status !== 'occupied') {
            $unit->update(['status' => 'occupied']);
        } elseif (!$hasActive && $unit->status === 'occupied') {
            $unit->update(['status' => 'vacant']);
        }
    }

    protected function ensureBusinessSelected(): bool
    {
        if (!$this->selectedBusiness) {
            session()->flash('error', 'Please select a rental business first.');
            return false;
        }
        return true;
    }

    /** Agreements joined through unit→property→landlord so business scoping is bullet-proof. */
    protected function scopedQuery()
    {
        return TenancyAgreement::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness));
    }

    public function resetForm(): void
    {
        $this->reset([
            'agreementId', 'editMode',
            'landlord_id', 'property_id', 'rental_unit_id', 'customer_id',
            'end_date', 'duration_months', 'rent_amount', 'deposit_paid', 'notes',
            'quickTenant', 'qt_name', 'qt_phone', 'qt_email',
            'country_id', 'region_id', 'district_id', 'ward_id', 'street_id',
            'countrySearch', 'regionSearch', 'districtSearch', 'wardSearch', 'streetSearch',
            'showCountryDropdown', 'showRegionDropdown', 'showDistrictDropdown',
            'showWardDropdown', 'showStreetDropdown',
        ]);
        $this->deposit_paid = 0;
        $this->payment_frequency = 'monthly';
        $this->agreement_status = 'draft';
        $this->start_date = now()->toDateString();
        $this->propertyOptions = collect();
        $this->unitOptions = collect();
        $this->resetValidation();
    }

    // ─── Render ─────────────────────────────────────────────────

    public function render()
    {
        $landlords = $this->selectedBusiness
            ? Landlord::where('business_id', $this->selectedBusiness)->active()->orderBy('name')->get(['id', 'name'])
            : collect();

        $tenants = $this->selectedBusiness
            ? customers::where('business_id', $this->selectedBusiness)->orderBy('name')->limit(500)->get(['id', 'name', 'phone'])
            : collect();

        // Properties for the filter dropdown — across all landlords of this business
        $filterProperties = $this->selectedBusiness
            ? Property::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->orderBy('property_name')->get(['id', 'property_name'])
            : collect();

        if (!$this->selectedBusiness) {
            $agreements = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $stats = ['total' => 0, 'active' => 0, 'draft' => 0, 'expired' => 0, 'terminated' => 0, 'monthly_revenue' => 0];
        } else {
            $agreements = $this->scopedQuery()
                ->with([
                    'customer:id,name,phone',
                    'unit:id,unit_number,property_id',
                    'unit.property:id,property_name',
                    'landlord:id,name',
                ])
                ->when($this->search, function ($q) {
                    $q->where(function ($qq) {
                        $qq->whereHas('customer', fn ($c) => $c->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('phone', 'like', '%' . $this->search . '%'))
                           ->orWhereHas('unit', fn ($u) => $u->where('unit_number', 'like', '%' . $this->search . '%'));
                    });
                })
                ->when($this->statusFilter, fn ($q) => $q->where('agreement_status', $this->statusFilter))
                ->when($this->landlordFilter, fn ($q) => $q->where('landlord_id', $this->landlordFilter))
                ->when($this->propertyFilter, fn ($q) => $q->where('property_id', $this->propertyFilter))
                ->latest('start_date')
                ->paginate(12);

            $base = $this->scopedQuery();
            $stats = [
                'total' => (clone $base)->count(),
                'active' => (clone $base)->where('agreement_status', 'active')->count(),
                'draft' => (clone $base)->where('agreement_status', 'draft')->count(),
                'expired' => (clone $base)->where('agreement_status', 'expired')->count(),
                'terminated' => (clone $base)->where('agreement_status', 'terminated')->count(),
                'monthly_revenue' => (clone $base)->where('agreement_status', 'active')->sum('rent_amount'),
            ];
        }

        // ─── Location picker data (selected + cascading search results) ──
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

        // ─── Live term summary for the form ──────────────────────
        // total contract = monthly rent × duration; per-period charge = rent × interval
        $months = (int) $this->duration_months;
        $rent = (float) ($this->rent_amount ?: 0);
        $interval = match ($this->payment_frequency) {
            'quarterly' => 3, 'semi_annual' => 6, 'annual' => 12, default => 1,
        };
        $termSummary = [
            'months' => $months,
            'periods' => $months > 0 ? (int) ceil($months / $interval) : 0,
            'period_charge' => $rent * $interval,
            'total' => $months > 0 ? $rent * $months : 0,
        ];

        return view('livewire.owner.rental.rental-agreements', [
            'agreements' => $agreements,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'landlords' => $landlords,
            'tenants' => $tenants,
            'filterProperties' => $filterProperties,
            'termSummary' => $termSummary,
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
