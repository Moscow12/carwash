<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Business;
use App\Models\Guest;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\BookingSource;

#[Layout('components.layouts.app-owner')]
class Guests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $vipFilter = '';
    public $statusFilter = '';
    public $showModal = false;
    public $showReservationModal = false;
    public $editMode = false;
    public $guestId = null;
    public $selectedGuestForReservation = null;

    #[Rule('required|string|max:255')]
    public $first_name = '';

    #[Rule('required|string|max:255')]
    public $last_name = '';

    #[Rule('nullable|email|max:255')]
    public $email = '';

    #[Rule('required|string|max:50')]
    public $phone = '';

    #[Rule('nullable|string|max:255')]
    public $nationality = '';

    #[Rule('nullable|string|max:255')]
    public $country = '';

    #[Rule('nullable|string|max:255')]
    public $coming_from = '';

    #[Rule('nullable|string|max:255')]
    public $going_to = '';

    #[Rule('nullable|in:passport,national_id,drivers_license')]
    public $id_type = '';

    #[Rule('nullable|string|max:100')]
    public $id_number = '';

    #[Rule('nullable|date')]
    public $date_of_birth = '';

    #[Rule('nullable|in:male,female,other')]
    public $gender = '';

    #[Rule('nullable|string|max:500')]
    public $address = '';

    #[Rule('nullable|in:standard,silver,gold,platinum')]
    public $vip_level = 'standard';

    #[Rule('nullable|integer|min:0')]
    public $loyalty_points = null;

    #[Rule('nullable|boolean')]
    public $blacklisted = false;

    #[Rule('nullable|string|max:500')]
    public $blacklist_reason = '';

    #[Rule('nullable|in:active,inactive')]
    public $status = '';

    // Reservation fields
    #[Rule('required|exists:room_types,id')]
    public $room_type_id = null;

    #[Rule('nullable|exists:rate_plans,id')]
    public $rate_plan_id = null;

    #[Rule('nullable|exists:booking_sources,id')]
    public $source_id = null;

    #[Rule('required|date')]
    public $check_in_date = '';

    #[Rule('required|date|after:check_in_date')]
    public $check_out_date = '';

    #[Rule('required|integer|min:1')]
    public $adults = 1;

    #[Rule('required|integer|min:0')]
    public $children = 0;

    #[Rule('required|integer|min:1')]
    public $number_of_rooms = 1;

    #[Rule('nullable|numeric|min:0')]
    public $room_rate = 0;

    #[Rule('nullable|numeric|min:0')]
    public $deposit_amount = 0;

    #[Rule('nullable|string|max:1000')]
    public $special_requests = '';

    public function mount()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($hotels->count() > 0) {
            $this->selectedHotel = $hotels->first()->id;
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function editGuest($id)
    {
        $guest = Guest::findOrFail($id);

        $this->guestId = $guest->id;
        $this->first_name = $guest->first_name;
        $this->last_name = $guest->last_name;
        $this->email = $guest->email ?? '';
        $this->phone = $guest->phone;
        $this->nationality = $guest->nationality ?? '';
        $this->country = $guest->country ?? '';
        $this->coming_from = $guest->coming_from ?? '';
        $this->going_to = $guest->going_to ?? '';
        $this->id_type = $guest->id_type ?? '';
        $this->id_number = $guest->id_number ?? '';
        $this->date_of_birth = $guest->date_of_birth?->format('Y-m-d') ?? '';
        $this->gender = $guest->gender ?? '';
        $this->address = $guest->address ?? '';
        $this->vip_level = $guest->vip_level ?? 'standard';
        $this->loyalty_points = $guest->loyalty_points;
        $this->blacklisted = $guest->blacklisted;
        $this->blacklist_reason = $guest->blacklist_reason ?? '';
        $this->status = $guest->status;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        try {
            // Check if hotel is selected
            if (!$this->selectedHotel) {
                session()->flash('error', 'Please select a hotel first.');
                return;
            }

            // Manual validation to provide better error messages
            $this->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'required|string|max:50',
            ]);

            $data = [
                'business_id' => $this->selectedHotel,
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $this->email ?: null,
                'phone' => $this->phone,
                'nationality' => $this->nationality ?: null,
                'country' => $this->country ?: null,
                'coming_from' => $this->coming_from ?: null,
                'going_to' => $this->going_to ?: null,
                'id_type' => $this->id_type ?: null,
                'id_number' => $this->id_number ?: null,
                'date_of_birth' => $this->date_of_birth ?: null,
                'gender' => $this->gender ?: null,
                'address' => $this->address ?: null,
                'vip_level' => $this->vip_level ?: 'standard',
                'loyalty_points' => $this->loyalty_points ?? 0,
                'blacklisted' => $this->blacklisted ?? false,
                'blacklist_reason' => $this->blacklisted ? $this->blacklist_reason : null,
                'status' => $this->status ?: 'active',
            ];

            if ($this->editMode) {
                $guest = Guest::findOrFail($this->guestId);
                $guest->update($data);
                session()->flash('message', 'Guest updated successfully.');
            } else {
                Guest::create($data);
                session()->flash('message', 'Guest created successfully.');
            }

            $this->closeModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Guest save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function toggleBlacklist($id)
    {
        $guest = Guest::findOrFail($id);
        $guest->update(['blacklisted' => !$guest->blacklisted]);
        session()->flash('message', 'Guest blacklist status updated.');
    }

    public function openModalReservation($guestId)
    {
        $this->selectedGuestForReservation = $guestId;
        $this->resetReservationForm();

        // Set default dates
        $this->check_in_date = now()->format('Y-m-d');
        $this->check_out_date = now()->addDay()->format('Y-m-d');

        $this->showReservationModal = true;
    }

    public function closeReservationModal()
    {
        $this->showReservationModal = false;
        $this->resetReservationForm();
    }

    public function resetReservationForm()
    {
        $this->reset([
            'room_type_id', 'rate_plan_id', 'source_id',
            'check_in_date', 'check_out_date', 'adults', 'children',
            'number_of_rooms', 'room_rate', 'deposit_amount', 'special_requests'
        ]);
        $this->adults = 1;
        $this->children = 0;
        $this->number_of_rooms = 1;
        $this->room_rate = 0;
        $this->deposit_amount = 0;
    }

    public function saveReservation()
    {
        try {
            // Validate required fields
            $this->validate([
                'room_type_id' => 'required|exists:room_types,id',
                'check_in_date' => 'required|date',
                'check_out_date' => 'required|date|after:check_in_date',
                'adults' => 'required|integer|min:1',
                'children' => 'required|integer|min:0',
                'number_of_rooms' => 'required|integer|min:1',
            ]);

            // Check if guest and hotel are selected
            if (!$this->selectedGuestForReservation) {
                session()->flash('error', 'Guest not selected.');
                return;
            }

            if (!$this->selectedHotel) {
                session()->flash('error', 'Hotel not selected.');
                return;
            }

            // Get default branch for the hotel
            $defaultBranch = \App\Models\HotelBranch::where('business_id', $this->selectedHotel)
                ->where('status', 'active')
                ->first();

            if (!$defaultBranch) {
                session()->flash('error', 'No active branch found for this hotel. Please create a branch first.');
                return;
            }

            // Calculate total nights
            $checkIn = \Carbon\Carbon::parse($this->check_in_date);
            $checkOut = \Carbon\Carbon::parse($this->check_out_date);
            $totalNights = $checkIn->diffInDays($checkOut);

            // Generate reservation number
            $lastReservation = Reservation::where('business_id', $this->selectedHotel)
                ->whereNotNull('reservation_no')
                ->orderBy('created_at', 'desc')
                ->first();

            $nextNumber = $lastReservation ? (int) substr($lastReservation->reservation_no, 3) + 1 : 1;
            $reservationNo = 'RES' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            // Calculate total amount
            $totalAmount = ($this->room_rate ?? 0) * $totalNights * $this->number_of_rooms;

            $data = [
                'reservation_no' => $reservationNo,
                'business_id' => $this->selectedHotel,
                'branch_id' => $defaultBranch->id,
                'guest_id' => $this->selectedGuestForReservation,
                'room_type_id' => $this->room_type_id,
                'rate_plan_id' => $this->rate_plan_id ?: null,
                'source_id' => $this->source_id ?: null,
                'check_in_date' => $this->check_in_date,
                'check_out_date' => $this->check_out_date,
                'adults' => $this->adults,
                'children' => $this->children,
                'number_of_rooms' => $this->number_of_rooms,
                'total_nights' => $totalNights,
                'room_rate' => $this->room_rate ?? 0,
                'total_amount' => $totalAmount,
                'deposit_amount' => $this->deposit_amount ?? 0,
                'status' => 'pending',
                'special_requests' => $this->special_requests ?: null,
                'created_by' => Auth::id(),
            ];

            Reservation::create($data);
            session()->flash('message', 'Reservation created successfully.');
            $this->closeReservationModal();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Let Livewire handle validation errors
            throw $e;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Reservation save error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'guest_id' => $this->selectedGuestForReservation,
                    'hotel_id' => $this->selectedHotel,
                    'room_type_id' => $this->room_type_id,
                ]
            ]);
            session()->flash('error', 'Failed to create reservation: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'guestId', 'first_name', 'last_name', 'email', 'phone', 'nationality',
            'country', 'coming_from', 'going_to',
            'id_type', 'id_number', 'date_of_birth', 'gender', 'address',
            'vip_level', 'loyalty_points', 'blacklisted', 'blacklist_reason', 'status'
        ]);
        $this->blacklisted = false;
        $this->resetValidation();
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->orderBy('name')
            ->get();

        try {
            $query = Guest::withCount(['reservations', 'folios'])
                ->where('business_id', $this->selectedHotel);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->vipFilter) {
                $query->where('vip_level', $this->vipFilter);
            }

            if ($this->statusFilter) {
                $query->where('status', $this->statusFilter);
            }

            $guests = $query->latest()->paginate(15);
        } catch (\Exception $e) {
            session()->flash('error', 'Unable to load guest data. Please contact support if this issue persists.');
            $guests = Guest::where('business_id', $this->selectedHotel)->paginate(15);
        }

        $stats = [
            'total' => Guest::where('business_id', $this->selectedHotel)->count(),
            'vip' => Guest::where('business_id', $this->selectedHotel)->whereIn('vip_level', ['silver', 'gold', 'platinum'])->count(),
            'blacklisted' => Guest::where('business_id', $this->selectedHotel)->where('blacklisted', true)->count(),
        ];

        // Get countries for dropdown
        $countries = DB::table('countries')
            ->orderBy('name')
            ->pluck('name', 'name');

        // Get room types, rate plans, and booking sources for reservation modal
        $roomTypes = RoomType::where('business_id', $this->selectedHotel)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $ratePlans = RatePlan::where('business_id', $this->selectedHotel)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        $bookingSources = BookingSource::where('business_id', $this->selectedHotel)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return view('livewire.owner.hotel.guests', [
            'hotels' => $hotels,
            'guests' => $guests,
            'countries' => $countries,
            'stats' => $stats,
            'roomTypes' => $roomTypes,
            'ratePlans' => $ratePlans,
            'bookingSources' => $bookingSources,
        ]);
    }
}
