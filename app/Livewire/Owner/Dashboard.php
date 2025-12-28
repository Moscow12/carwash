<?php

namespace App\Livewire\Owner;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use App\Models\carwashes;
use App\Models\sales;
use App\Models\staffs;
use App\Models\customers;

#[Layout('components.layouts.app-owner')]
class Dashboard extends Component
{
    public $totalCarwashes = 0;
    public $totalSales = 0;
    public $totalStaff = 0;
    public $totalCustomers = 0;
    public $recentSales = [];

    public function mount()
    {
        $owner = Auth::user();
        $carwashIds = $owner->ownedCarwashes()->pluck('id');

        $this->totalCarwashes = $carwashIds->count();
        $this->totalSales = sales::whereIn('carwash_id', $carwashIds)->count();
        $this->totalStaff = staffs::whereIn('carwash_id', $carwashIds)->count();
        $this->totalCustomers = customers::whereIn('carwash_id', $carwashIds)->count();
        $this->recentSales = sales::whereIn('carwash_id', $carwashIds)
            ->with(['item', 'carwash'])
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.owner.dashboard');
    }
}
