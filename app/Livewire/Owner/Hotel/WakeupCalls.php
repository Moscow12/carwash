<?php

namespace App\Livewire\Owner\Hotel;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Business;
use App\Models\WakeupCall;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\Guest;

#[Layout('components.layouts.app-owner')]
class WakeupCalls extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedBusiness = null;
    public $filterStatus = 'all';
    public $filterDate = null;
    public $showModal = false;
    public $editMode = false;

    // Wakeup Call Properties
    public $callId = null;

    #[Rule('nullable|exists:reservations,id')]
    public $reservation_id = null;

    #[Rule('nullable|exists:rooms,id')]
    public $room_id = null;

    #[Rule('required|exists:guests,id')]
    public $guest_id = null;

    #[Rule('required|date')]
    public $scheduled_at = null;

    #[Rule('nullable|boolean')]
    public $repeat_daily = false;

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

        $this->filterDate = today()->format('Y-m-d');
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
        $this->callId = null;
        $this->reservation_id = null;
        $this->room_id = null;
        $this->guest_id = null;
        $this->scheduled_at = null;
        $this->repeat_daily = false;
        $this->notes = '';
        $this->resetValidation();
    }

    public function saveCall()
    {
        $this->validate([
            'guest_id' => 'required|exists:guests,id',
            'scheduled_at' => 'required|date',
        ]);

        try {
            $data = [
                'business_id' => $this->selectedBusiness,
                'reservation_id' => $this->reservation_id,
                'room_id' => $this->room_id,
                'guest_id' => $this->guest_id,
                'scheduled_at' => $this->scheduled_at,
                'repeat_daily' => $this->repeat_daily ?? false,
                'status' => 'scheduled',
                'notes' => $this->notes,
            ];

            if ($this->editMode && $this->callId) {
                WakeupCall::findOrFail($this->callId)->update($data);
                session()->flash('message', 'Wakeup call updated successfully.');
            } else {
                WakeupCall::create($data);
                session()->flash('message', 'Wakeup call scheduled successfully.');
            }

            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    public function editCall($id)
    {
        $call = WakeupCall::findOrFail($id);

        $this->editMode = true;
        $this->callId = $call->id;
        $this->reservation_id = $call->reservation_id;
        $this->room_id = $call->room_id;
        $this->guest_id = $call->guest_id;
        $this->scheduled_at = $call->scheduled_at;
        $this->repeat_daily = $call->repeat_daily;
        $this->notes = $call->notes;

        $this->showModal = true;
    }

    public function markDelivered($id)
    {
        try {
            $call = WakeupCall::findOrFail($id);
            $call->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'delivered_by' => Auth::user()->staff->id ?? null,
            ]);

            // If repeat daily, create next day's call
            if ($call->repeat_daily) {
                WakeupCall::create([
                    'business_id' => $call->business_id,
                    'reservation_id' => $call->reservation_id,
                    'room_id' => $call->room_id,
                    'guest_id' => $call->guest_id,
                    'scheduled_at' => \Carbon\Carbon::parse($call->scheduled_at)->addDay(),
                    'repeat_daily' => true,
                    'status' => 'scheduled',
                    'notes' => $call->notes,
                ]);
            }

            session()->flash('message', 'Wakeup call marked as delivered.');
        } catch (\Exception $e) {
            session()->flash('error', 'Delivery failed: ' . $e->getMessage());
        }
    }

    public function markMissed($id)
    {
        try {
            WakeupCall::findOrFail($id)->update(['status' => 'missed']);
            session()->flash('message', 'Wakeup call marked as missed.');
        } catch (\Exception $e) {
            session()->flash('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function cancelCall($id)
    {
        try {
            WakeupCall::findOrFail($id)->update(['status' => 'cancelled']);
            session()->flash('message', 'Wakeup call cancelled.');
        } catch (\Exception $e) {
            session()->flash('error', 'Cancel failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $businesses = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $calls = collect();
        $stats = [
            'scheduled' => 0,
            'delivered_today' => 0,
            'missed_today' => 0,
            'pending_today' => 0,
        ];

        if ($this->selectedBusiness) {
            $query = WakeupCall::where('business_id', $this->selectedBusiness);

            // Filter by status
            if ($this->filterStatus !== 'all') {
                $query->where('status', $this->filterStatus);
            }

            // Filter by date
            if ($this->filterDate) {
                $query->whereDate('scheduled_at', $this->filterDate);
            }

            // Search
            if ($this->search) {
                $query->where(function($q) {
                    $q->whereHas('guest', function($gq) {
                        $gq->where('full_name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('room', function($rq) {
                        $rq->where('room_number', 'like', '%' . $this->search . '%');
                    });
                });
            }

            $calls = $query->with(['guest', 'room', 'reservation', 'deliveredBy'])
                ->orderBy('scheduled_at', 'asc')
                ->paginate(15);

            // Statistics
            $stats['scheduled'] = WakeupCall::where('business_id', $this->selectedBusiness)
                ->where('status', 'scheduled')
                ->whereDate('scheduled_at', '>=', today())
                ->count();

            $stats['delivered_today'] = WakeupCall::where('business_id', $this->selectedBusiness)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->count();

            $stats['missed_today'] = WakeupCall::where('business_id', $this->selectedBusiness)
                ->where('status', 'missed')
                ->whereDate('scheduled_at', today())
                ->count();

            $stats['pending_today'] = WakeupCall::where('business_id', $this->selectedBusiness)
                ->where('status', 'scheduled')
                ->whereDate('scheduled_at', today())
                ->count();
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

        return view('livewire.owner.hotel.wakeup-calls', [
            'businesses' => $businesses,
            'calls' => $calls,
            'stats' => $stats,
            'guests' => $guests,
            'rooms' => $rooms,
            'reservations' => $reservations,
        ]);
    }
}
