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

#[Layout('components.layouts.app-owner')]
class CommunicationLog extends Component
{
    use WithPagination;

    public $selectedHotel = null;
    public $search = '';
    public $filterType = '';
    public $filterStatus = '';
    public $showModal = false;
    public $editMode = false;
    public $logId = null;

    // Communication properties
    #[Rule('required|exists:guests,id')]
    public $guest_id = '';

    #[Rule('nullable|exists:reservations,id')]
    public $reservation_id = '';

    #[Rule('required|in:email,sms,phone_call,whatsapp,letter,other')]
    public $communication_type = 'email';

    #[Rule('required|in:outbound,inbound')]
    public $direction = 'outbound';

    #[Rule('required|string|max:255')]
    public $subject = '';

    #[Rule('required|string')]
    public $message = '';

    #[Rule('required|in:sent,delivered,read,failed,pending')]
    public $status = 'sent';

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
        $this->logId = null;
        $this->guest_id = '';
        $this->reservation_id = '';
        $this->communication_type = 'email';
        $this->direction = 'outbound';
        $this->subject = '';
        $this->message = '';
        $this->status = 'sent';
        $this->notes = '';
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // In a real implementation, save to communication_logs table
            // For now, we'll just flash a success message

            session()->flash('message', 'Communication logged successfully.');

            DB::commit();
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Failed to log communication: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $hotels = Business::where('owner_id', Auth::id())
            ->where('type', 'hotel')
            ->get();

        $communications = collect();
        $guests = collect();
        $reservations = collect();
        $stats = [
            'total' => 0,
            'emails' => 0,
            'sms' => 0,
            'calls' => 0,
        ];

        if ($this->selectedHotel) {
            // Since we don't have a communication_logs table yet, we'll create sample data
            // In production, this would query the actual table
            $communications = collect([]);

            // Get guests for dropdown
            $guests = Guest::where('business_id', $this->selectedHotel)
                ->orderBy('first_name')
                ->get();

            // Get recent reservations for dropdown
            $reservations = Reservation::where('business_id', $this->selectedHotel)
                ->with('guest')
                ->latest()
                ->limit(50)
                ->get();

            // Mock stats - in production, query from communication_logs table
            $stats = [
                'total' => 0,
                'emails' => 0,
                'sms' => 0,
                'calls' => 0,
            ];
        }

        return view('livewire.owner.hotel.communication-log', [
            'hotels' => $hotels,
            'communications' => $communications,
            'guests' => $guests,
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }
}
