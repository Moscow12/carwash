<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Reservation;
use App\Models\Guest;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\BookingSource;
use App\Models\HotelBranch;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Reservations extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $showModal = false;
    public $editMode = false;
    public $reservationId = null;

    // Form fields
    #[Rule('required|exists:guests,id')]
    public $guest_id = '';

    #[Rule('required|exists:room_types,id')]
    public $room_type_id = '';

    #[Rule('nullable|exists:rate_plans,id')]
    public $rate_plan_id = '';

    #[Rule('nullable|exists:booking_sources,id')]
    public $source_id = '';

    #[Rule('required|date|after_or_equal:today')]
    public $check_in_date = '';

    #[Rule('required|date|after:check_in_date')]
    public $check_out_date = '';

    #[Rule('required|integer|min:1|max:10')]
    public $adults = 1;

    #[Rule('required|integer|min:0|max:10')]
    public $children = 0;

    #[Rule('required|numeric|min:0')]
    public $room_rate = 0;

    #[Rule('nullable|numeric|min:0')]
    public $deposit_amount = 0;

    #[Rule('required|in:pending,confirmed,checked_in,checked_out,cancelled,no_show')]
    public $status = 'pending';

    #[Rule('nullable|string|max:1000')]
    public $special_requests = '';

    #[Rule('nullable|string|max:1000')]
    public $internal_notes = '';

    public $selectedHotel = null;
    public $selectedBranch = null;
    public $total_nights = 0;
    public $total_amount = 0;

    // Data for dropdowns
    public $guests = [];
    public $roomTypes = [];
    public $ratePlans = [];
    public $bookingSources = [];

    public function mount()
    {
        $hotel = Auth::user()->ownedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->selectedBranch = $hotel->hotelBranches()->where('is_main', true)->first()?->id;
            $this->loadDropdownData();
        }

        $this->check_in_date = today()->format('Y-m-d');
        $this->check_out_date = today()->addDay()->format('Y-m-d');
    }

    public function updatedSelectedHotel($value)
    {
        $hotel = Business::find($value);
        $this->selectedBranch = $hotel?->hotelBranches()->where('is_main', true)->first()?->id;
        $this->loadDropdownData();
        $this->resetPage();
    }

    public function updatedSelectedBranch()
    {
        $this->resetPage();
    }

    public function updatedCheckInDate()
    {
        $this->calculateNightsAndTotal();
    }

    public function updatedCheckOutDate()
    {
        $this->calculateNightsAndTotal();
    }

    public function updatedRoomRate()
    {
        $this->calculateNightsAndTotal();
    }

    public function updatedRoomTypeId($value)
    {
        if ($value) {
            $roomType = RoomType::find($value);
            $this->room_rate = $roomType?->base_price ?? 0;

            // Load rate plans for this room type
            $this->ratePlans = RatePlan::where('room_type_id', $value)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            $this->calculateNightsAndTotal();
        }
    }

    public function updatedRatePlanId($value)
    {
        if ($value) {
            $ratePlan = RatePlan::find($value);
            $this->room_rate = $ratePlan?->rate_amount ?? $this->room_rate;
            $this->calculateNightsAndTotal();
        }
    }

    public function calculateNightsAndTotal()
    {
        if ($this->check_in_date && $this->check_out_date) {
            $checkIn = Carbon::parse($this->check_in_date);
            $checkOut = Carbon::parse($this->check_out_date);
            $this->total_nights = $checkIn->diffInDays($checkOut);
            $this->total_amount = $this->total_nights * $this->room_rate;
        }
    }

    public function loadDropdownData()
    {
        if (!$this->selectedHotel) {
            return;
        }

        $this->guests = Guest::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->where('blacklisted', false)
            ->orderBy('first_name')
            ->get();

        $this->roomTypes = RoomType::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $this->bookingSources = BookingSource::where('business_id', $this->selectedHotel)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
        $this->loadDropdownData();
    }

    public function editReservation($id)
    {
        $reservation = Reservation::findOrFail($id);

        $this->reservationId = $reservation->id;
        $this->guest_id = $reservation->guest_id;
        $this->room_type_id = $reservation->room_type_id;
        $this->rate_plan_id = $reservation->rate_plan_id;
        $this->source_id = $reservation->source_id;
        $this->check_in_date = $reservation->check_in_date->format('Y-m-d');
        $this->check_out_date = $reservation->check_out_date->format('Y-m-d');
        $this->adults = $reservation->adults;
        $this->children = $reservation->children;
        $this->room_rate = $reservation->room_rate;
        $this->deposit_amount = $reservation->deposit_amount;
        $this->status = $reservation->status;
        $this->special_requests = $reservation->special_requests ?? '';
        $this->internal_notes = $reservation->internal_notes ?? '';

        $this->calculateNightsAndTotal();
        $this->loadDropdownData();
        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $this->calculateNightsAndTotal();

        $data = [
            'business_id' => $this->selectedHotel,
            'branch_id' => $this->selectedBranch,
            'guest_id' => $this->guest_id,
            'room_type_id' => $this->room_type_id,
            'rate_plan_id' => $this->rate_plan_id ?: null,
            'source_id' => $this->source_id ?: null,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'adults' => $this->adults,
            'children' => $this->children,
            'total_nights' => $this->total_nights,
            'room_rate' => $this->room_rate,
            'total_amount' => $this->total_amount,
            'deposit_amount' => $this->deposit_amount ?: 0,
            'status' => $this->status,
            'special_requests' => $this->special_requests ?: null,
            'internal_notes' => $this->internal_notes ?: null,
        ];

        if ($this->editMode) {
            $reservation = Reservation::findOrFail($this->reservationId);
            $reservation->update($data);
            session()->flash('message', 'Reservation updated successfully.');
        } else {
            $data['reservation_no'] = 'RES-' . strtoupper(uniqid());
            $data['created_by'] = Auth::id();
            Reservation::create($data);
            session()->flash('message', 'Reservation created successfully.');
        }

        $this->closeModal();
    }

    public function cancelReservation($id)
    {
        $reservation = Reservation::findOrFail($id);

        if (in_array($reservation->status, ['checked_in', 'checked_out'])) {
            session()->flash('error', 'Cannot cancel a reservation that is already checked-in or checked-out.');
            return;
        }

        $reservation->update(['status' => 'cancelled']);
        session()->flash('message', 'Reservation cancelled successfully.');
    }

    public function confirmReservation($id)
    {
        $reservation = Reservation::findOrFail($id);

        if ($reservation->status !== 'pending') {
            session()->flash('error', 'Only pending reservations can be confirmed.');
            return;
        }

        $reservation->update(['status' => 'confirmed']);
        session()->flash('message', 'Reservation confirmed successfully.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'reservationId', 'guest_id', 'room_type_id', 'rate_plan_id', 'source_id',
            'adults', 'children', 'room_rate', 'deposit_amount', 'status',
            'special_requests', 'internal_notes', 'total_nights', 'total_amount'
        ]);

        $this->check_in_date = today()->format('Y-m-d');
        $this->check_out_date = today()->addDay()->format('Y-m-d');
        $this->status = 'pending';
        $this->adults = 1;
        $this->children = 0;
        $this->resetValidation();
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

        $query = Reservation::with(['guest', 'roomType', 'source', 'branch'])
            ->where('business_id', $this->selectedHotel);

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

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('check_in_date', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('check_in_date', '<=', $this->dateTo);
        }

        $reservations = $query->latest()->paginate(15);

        // Statistics
        $stats = [
            'total' => Reservation::where('business_id', $this->selectedHotel)->count(),
            'pending' => Reservation::where('business_id', $this->selectedHotel)->where('status', 'pending')->count(),
            'confirmed' => Reservation::where('business_id', $this->selectedHotel)->where('status', 'confirmed')->count(),
            'checked_in' => Reservation::where('business_id', $this->selectedHotel)->where('status', 'checked_in')->count(),
        ];

        return view('livewire.owner.hotel.reservations', [
            'hotels' => $hotels,
            'branches' => $branches,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }
}
