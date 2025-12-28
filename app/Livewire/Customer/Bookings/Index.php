<?php

namespace App\Livewire\Customer\Bookings;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;

#[Layout('components.layouts.app-customer')]
class Index extends Component
{
    use WithPagination;

    public $statusFilter = '';

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function cancelBooking($bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('customer_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($booking) {
            $booking->update(['status' => 'cancelled']);
            session()->flash('success', 'Booking cancelled successfully.');
        }
    }

    public function render()
    {
        $bookings = Booking::where('customer_id', Auth::id())
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->with(['carwash', 'item'])
            ->latest()
            ->paginate(10);

        return view('livewire.customer.bookings.index', [
            'bookings' => $bookings
        ]);
    }
}
