<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Room;
use App\Models\HousekeepingTask;

#[Layout('components.layouts.app-owner')]
class HousekeepingStatus extends Component
{
    public $selectedHotel = null;
    public $filterFloor = '';
    public $filterStatus = '';
    public $search = '';
    public $showModal = false;
    public $selectedRoom = null;

    // Quick update properties
    #[Rule('required|in:clean,dirty,inspected,out_of_order')]
    public $housekeeping_status = 'clean';

    #[Rule('nullable|string|max:500')]
    public $notes = '';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }
    }

    public function openModal($roomId)
    {
        $this->selectedRoom = Room::findOrFail($roomId);
        $this->housekeeping_status = $this->selectedRoom->housekeeping_status ?? 'clean';
        $this->notes = '';
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedRoom = null;
        $this->housekeeping_status = 'clean';
        $this->notes = '';
        $this->resetValidation();
    }

    public function updateStatus()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $this->selectedRoom->update([
                'housekeeping_status' => $this->housekeeping_status,
            ]);

            // Create housekeeping task if status is dirty or needs inspection
            if (in_array($this->housekeeping_status, ['dirty', 'inspected'])) {
                HousekeepingTask::create([
                    'business_id' => $this->selectedHotel,
                    'room_id' => $this->selectedRoom->id,
                    'task_type' => $this->housekeeping_status === 'dirty' ? 'cleaning' : 'inspection',
                    'priority' => 'medium',
                    'status' => 'pending',
                    'description' => $this->notes ?: "Room {$this->selectedRoom->number} needs " .
                                   ($this->housekeeping_status === 'dirty' ? 'cleaning' : 'inspection'),
                    'due_date' => now()->addHours(2),
                ]);
            }

            DB::commit();
            session()->flash('message', "Room {$this->selectedRoom->number} status updated to " . ucfirst(str_replace('_', ' ', $this->housekeeping_status)));
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function quickUpdateStatus($roomId, $status)
    {
        try {
            $room = Room::findOrFail($roomId);
            $room->update(['housekeeping_status' => $status]);
            session()->flash('message', "Room {$room->number} marked as " . ucfirst(str_replace('_', ' ', $status)));
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $rooms = collect();
        $stats = [
            'clean' => 0,
            'dirty' => 0,
            'inspected' => 0,
            'out_of_order' => 0,
            'total' => 0,
        ];
        $floors = [];

        if ($this->selectedHotel) {
            $roomQuery = Room::where('business_id', $this->selectedHotel)
                ->with('roomType');

            if ($this->filterFloor) {
                $roomQuery->where('floor', $this->filterFloor);
            }

            if ($this->filterStatus) {
                $roomQuery->where('housekeeping_status', $this->filterStatus);
            }

            if ($this->search) {
                $roomQuery->where(function($q) {
                    $q->where('number', 'like', '%' . $this->search . '%')
                      ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            }

            $rooms = $roomQuery->orderBy('floor')->orderBy('number')->get();

            // Get all floors for filter dropdown
            $floors = Room::where('business_id', $this->selectedHotel)
                ->select('floor')
                ->distinct()
                ->orderBy('floor')
                ->pluck('floor')
                ->toArray();

            // Calculate statistics
            $allRooms = Room::where('business_id', $this->selectedHotel)->get();
            $stats = [
                'clean' => $allRooms->where('housekeeping_status', 'clean')->count(),
                'dirty' => $allRooms->where('housekeeping_status', 'dirty')->count(),
                'inspected' => $allRooms->where('housekeeping_status', 'inspected')->count(),
                'out_of_order' => $allRooms->where('housekeeping_status', 'out_of_order')->count(),
                'total' => $allRooms->count(),
            ];
        }

        return view('livewire.owner.hotel.housekeeping-status', [
            'hotels' => $hotels,
            'rooms' => $rooms,
            'stats' => $stats,
            'floors' => $floors,
        ]);
    }
}
