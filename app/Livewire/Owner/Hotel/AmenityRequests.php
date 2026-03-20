<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\HotelAmenityRequest;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;
use App\Models\Folio;
use App\Models\staffs;

#[Layout('components.layouts.app-owner')]
class AmenityRequests extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = null;
    public $filterStatus = 'all';
    public $showModal = false;
    public $editMode = false;

    // Request Properties
    public $requestId = null;

    #[Rule('nullable|exists:reservations,id')]
    public $reservation_id = null;

    #[Rule('nullable|exists:rooms,id')]
    public $room_id = null;

    #[Rule('required|exists:guests,id')]
    public $guest_id = null;

    #[Rule('nullable|exists:folios,id')]
    public $folio_id = null;

    #[Rule('required|string|max:100')]
    public $amenity = '';

    #[Rule('required|integer|min:1')]
    public $quantity = 1;

    #[Rule('nullable|numeric|min:0')]
    public $charge_amount = 0;

    #[Rule('nullable|string')]
    public $notes = '';

    public function mount()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        if ($businesses->count() > 0) {
            $this->selectedBusiness = $businesses->first()->id;
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->editMode = false;
        $this->requestId = null;
        $this->reservation_id = null;
        $this->room_id = null;
        $this->guest_id = null;
        $this->folio_id = null;
        $this->amenity = '';
        $this->quantity = 1;
        $this->charge_amount = 0;
        $this->notes = '';
        $this->resetValidation();
    }

    public function saveRequest()
    {
        $this->validate([
            'guest_id' => 'required|exists:guests,id',
            'amenity' => 'required|string|max:100',
            'quantity' => 'required|integer|min:1',
            'charge_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedBusiness,
                'reservation_id' => $this->reservation_id,
                'room_id' => $this->room_id,
                'guest_id' => $this->guest_id,
                'folio_id' => $this->folio_id,
                'amenity' => $this->amenity,
                'quantity' => $this->quantity,
                'charge_amount' => $this->charge_amount ?? 0,
                'requested_at' => now(),
                'status' => 'pending',
                'notes' => $this->notes,
            ];

            if ($this->editMode && $this->requestId) {
                HotelAmenityRequest::findOrFail($this->requestId)->update($data);
                session()->flash('message', 'Amenity request updated successfully.');
            } else {
                HotelAmenityRequest::create($data);
                session()->flash('message', 'Amenity request created successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editRequest($id)
    {
        $request = HotelAmenityRequest::findOrFail($id);

        $this->editMode = true;
        $this->requestId = $request->id;
        $this->reservation_id = $request->reservation_id;
        $this->room_id = $request->room_id;
        $this->guest_id = $request->guest_id;
        $this->folio_id = $request->folio_id;
        $this->amenity = $request->amenity;
        $this->quantity = $request->quantity;
        $this->charge_amount = $request->charge_amount;
        $this->notes = $request->notes;

        $this->showModal = true;
    }

    public function markInProgress($id)
    {
        try {
            HotelAmenityRequest::findOrFail($id)->update(['status' => 'in_progress']);
            session()->flash('message', 'Request marked as in progress.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function markDelivered($id)
    {
        try {
            HotelAmenityRequest::findOrFail($id)->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => Auth::user()->staff->id ?? null,
            ]);
            session()->flash('message', 'Request marked as delivered.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delivery failed: ' . $e->getMessage());
        }
    }

    public function cancelRequest($id)
    {
        try {
            HotelAmenityRequest::findOrFail($id)->update(['status' => 'cancelled']);
            session()->flash('message', 'Request cancelled.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cancel failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $requests = collect();
        $stats = [
            'pending' => 0,
            'in_progress' => 0,
            'delivered_today' => 0,
            'total_charges' => 0,
        ];

        if ($this->selectedBusiness) {
            $query = HotelAmenityRequest::where('business_id', $this->selectedBusiness);

            // Filter by status
            if ($this->filterStatus !== 'all') {
                $query->where('status', $this->filterStatus);
            }

            // Search
            if ($this->search) {
                $query->where(function($q) {
                    $q->where('amenity', 'like', '%' . $this->search . '%')
                      ->orWhereHas('guest', function($gq) {
                          $gq->where('full_name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('room', function($rq) {
                          $rq->where('room_number', 'like', '%' . $this->search . '%');
                      });
                });
            }

            $requests = $query->with(['guest', 'room', 'reservation', 'folio', 'deliveredBy'])
                ->latest('requested_at')
                ->paginate(15);

            // Statistics
            $stats['pending'] = HotelAmenityRequest::where('business_id', $this->selectedBusiness)
                ->where('status', 'pending')->count();
            $stats['in_progress'] = HotelAmenityRequest::where('business_id', $this->selectedBusiness)
                ->where('status', 'in_progress')->count();
            $stats['delivered_today'] = HotelAmenityRequest::where('business_id', $this->selectedBusiness)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', today())->count();
            $stats['total_charges'] = HotelAmenityRequest::where('business_id', $this->selectedBusiness)
                ->whereDate('requested_at', today())
                ->sum('charge_amount');
        }

        // Get guests for dropdown
        $guests = Guest::where('business_id', $this->selectedBusiness)
            ->where('status', 'active')
            ->get();

        // Get rooms for dropdown
        $rooms = Room::where('business_id', $this->selectedBusiness)->get();

        // Get active reservations for dropdown
        $reservations = Reservation::where('business_id', $this->selectedBusiness)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->with('guest')
            ->get();

        return view('livewire.owner.hotel.amenity-requests', [
            'businesses' => $businesses,
            'requests' => $requests,
            'stats' => $stats,
            'guests' => $guests,
            'rooms' => $rooms,
            'reservations' => $reservations,
        ]);
    }
}
