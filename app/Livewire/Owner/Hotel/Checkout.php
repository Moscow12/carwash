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
use App\Models\Folio;
use App\Models\HotelPayment;
use App\Models\HotelBranch;
use Carbon\Carbon;

#[Layout('components.layouts.app-owner')]
class Checkout extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedHotel = null;
    public $selectedBranch = null;
    public $showModal = false;
    public $selectedReservation = null;
    public $folioData = null;
    public $roomAllocation = null;
    public $actualCheckOutTime = '';
    public $paymentAmount = 0;
    public $paymentMethod = 'cash';
    public $roomCondition = 'good';
    public $damageRemarks = '';
    public $guestFeedback = '';
    public $guestRating = 5;
    public $additionalCharges = 0;
    public $chargeDescription = '';

    public function mount()
    {
        $hotel = Auth::user()->assignedBusinesses()
            ->where('type', 'hotel')
            ->where('status', 'active')
            ->first();

        if ($hotel) {
            $this->selectedHotel = $hotel->id;
            $this->selectedBranch = $hotel?->hotelBranches()->where('is_main', true)->first()?->id;
        }

        $this->actualCheckOutTime = now()->format('Y-m-d\TH:i');
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

    public function openCheckOutModal($reservationId)
    {
        $reservation = Reservation::with(['guest', 'roomType', 'branch', 'roomAllocation.room'])
            ->findOrFail($reservationId);

        if ($reservation->status !== 'checked_in') {
            session()->flash('error', 'Only checked-in guests can be checked out.');
            return;
        }

        $this->selectedReservation = $reservation->toArray();
        $this->roomAllocation = $reservation->roomAllocation?->toArray();

        // Get folio data
        $folio = $reservation->folios()->with('charges', 'payments')->first();
        if ($folio) {
            $this->folioData = $folio->toArray();
            $balance = $folio->balance;
            $this->paymentAmount = max(0, $balance);
        } else {
            // Create folio if doesn't exist
            $balance = $reservation->total_amount - $reservation->deposit_amount;
            $this->paymentAmount = max(0, $balance);
        }

        $this->showModal = true;
    }

    public function addAdditionalCharge()
    {
        $this->validate([
            'additionalCharges' => 'required|numeric|min:0',
            'chargeDescription' => 'required|string|max:255',
        ]);

        if ($this->folioData) {
            $folio = Folio::find($this->folioData['id']);

            // Add charge to folio
            $folio->charges()->create([
                'charge_type' => 'additional',
                'description' => $this->chargeDescription,
                'amount' => $this->additionalCharges,
                'charged_at' => now(),
            ]);

            // Update folio totals
            $folio->update([
                'total_charges' => $folio->total_charges + $this->additionalCharges,
                'balance' => $folio->balance + $this->additionalCharges,
            ]);

            // Reload folio data
            $this->folioData = $folio->fresh(['charges', 'payments'])->toArray();
            $this->paymentAmount = max(0, $folio->balance);

            session()->flash('message', 'Additional charge added successfully.');
            $this->reset(['additionalCharges', 'chargeDescription']);
        }
    }

    public function processCheckOut()
    {
        $this->validate([
            'actualCheckOutTime' => 'required|date',
            'paymentAmount' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:cash,card,bank_transfer,mobile_money',
            'roomCondition' => 'required|in:good,damaged,needs_deep_clean',
            'guestRating' => 'required|integer|min:1|max:5',
        ]);

        DB::beginTransaction();

        try {
            $reservation = Reservation::findOrFail($this->selectedReservation['id']);
            $roomAllocation = RoomAllocation::find($this->roomAllocation['id']);
            $room = Room::find($this->roomAllocation['room_id']);

            // Update room allocation
            $roomAllocation->update([
                'actual_check_out' => $this->actualCheckOutTime,
            ]);

            // Update reservation status
            $reservation->update([
                'status' => 'checked_out',
            ]);

            // Get or create folio
            $folio = $reservation->folios()->first();
            if (!$folio) {
                $folio = $reservation->folios()->create([
                    'folio_no' => 'FOL-' . strtoupper(uniqid()),
                    'business_id' => $this->selectedHotel,
                    'guest_id' => $reservation->guest_id,
                    'status' => 'open',
                    'total_charges' => $reservation->total_amount,
                    'total_payments' => $reservation->deposit_amount,
                    'balance' => $reservation->total_amount - $reservation->deposit_amount,
                    'opened_at' => $roomAllocation->actual_check_in,
                ]);
            }

            // Record payment if amount > 0
            if ($this->paymentAmount > 0) {
                HotelPayment::create([
                    'business_id' => $this->selectedHotel,
                    'folio_id' => $folio->id,
                    'reservation_id' => $reservation->id,
                    'guest_id' => $reservation->guest_id,
                    'amount' => $this->paymentAmount,
                    'payment_method' => $this->paymentMethod,
                    'payment_date' => now(),
                    'status' => 'completed',
                    'processed_by' => Auth::id(),
                ]);

                // Update folio
                $folio->update([
                    'total_payments' => $folio->total_payments + $this->paymentAmount,
                    'balance' => $folio->balance - $this->paymentAmount,
                ]);
            }

            // Close folio
            $folio->update([
                'status' => 'closed',
                'closed_at' => now(),
            ]);

            // Update room status based on condition
            $newRoomStatus = match($this->roomCondition) {
                'damaged' => 'maintenance',
                'needs_deep_clean' => 'cleaning',
                default => 'cleaning',
            };

            $room->update([
                'status' => $newRoomStatus,
            ]);

            // Save guest feedback (you might want to create a feedback table)
            if ($this->guestFeedback || $this->guestRating) {
                // Store feedback logic here
            }

            DB::commit();

            session()->flash('message', "Guest successfully checked out from Room {$room->number}.");
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Check-out failed: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset([
            'selectedReservation', 'folioData', 'roomAllocation', 'paymentAmount',
            'paymentMethod', 'roomCondition', 'damageRemarks', 'guestFeedback',
            'guestRating', 'additionalCharges', 'chargeDescription'
        ]);
        $this->actualCheckOutTime = now()->format('Y-m-d\TH:i');
        $this->guestRating = 5;
        $this->roomCondition = 'good';
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

        $query = Reservation::with(['guest', 'roomType', 'branch', 'roomAllocation.room'])
            ->where('business_id', $this->selectedHotel)
            ->where('status', 'checked_in')
            ->whereDate('check_out_date', '<=', today()->addDays(7))
            ->has('roomAllocation');

        if ($this->selectedBranch) {
            $query->where('branch_id', $this->selectedBranch);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('reservation_no', 'like', '%' . $this->search . '%')
                  ->orWhereHas('guest', function ($gq) {
                      $gq->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('roomAllocation.room', function ($rq) {
                      $rq->where('number', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $reservations = $query->orderBy('check_out_date')->paginate(15);

        // Statistics
        $stats = [
            'today' => Reservation::where('business_id', $this->selectedHotel)
                ->whereDate('check_out_date', today())
                ->where('status', 'checked_in')
                ->count(),
            'overdue' => Reservation::where('business_id', $this->selectedHotel)
                ->whereDate('check_out_date', '<', today())
                ->where('status', 'checked_in')
                ->count(),
            'upcoming' => Reservation::where('business_id', $this->selectedHotel)
                ->whereBetween('check_out_date', [today()->addDay(), today()->addWeek()])
                ->where('status', 'checked_in')
                ->count(),
        ];

        return view('livewire.owner.hotel.checkout', [
            'hotels' => $hotels,
            'branches' => $branches,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }
}
