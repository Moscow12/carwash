<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\RatePlan;
use App\Models\RoomType;

#[Layout('components.layouts.app-owner')]
class RatePlans extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $showModal = false;
    public $editMode = false;
    public $ratePlanId = null;

    #[Rule('required|exists:room_types,id')]
    public $room_type_id = '';

    #[Rule('required|string|max:255')]
    public $name = '';

    #[Rule('required|in:RO,BB,HB,FB,AI')]
    public $meal_plan = 'RO';

    #[Rule('required|numeric|min:0')]
    public $price = 0;

    #[Rule('nullable|date')]
    public $valid_from = '';

    #[Rule('nullable|date|after_or_equal:valid_from')]
    public $valid_to = '';

    #[Rule('required|integer|min:1')]
    public $min_nights = 1;

    #[Rule('required|in:active,inactive')]
    public $status = 'active';

    public $roomTypes = [];

    public function mount()
    {
        $hotel = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->loadRoomTypes();
        }
    }

    public function loadRoomTypes()
    {
        if (!$this->selectedHotel) return;

        $this->roomTypes = RoomType::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
        $this->loadRoomTypes();
    }

    public function editRatePlan($id)
    {
        $ratePlan = RatePlan::findOrFail($id);

        $this->ratePlanId = $ratePlan->id;
        $this->room_type_id = $ratePlan->room_type_id;
        $this->name = $ratePlan->name;
        $this->meal_plan = $ratePlan->meal_plan;
        $this->price = $ratePlan->price;
        $this->valid_from = $ratePlan->valid_from?->format('Y-m-d') ?? '';
        $this->valid_to = $ratePlan->valid_to?->format('Y-m-d') ?? '';
        $this->min_nights = $ratePlan->min_nights;
        $this->status = $ratePlan->status;

        $this->editMode = true;
        $this->showModal = true;
        $this->loadRoomTypes();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'business_id' => $this->selectedHotel,
            'room_type_id' => $this->room_type_id,
            'name' => $this->name,
            'meal_plan' => $this->meal_plan,
            'price' => $this->price,
            'valid_from' => $this->valid_from ?: null,
            'valid_to' => $this->valid_to ?: null,
            'min_nights' => $this->min_nights,
            'status' => $this->status,
        ];

        if ($this->editMode) {
            RatePlan::findOrFail($this->ratePlanId)->update($data);
            session()->flash('message', 'Rate plan updated successfully.');
        } else {
            RatePlan::create($data);
            session()->flash('message', 'Rate plan created successfully.');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $ratePlan = RatePlan::findOrFail($id);

        if ($ratePlan->reservations()->exists()) {
            session()->flash('error', 'Cannot delete rate plan with existing reservations.');
            return;
        }

        $ratePlan->delete();
        session()->flash('message', 'Rate plan deleted successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['ratePlanId', 'room_type_id', 'name', 'meal_plan', 'price', 'valid_from', 'valid_to', 'min_nights', 'status']);
        $this->meal_plan = 'RO';
        $this->min_nights = 1;
        $this->status = 'active';
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $query = RatePlan::with('roomType')
            ->where('business_id', $this->selectedHotel);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $ratePlans = $query->latest()->paginate(15);

        return view('livewire.owner.hotel.rate-plans', [
            'hotels' => $hotels,
            'ratePlans' => $ratePlans,
        ]);
    }
}
