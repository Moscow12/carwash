<?php

namespace App\Livewire\Customer;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking;
use App\Models\carwashes;

#[Layout('components.layouts.app-customer')]
class Dashboard extends Component
{
    public $totalBookings = 0;
    public $pendingBookings = 0;
    public $completedBookings = 0;
    public $recentBookings = [];

    public function mount()
    {
        $user = Auth::user();

        $this->totalBookings = Booking::where('customer_id', $user->id)->count();
        $this->pendingBookings = Booking::where('customer_id', $user->id)
            ->where('status', 'pending')
            ->count();
        $this->completedBookings = Booking::where('customer_id', $user->id)
            ->where('status', 'completed')
            ->count();
        $this->recentBookings = Booking::where('customer_id', $user->id)
            ->with(['carwash', 'item'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.customer.dashboard');
    }
}
