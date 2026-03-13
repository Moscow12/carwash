<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\HotelBranch;

#[Layout('components.layouts.app-owner')]
class Frontdesk extends Component
{
    public $selectedHotel = null;
    public $selectedBranch = null;

    // Metrics
    public $totalRooms = 0;
    public $availableRooms = 0;
    public $occupiedRooms = 0;
    public $occupancyRate = 0;
    public $checkingInToday = 0;
    public $checkingOutToday = 0;
    public $pendingReservations = 0;

    // Recent activities
    public $recentCheckIns = [];
    public $upcomingArrivals = [];
    public $upcomingDepartures = [];

    // Room status breakdown
    public $roomsByStatus = [];

    public function mount()
    {
        // Get user's first hotel
        $hotel = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->selectedBranch = $hotel->hotelBranches()->where('is_main', true)->first()?->id;
            $this->loadDashboardData();
        }
    }

    public function updatedSelectedHotel($value)
    {
        $hotel = Business::find($value);
        $this->selectedBranch = $hotel?->hotelBranches()->where('is_main', true)->first()?->id;
        $this->loadDashboardData();
    }

    public function updatedSelectedBranch()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        if (!$this->selectedHotel) {
            return;
        }

        $query = Room::where('business_id', $this->selectedHotel);

        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        // Room statistics
        $this->totalRooms = $query->where('is_active', true)->count();
        $this->availableRooms = $query->where('status', 'available')->where('is_active', true)->count();
        $this->occupiedRooms = $query->where('status', 'occupied')->count();
        $this->occupancyRate = $this->totalRooms > 0
            ? round(($this->occupiedRooms / $this->totalRooms) * 100, 1)
            : 0;

        // Room status breakdown
        $this->roomsByStatus = $query->where('is_active', true)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Reservation statistics
        $reservationQuery = Reservation::where('business_id', $this->selectedHotel);

        if ($this->selectedBranch) {
            $reservationQuery->where('branch_id', $this->selectedBranch);
        }

        $this->checkingInToday = $reservationQuery->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();

        $this->checkingOutToday = $reservationQuery->whereDate('check_out_date', today())
            ->where('status', 'checked_in')
            ->count();

        $this->pendingReservations = $reservationQuery->where('status', 'pending')->count();

        // Recent check-ins (last 24 hours)
        $this->recentCheckIns = $reservationQuery->where('status', 'checked_in')
            ->whereBetween('updated_at', [now()->subDay(), now()])
            ->with(['guest', 'roomType'])
            ->latest('updated_at')
            ->limit(5)
            ->get()
            ->toArray();

        // Upcoming arrivals (today)
        $this->upcomingArrivals = $reservationQuery->whereDate('check_in_date', today())
            ->whereIn('status', ['confirmed', 'pending'])
            ->with(['guest', 'roomType'])
            ->orderBy('check_in_date')
            ->limit(5)
            ->get()
            ->toArray();

        // Upcoming departures (today)
        $this->upcomingDepartures = $reservationQuery->whereDate('check_out_date', today())
            ->where('status', 'checked_in')
            ->with(['guest', 'roomType', 'roomAllocation.room'])
            ->orderBy('check_out_date')
            ->limit(5)
            ->get()
            ->toArray();
    }

    public function quickCheckIn($reservationId)
    {
        return redirect()->route('owner.hotel.checkin', ['reservation' => $reservationId]);
    }

    public function quickCheckOut($reservationId)
    {
        return redirect()->route('owner.hotel.checkout', ['reservation' => $reservationId]);
    }

    public function render()
    {
        $hotels = Auth::user()->ownedBusinesses()
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

        return view('livewire.owner.hotel.frontdesk', [
            'hotels' => $hotels,
            'branches' => $branches,
        ]);
    }
}
