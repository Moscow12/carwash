<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\HotelBranch;

#[Layout('components.layouts.app-owner')]
class Rooms extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $selectedBranch = null;
    public $statusFilter = '';
    public $floorFilter = '';
    public $showModal = false;
    public $editMode = false;
    public $roomId = null;

    #[Rule('required|exists:room_types,id')]
    public $room_type_id = '';

    #[Rule('required|string|max:20')]
    public $number = '';

    #[Rule('nullable|string|max:10')]
    public $floor = '';

    #[Rule('required|in:available,occupied,cleaning,maintenance,out_of_order')]
    public $status = 'available';

    #[Rule('required|boolean')]
    public $is_smoking = false;

    #[Rule('required|boolean')]
    public $is_active = true;

    #[Rule('nullable|string|max:500')]
    public $notes = '';

    public $roomTypes = [];
    public $branches = [];

    public function mount()
    {
        $hotel = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->loadDropdownData();
            // Only set branch if branches exist
            if ($this->branches->isNotEmpty()) {
                $this->selectedBranch = $hotel->hotelBranches()->where('is_main', true)->first()?->id;
            }
        }
    }

    public function updatedSelectedHotel($value)
    {
        $this->loadDropdownData();
        // Only set branch if branches exist
        if ($this->branches->isNotEmpty()) {
            $hotel = Business::find($value);
            $this->selectedBranch = $hotel?->hotelBranches()->where('is_main', true)->first()?->id;
        } else {
            $this->selectedBranch = null;
        }
        $this->resetPage();
    }

    public function updatedSelectedBranch()
    {
        $this->resetPage();
    }

    public function loadDropdownData()
    {
        if (!$this->selectedHotel) {
            return;
        }

        $this->roomTypes = RoomType::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->branches = HotelBranch::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->orderBy('is_main', 'desc')
            ->orderBy('name')
            ->get();

        // Auto-create main branch if business has no branches
        if ($this->branches->isEmpty()) {
            $business = Business::find($this->selectedHotel);
            if ($business) {
                $mainBranch = HotelBranch::create([
                    'business_id' => $this->selectedHotel,
                    'name' => $business->name . ' - Main Branch',
                    'code' => 'MAIN',
                    'is_main' => true,
                    'phone' => $business->phone ?? null,
                    'address' => $business->address ?? null,
                    'status' => 'active',
                ]);

                // Reload branches
                $this->branches = HotelBranch::where('business_id', $this->selectedHotel)
                    ->where('status', 'active')
                    ->orderBy('is_main', 'desc')
                    ->orderBy('name')
                    ->get();

                $this->selectedBranch = $mainBranch->id;

                session()->flash('message', 'Main branch automatically created for ' . $business->name);
            }
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
        $this->loadDropdownData();
    }

    public function editRoom($id)
    {
        $room = Room::findOrFail($id);

        $this->roomId = $room->id;
        $this->room_type_id = $room->room_type_id;
        $this->number = $room->number;
        $this->floor = $room->floor ?? '';
        $this->status = $room->status;
        $this->is_smoking = $room->is_smoking;
        $this->is_active = $room->is_active;
        $this->notes = $room->notes ?? '';

        $this->editMode = true;
        $this->showModal = true;
        $this->loadDropdownData();
    }

    public function save()
    {
        $this->validate();

        // Ensure branch_id is properly set
        if (!$this->selectedBranch || $this->selectedBranch === '' || $this->selectedBranch === 'null') {
            // If no branch selected, reload data to check/create branch
            $this->loadDropdownData();

            // After loading, if still no branch, set to null
            if ($this->branches->isEmpty()) {
                $this->selectedBranch = null;
            } elseif (!$this->selectedBranch) {
                session()->flash('error', 'Please select a branch for this room.');
                return;
            }
        }

        $data = [
            'business_id' => $this->selectedHotel,
            'branch_id' => $this->selectedBranch ?: null,
            'room_type_id' => $this->room_type_id,
            'number' => $this->number,
            'floor' => $this->floor ?: null,
            'status' => $this->status,
            'is_smoking' => $this->is_smoking,
            'is_active' => $this->is_active,
            'notes' => $this->notes ?: null,
        ];

        try {
            if ($this->editMode) {
                $room = Room::findOrFail($this->roomId);
                $room->update($data);
                session()->flash('message', 'Room updated successfully.');
            } else {
                Room::create($data);
                session()->flash('message', 'Room created successfully.');
            }
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to save room: ' . $e->getMessage());
        }
    }

    public function changeRoomStatus($id, $newStatus)
    {
        $room = Room::findOrFail($id);
        $room->update(['status' => $newStatus]);
        session()->flash('message', 'Room status updated successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['roomId', 'room_type_id', 'number', 'floor', 'status', 'is_smoking', 'is_active', 'notes']);
        $this->status = 'available';
        $this->is_active = true;
        $this->is_smoking = false;
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $query = Room::with(['roomType', 'branch'])
            ->where('business_id', $this->selectedHotel);

        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        if ($this->search) {
            $query->where('number', 'like', '%' . $this->search . '%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->floorFilter) {
            $query->where('floor', $this->floorFilter);
        }

        $rooms = $query->orderBy('floor')->orderBy('number')->paginate(20);

        $stats = [
            'total' => Room::where('business_id', $this->selectedHotel)->where('is_active', true)->count(),
            'available' => Room::where('business_id', $this->selectedHotel)->where('status', 'available')->count(),
            'occupied' => Room::where('business_id', $this->selectedHotel)->where('status', 'occupied')->count(),
            'cleaning' => Room::where('business_id', $this->selectedHotel)->where('status', 'cleaning')->count(),
            'maintenance' => Room::where('business_id', $this->selectedHotel)->where('status', 'maintenance')->count(),
        ];

        return view('livewire.owner.hotel.rooms', [
            'hotels' => $hotels,
            'rooms' => $rooms,
            'stats' => $stats,
        ]);
    }
}
