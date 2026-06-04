<?php

namespace App\Livewire\Owner\Rental;

use App\Models\Business;
use App\Models\Property;
use App\Models\RentalUnit;
use App\Models\UnitFeature;
use App\Models\UnitImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app-owner')]
class RentalUnits extends Component
{
    use WithFileUploads;
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public ?string $selectedBusiness = null;
    public \Illuminate\Support\Collection $ownerBusinesses;

    // Filters
    public $search = '';
    public $statusFilter = '';
    public $typeFilter = '';
    public $propertyFilter = '';

    // Modal state
    public $showModal = false;
    public $showViewModal = false;
    public $editMode = false;
    public $unitId = null;
    public $viewUnit = null;

    // Form fields (mirror rental_units table)
    public $property_id = '';
    public $unit_number = '';
    public $unit_type = 'single';
    public $floor_no = '';
    public $bedrooms = 1;
    public $bathrooms = 1;
    public $has_electricity = true;
    public $has_water = true;
    public $has_furniture = false;
    public $monthly_rent = '';
    public $deposit_amount = '';
    // 'occupied' is intentionally NOT in this list — it's derived from active tenancy
    public $status = 'vacant';
    public $is_published = false;
    public $description = '';

    // Pivot: feature IDs the user has ticked
    public $selectedFeatures = [];

    // New images to upload (Livewire file objects)
    public $newImages = [];

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
    public function updatingPropertyFilter(): void { $this->resetPage(); }
    public function updatedSelectedBusiness(): void
    {
        $this->reset(['propertyFilter']);
        $this->resetPage();
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

        $unit = $this->scopedQuery()->with('features:id')->find($id);
        if (!$unit) return;

        $this->unitId = $unit->id;
        $this->property_id = $unit->property_id;
        $this->unit_number = $unit->unit_number;
        $this->unit_type = $unit->unit_type;
        $this->floor_no = $unit->floor_no ?? '';
        $this->bedrooms = $unit->bedrooms;
        $this->bathrooms = $unit->bathrooms;
        $this->has_electricity = (bool) $unit->has_electricity;
        $this->has_water = (bool) $unit->has_water;
        $this->has_furniture = (bool) $unit->has_furniture;
        $this->monthly_rent = $unit->monthly_rent;
        $this->deposit_amount = $unit->deposit_amount;
        // If the unit is currently occupied, status stays read-only-ish — but allow
        // the user to flip between vacant/maintenance/reserved freely.
        $this->status = $unit->status === 'occupied' ? 'vacant' : $unit->status;
        $this->is_published = (bool) $unit->is_published;
        $this->description = $unit->description ?? '';
        $this->selectedFeatures = $unit->features->pluck('id')->toArray();
        $this->editMode = true;
        $this->showModal = true;
    }

    public function openViewModal(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $this->viewUnit = $this->scopedQuery()
            ->with(['property:id,property_name,property_type', 'features', 'images', 'activeAgreement.customer:id,name'])
            ->find($id);

        if ($this->viewUnit) {
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
        $this->viewUnit = null;
    }

    // ─── Save / Delete / Toggle ──────────────────────────────────

    public function saveUnit(): void
    {
        if (!$this->ensureBusinessSelected()) return;

        // Empty string → null
        foreach (['floor_no', 'description'] as $opt) {
            if ($this->$opt === '') {
                $this->$opt = null;
            }
        }

        $businessId = $this->selectedBusiness;

        // The selected property must belong to a landlord of this business
        $propertyScope = function (string $attribute, $value, \Closure $fail) use ($businessId) {
            unset($attribute);
            $belongs = Property::where('id', $value)
                ->whereHas('landlord', fn ($q) => $q->where('business_id', $businessId))
                ->exists();
            if (!$belongs) {
                $fail('The selected property does not belong to this business.');
            }
        };

        // unit_number must be unique within the property (DB also enforces this)
        $unitId = $this->unitId;
        $uniqueWithinProperty = function (string $attribute, $value, \Closure $fail) use ($unitId) {
            unset($attribute);
            $exists = RentalUnit::where('property_id', $this->property_id)
                ->where('unit_number', $value)
                ->when($unitId, fn ($q) => $q->where('id', '!=', $unitId))
                ->exists();
            if ($exists) {
                $fail('This unit number already exists on that property.');
            }
        };

        $data = $this->validate([
            'property_id' => ['required', 'uuid', $propertyScope],
            'unit_number' => ['required', 'string', 'max:50', $uniqueWithinProperty],
            'unit_type' => 'required|in:single,double,full_house,apartment',
            'floor_no' => 'nullable|integer|min:-5|max:200',
            'bedrooms' => 'required|integer|min:0|max:50',
            'bathrooms' => 'required|integer|min:0|max:50',
            'has_electricity' => 'boolean',
            'has_water' => 'boolean',
            'has_furniture' => 'boolean',
            'monthly_rent' => 'required|numeric|min:0',
            'deposit_amount' => 'required|numeric|min:0',
            'status' => 'required|in:vacant,maintenance,reserved',
            'is_published' => 'boolean',
            'description' => 'nullable|string|max:1000',
            'selectedFeatures' => 'array',
            'selectedFeatures.*' => 'uuid|exists:unit_features,id',
            'newImages.*' => 'nullable|image|max:4096',
        ]);

        try {
            if ($this->editMode && $this->unitId) {
                $unit = $this->scopedQuery()->find($this->unitId);
                if (!$unit) return;

                // Only overwrite status if the unit isn't currently occupied
                // (occupied is derived from active tenancy; admin should release agreement first)
                $payload = collect($data)
                    ->except(['selectedFeatures', 'newImages'])
                    ->toArray();
                if ($unit->status === 'occupied') {
                    unset($payload['status']);
                }
                $unit->update($payload);
            } else {
                $unit = RentalUnit::create(
                    collect($data)->except(['selectedFeatures', 'newImages'])->toArray()
                );
            }

            // Sync features
            $unit->features()->sync($this->selectedFeatures ?? []);

            // Persist new uploaded images
            if (!empty($this->newImages)) {
                foreach ($this->newImages as $img) {
                    if (!$img) continue;
                    $path = $img->store('rental-units', 'public');
                    UnitImage::create([
                        'rental_unit_id' => $unit->id,
                        'image_url' => $path,
                        'is_primary' => $unit->images()->count() === 0,
                    ]);
                }
            }

            session()->flash('message', $this->editMode ? 'Unit updated successfully.' : 'Unit created successfully.');
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', 'Error saving unit: ' . $e->getMessage());
        }
    }

    public function deleteUnit(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;
        $unit = $this->scopedQuery()->find($id);
        if (!$unit) return;

        if ($unit->status === 'occupied' || $unit->tenancyAgreements()->whereIn('agreement_status', ['active', 'draft'])->exists()) {
            session()->flash('error', 'Cannot delete a unit with active or draft tenancy agreements.');
            return;
        }

        try {
            // Drop stored images from disk before cascading DB delete
            foreach ($unit->images as $img) {
                Storage::disk('public')->delete($img->image_url);
            }
            $unit->delete();
            session()->flash('message', 'Unit deleted.');
        } catch (\Throwable) {
            session()->flash('error', 'Error deleting unit.');
        }
    }

    public function setStatus(string $id, string $status): void
    {
        if (!in_array($status, ['vacant', 'maintenance', 'reserved'], true)) return;
        if (!$this->ensureBusinessSelected()) return;

        $unit = $this->scopedQuery()->find($id);
        if (!$unit) return;

        if ($unit->status === 'occupied') {
            session()->flash('error', 'Unit is occupied — terminate the tenancy agreement first.');
            return;
        }

        $unit->update(['status' => $status]);
        session()->flash('message', "Unit marked as {$status}.");
    }

    public function togglePublish(string $id): void
    {
        if (!$this->ensureBusinessSelected()) return;

        $unit = $this->scopedQuery()->find($id);
        if (!$unit) return;

        $unit->update(['is_published' => ! $unit->is_published]);
        session()->flash('message', $unit->is_published
            ? 'Unit published to the marketplace.'
            : 'Unit unpublished from the marketplace.');
    }

    public function deleteImage(string $imageId): void
    {
        if (!$this->ensureBusinessSelected() || !$this->unitId) return;

        $img = UnitImage::where('rental_unit_id', $this->unitId)->find($imageId);
        if (!$img) return;

        Storage::disk('public')->delete($img->image_url);
        $img->delete();
        session()->flash('message', 'Image removed.');
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
     * Units scoped through property→landlord→business so a tampered ID
     * can never touch a different business's data.
     */
    protected function scopedQuery()
    {
        return RentalUnit::whereHas('property.landlord', function ($q) {
            $q->where('business_id', $this->selectedBusiness);
        });
    }

    public function resetForm(): void
    {
        $this->reset([
            'unitId', 'editMode',
            'property_id', 'unit_number',
            'floor_no', 'monthly_rent', 'deposit_amount', 'description',
            'selectedFeatures', 'newImages',
        ]);
        $this->unit_type = 'single';
        $this->bedrooms = 1;
        $this->bathrooms = 1;
        $this->has_electricity = true;
        $this->has_water = true;
        $this->has_furniture = false;
        $this->status = 'vacant';
        $this->is_published = false;
        $this->resetValidation();
    }

    // ─── Render ──────────────────────────────────────────────────

    public function render()
    {
        // Properties belonging to landlords of the selected business
        $properties = $this->selectedBusiness
            ? Property::whereHas('landlord', fn ($q) => $q->where('business_id', $this->selectedBusiness))
                ->where('status', 'active')
                ->orderBy('property_name')
                ->get(['id', 'property_name', 'property_type'])
            : collect();

        $features = $this->selectedBusiness
            ? UnitFeature::where('business_id', $this->selectedBusiness)->active()->orderBy('feature_name')->get()
            : collect();

        // When editing, load images so the gallery can render in the modal
        $unitImages = ($this->editMode && $this->unitId)
            ? UnitImage::where('rental_unit_id', $this->unitId)->orderBy('display_order')->get()
            : collect();

        if (!$this->selectedBusiness) {
            $units = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
            $stats = ['total' => 0, 'vacant' => 0, 'occupied' => 0, 'maintenance' => 0, 'monthly_potential' => 0];
        } else {
            $units = $this->scopedQuery()
                ->with(['property:id,property_name', 'images' => fn ($q) => $q->orderBy('display_order')->limit(1)])
                ->when($this->search, function ($q) {
                    $q->where(function ($qq) {
                        $qq->where('unit_number', 'like', '%' . $this->search . '%')
                           ->orWhere('description', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
                ->when($this->typeFilter, fn ($q) => $q->where('unit_type', $this->typeFilter))
                ->when($this->propertyFilter, fn ($q) => $q->where('property_id', $this->propertyFilter))
                ->latest()
                ->paginate(12);

            $base = $this->scopedQuery();
            $stats = [
                'total' => (clone $base)->count(),
                'vacant' => (clone $base)->where('status', 'vacant')->count(),
                'occupied' => (clone $base)->where('status', 'occupied')->count(),
                'maintenance' => (clone $base)->where('status', 'maintenance')->count(),
                'monthly_potential' => (clone $base)->sum('monthly_rent'),
            ];
        }

        return view('livewire.owner.rental.rental-units', [
            'units' => $units,
            'stats' => $stats,
            'businesses' => $this->ownerBusinesses,
            'properties' => $properties,
            'features' => $features,
            'unitImages' => $unitImages,
        ]);
    }
}
