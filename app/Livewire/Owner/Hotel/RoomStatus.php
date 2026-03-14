<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Reservation;

#[Layout('components.layouts.app-owner')]
class RoomStatus extends Component
{
    public $selectedHotel = null;
    public $filterStatus = '';
    public $filterRoomType = '';
    public $search = '';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }
    }

    public function changeRoomStatus($roomId, $newStatus)
    {
        try {
            $room = Room::findOrFail($roomId);
            $room->update(['status' => $newStatus]);
            session()->flash('message', "Room {$room->number} status updated to " . ucfirst($newStatus));
        } catch (\Exception $e) {
            session()->flash('error', 'Status update failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $rooms = collect();
        $roomTypes = collect();
        $stats = [
            'total' => 0,
            'available' => 0,
            'occupied' => 0,
            'cleaning' => 0,
            'maintenance' => 0,
            'out_of_order' => 0,
        ];

        if ($this->selectedHotel) {
            $query = Room::where('business_id', $this->selectedHotel)
                ->with(['roomType', 'currentReservation']);

            if ($this->search) {
                $query->where('number', 'like', '%' . $this->search . '%');
            }

            if ($this->filterStatus) {
                $query->where('status', $this->filterStatus);
            }

            if ($this->filterRoomType) {
                $query->where('room_type_id', $this->filterRoomType);
            }

            $rooms = $query->orderBy('number')->get();

            // Get room types for filter
            $roomTypes = RoomType::where('business_id', $this->selectedHotel)
                ->orderBy('name')
                ->get();

            // Calculate statistics
            $allRooms = Room::where('business_id', $this->selectedHotel)->get();
            $stats = [
                'total' => $allRooms->count(),
                'available' => $allRooms->where('status', 'available')->count(),
                'occupied' => $allRooms->where('status', 'occupied')->count(),
                'cleaning' => $allRooms->where('status', 'cleaning')->count(),
                'maintenance' => $allRooms->where('status', 'maintenance')->count(),
                'out_of_order' => $allRooms->where('status', 'out_of_order')->count(),
            ];
        }

        return view('livewire.owner.hotel.room-status', [
            'hotels' => $hotels,
            'rooms' => $rooms,
            'roomTypes' => $roomTypes,
            'stats' => $stats,
        ]);
    }
}
