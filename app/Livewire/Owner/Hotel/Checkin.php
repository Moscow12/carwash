<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomAllocation;
use App\Models\HotelBranch;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Checkin extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $selectedBranch = null;
    public $showModal = false;
    public $selectedReservation = null;
    public $selectedRoom = null;
    public $availableRooms = [];
    public $actualCheckInTime = '';
    public $depositPaid = 0;
    public $paymentMethod = 'cash';
    public $checkInNotes = '';
    public $keyCardNumber = '';

    public function mount()
    {
        $hotel = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->selectedBranch = $hotel->hotelBranches()->where('is_main', true)->first()?->id;
        }

        $this->actualCheckInTime = now()->format('Y-m-d\TH:i');
    }

    public function updatedSelectedHotel($value)
    {
        $hotel = Business::find($value);
        $this->selectedBranch = $hotel?->hotelBranches()->where('is_main', true)->first()?->id;
        $this->resetPage();
    }

    public function updatedSelectedBranch()
    {
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCheckInModal($reservationId)
    {
        $reservation = Reservation::with(['guest', 'roomType', 'branch'])
            ->findOrFail($reservationId);

        if (!in_array($reservation->status, ['confirmed', 'pending'])) {
            session()->flash('error', 'Only confirmed or pending reservations can be checked in.');
            return;
        }

        $this->selectedReservation = $reservation->toArray();
        $this->depositPaid = $reservation->deposit_amount;

        // Get available rooms of the same type
        $query = Room::where('business_id', $this->selectedHotel)
            ->where('room_type_id', $reservation->room_type_id)
            ->where('status', 'available')
            ->where('is_active', true);

        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        $this->availableRooms = $query->with('roomType')->get()->toArray();

        $this->showModal = true;
    }

    public function processCheckIn()
    {
        $this->validate([
            'selectedRoom' => 'required|exists:rooms,id',
            'actualCheckInTime' => 'required|date',
            'depositPaid' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:cash,card,bank_transfer,mobile_money',
            'keyCardNumber' => 'nullable|string|max:50',
        ]);

        DB::beginTransaction();

        try {
            $reservation = Reservation::findOrFail($this->selectedReservation['id']);
            $room = Room::findOrFail($this->selectedRoom);

            // Create room allocation
            RoomAllocation::create([
                'reservation_id' => $reservation->id,
                'room_id' => $room->id,
                'allocated_at' => now(),
                'actual_check_in' => $this->actualCheckInTime,
                'allocated_by' => Auth::id(),
            ]);

            // Update reservation status
            $reservation->update([
                'status' => 'checked_in',
                'deposit_amount' => $this->depositPaid,
            ]);

            // Update room status
            $room->update([
                'status' => 'occupied',
            ]);

            // Create folio if doesn't exist
            if (!$reservation->folios()->exists()) {
                $reservation->folios()->create([
                    'folio_no' => 'FOL-' . strtoupper(uniqid()),
                    'business_id' => $this->selectedHotel,
                    'guest_id' => $reservation->guest_id,
                    'status' => 'open',
                    'total_charges' => $reservation->total_amount,
                    'total_payments' => $this->depositPaid,
                    'balance' => $reservation->total_amount - $this->depositPaid,
                    'opened_at' => now(),
                ]);
            }

            DB::commit();

            session()->flash('message', "Guest successfully checked in to Room {$room->number}.");
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Check-in failed: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['selectedReservation', 'selectedRoom', 'availableRooms', 'depositPaid', 'paymentMethod', 'checkInNotes', 'keyCardNumber']);
        $this->actualCheckInTime = now()->format('Y-m-d\TH:i');
    }

    public function render()
    {
        $hotels = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $branches = $this->selectedHotel
            ? HotelBranch::where('business_id', $this->selectedHotel)
                ->where('status', 'active')
                ->orderBy('is_main', 'desc')
                ->orderBy('name')
                ->get()
            : collect();

        $query = Reservation::with(['guest', 'roomType', 'branch', 'source'])
            ->where('business_id', $this->selectedHotel)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereDate('check_in_date', '<=', today());

        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reservation_no', 'like', '%' . $this->search . '%')
                  ->orWhereHas('guest', function ($gq) {
                      $gq->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $reservations = $query->orderBy('check_in_date')->paginate(15);

        // Statistics
        $stats = [
            'today' => Reservation::where('business_id', $this->selectedHotel)
                ->whereDate('check_in_date', today())
                ->whereIn('status', ['confirmed', 'pending'])
                ->count(),
            'overdue' => Reservation::where('business_id', $this->selectedHotel)
                ->whereDate('check_in_date', '<', today())
                ->whereIn('status', ['confirmed', 'pending'])
                ->count(),
            'upcoming' => Reservation::where('business_id', $this->selectedHotel)
                ->whereBetween('check_in_date', [today()->addDay(), today()->addWeek()])
                ->whereIn('status', ['confirmed', 'pending'])
                ->count(),
        ];

        return view('livewire.owner.hotel.checkin', [
            'hotels' => $hotels,
            'branches' => $branches,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }
}
