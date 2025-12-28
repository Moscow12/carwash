<?php

namespace App\Livewire\Owner\Carwashes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\carwashes;
use App\Models\regions;
use App\Models\districts;
use App\Models\wards;
use App\Models\street;
#[Layout('components.layouts.app-owner')]
class Mycarwash extends Component
{
    use WithPagination;

    public $search = '';
    public $showModal = false;
    public $editMode = false;
    public $carwashId = null;

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|string|max:255')]
    public $address = '';

    #[Rule('nullable|string|max:500')]
    public $description = '';

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    #[Rule('nullable|string|max:50')]
    public $whatsapp = '';

    #[Rule('nullable|string|max:100')]
    public $instagram = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('nullable|url|max:255')]
    public $website = '';

    #[Rule('nullable|string|max:100')]
    public $operating_hours = '';

    #[Rule('required|string|max:255')]
    public $resentative_name = '';

    #[Rule('required|string|max:50')]
    public $resentative_phone = '';

    #[Rule('required|exists:regions,id')]
    public $region_id = '';

    #[Rule('required|exists:districts,id')]
    public $district_id = '';

    #[Rule('required|exists:wards,id')]
    public $ward_id = '';

    #[Rule('nullable|exists:streets,id')]
    public $street_id = '';

    public $allRegions = [];
    public $allDistricts = [];
    public $allWards = [];
    public $allStreets = [];

    public function mount()
    {
        $this->allRegions = regions::orderBy('name')->get();
    }

    public function updatedRegionId($value)
    {
        $this->allDistricts = $value ? districts::where('region_id', $value)->orderBy('name')->get() : [];
        $this->district_id = '';
        $this->ward_id = '';
        $this->street_id = '';
        $this->allWards = [];
        $this->allStreets = [];
    }

    public function updatedDistrictId($value)
    {
        $this->allWards = $value ? wards::where('district_id', $value)->orderBy('name')->get() : [];
        $this->ward_id = '';
        $this->street_id = '';
        $this->allStreets = [];
    }

    public function updatedWardId($value)
    {
        $this->allStreets = $value ? street::where('ward_id', $value)->orderBy('name')->get() : [];
        $this->street_id = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editCarwash($id)
    {
        $carwash = carwashes::findOrFail($id);

        $this->carwashId = $carwash->id;
        $this->name = $carwash->name;
        $this->address = $carwash->address;
        $this->description = $carwash->description ?? '';
        $this->status = $carwash->status;
        $this->whatsapp = $carwash->whatsapp ?? '';
        $this->instagram = $carwash->instagram ?? '';
        $this->email = $carwash->email ?? '';
        $this->website = $carwash->website ?? '';
        $this->operating_hours = $carwash->operating_hours ?? '';
        $this->resentative_name = $carwash->resentative_name;
        $this->resentative_phone = $carwash->resentative_phone;
        $this->region_id = $carwash->region_id;

        $this->allDistricts = districts::where('region_id', $carwash->region_id)->orderBy('name')->get();
        $this->district_id = $carwash->district_id;

        $this->allWards = wards::where('district_id', $carwash->district_id)->orderBy('name')->get();
        $this->ward_id = $carwash->ward_id;

        $this->allStreets = street::where('ward_id', $carwash->ward_id)->orderBy('name')->get();
        $this->street_id = $carwash->street_id ?? '';

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description ?: null,
            'status' => $this->status,
            'whatsapp' => $this->whatsapp ?: null,
            'instagram' => $this->instagram ?: null,
            'email' => $this->email ?: null,
            'website' => $this->website ?: null,
            'operating_hours' => $this->operating_hours ?: null,
            'resentative_name' => $this->resentative_name,
            'resentative_phone' => $this->resentative_phone,
            'region_id' => $this->region_id,
            'district_id' => $this->district_id,
            'ward_id' => $this->ward_id,
            'street_id' => $this->street_id ?: null,
        ];

        if ($this->editMode) {
            $carwash = carwashes::findOrFail($this->carwashId);
            $carwash->update($data);
            session()->flash('message', 'Carwash updated successfully.');
        } else {
            $data['owner_id'] = Auth::id();
            carwashes::create($data);
            session()->flash('message', 'Carwash created successfully.');
        }

        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'carwashId', 'name', 'address', 'description', 'status',
            'whatsapp', 'instagram', 'email', 'website', 'operating_hours',
            'resentative_name', 'resentative_phone', 'region_id',
            'district_id', 'ward_id', 'street_id'
        ]);
        $this->status = 'active';
        $this->allDistricts = [];
        $this->allWards = [];
        $this->allStreets = [];
        $this->resetValidation();
    }

    public function render()
    {
        $carwashes = Auth::user()->ownedCarwashes()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->with(['regions', 'districts', 'wards'])
            ->latest()
            ->paginate(10);
        return view('livewire.owner.carwashes.mycarwash', [
            'carwashes' => $carwashes
        ]);
    }
}
