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
    public $totalRevenue = 0;
    public $totalStaff = 0;
    public $totalCustomers = 0;
    public $todaySales = 0;
    public $todayRevenue = 0;
    public $recentSales = [];

    public function mount()
    {
        $owner = Auth::user();
        $carwashIds = $owner->ownedCarwashes()->pluck('id');

        $this->totalCarwashes = $carwashIds->count();
        $this->totalSales = sales::whereIn('carwash_id', $carwashIds)->count();
        $this->totalRevenue = sales::whereIn('carwash_id', $carwashIds)->sum('total_amount');
        $this->totalStaff = staffs::whereIn('carwash_id', $carwashIds)->count();
        $this->totalCustomers = customers::whereIn('carwash_id', $carwashIds)->count();

        // Today's stats
        $this->todaySales = sales::whereIn('carwash_id', $carwashIds)
            ->whereDate('sale_date', today())
            ->count();
        $this->todayRevenue = sales::whereIn('carwash_id', $carwashIds)
            ->whereDate('sale_date', today())
            ->sum('total_amount');

        $this->recentSales = sales::whereIn('carwash_id', $carwashIds)
            ->with(['items.item', 'carwash', 'customer'])
            ->latest('sale_date')
            ->take(5)
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.owner.dashboard');
    }
}
