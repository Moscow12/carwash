<?php

namespace App\Livewire\Customer\Carwashes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\carwashes;
use App\Models\Booking;
use App\Models\items;

#[Layout('components.layouts.app-customer')]
class Details extends Component
{
    public $carwash;
    public $services = [];
    public $selectedService = '';
    public $bookingDate = '';
    public $plateNumber = '';
    public $notes = '';
    public $showBookingModal = false;

    public function mount($id)
    {
        $this->carwash = carwashes::with(['regions', 'districts', 'wards', 'streets'])
            ->findOrFail($id);

        $this->services = items::where('carwash_id', $id)
            ->where('status', 'active')
            ->where('type', 'Service')
            ->get();
    }

    public function openBookingModal($serviceId)
    {
        $this->selectedService = $serviceId;
        $this->showBookingModal = true;
    }

    public function closeBookingModal()
    {
        $this->showBookingModal = false;
        $this->reset(['selectedService', 'bookingDate', 'plateNumber', 'notes']);
    }

    public function createBooking()
    {
        $this->validate([
            'selectedService' => 'required|exists:items,id',
            'bookingDate' => 'required|date|after:now',
            'plateNumber' => 'nullable|string|max:20',
        ]);

        Booking::create([
            'customer_id' => Auth::id(),
            'carwash_id' => $this->carwash->id,
            'item_id' => $this->selectedService,
            'booking_date' => $this->bookingDate,
            'plate_number' => $this->plateNumber,
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        $this->closeBookingModal();
        session()->flash('success', 'Booking created successfully!');
    }

    public function render()
    {
        return view('livewire.customer.carwashes.details');
    }
}
